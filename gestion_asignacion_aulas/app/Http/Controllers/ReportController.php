<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AttendanceRecord;
use App\Models\Classroom;
use App\Models\AcademicManagement;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    /**
     * Mostrar vista principal de reportes
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Reporte de Horarios Semanales
     */
    public function weeklySchedules(Request $request)
    {
        $academicManagementId = $request->input('academic_management_id');
        $groupId = $request->input('group_id');
        $format = $request->input('format', 'view'); // view, pdf, excel

        // Obtener periodo académico activo o el seleccionado
        $academicManagement = $academicManagementId 
            ? AcademicManagement::find($academicManagementId)
            : AcademicManagement::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

        if (!$academicManagement) {
            return back()->with('error', 'No hay periodo académico activo.');
        }

        // Construir query base
        $query = Assignment::with([
            'userSubject.subject',
            'userSubject.user',
            'group',
            'daySchedule.day',
            'daySchedule.schedule',
            'classroom.infrastructure'
        ])->where('academic_management_id', $academicManagement->id);

        // Filtrar por grupo si se especifica
        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $assignments = $query->get();

        // Organizar por día y horario
        $scheduleData = $this->organizeScheduleByDay($assignments);

        // Obtener listas para filtros
        $academicManagements = AcademicManagement::orderBy('start_date', 'desc')->get();
        $groups = Group::where('is_active', true)->orderBy('name')->get();

        if ($format === 'pdf') {
            return $this->generateWeeklySchedulePDF($scheduleData, $academicManagement, $groupId);
        }

        return view('reports.weekly-schedules', compact(
            'scheduleData',
            'academicManagement',
            'academicManagements',
            'groups',
            'groupId'
        ));
    }

    /**
     * Reporte de Asistencia por Docente y Grupo
     */
    public function attendanceReport(Request $request)
    {
        $userId = $request->input('user_id');
        $groupId = $request->input('group_id');
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $format = $request->input('format', 'view');

        // Query base de registros de asistencia
        $query = AttendanceRecord::with([
            'assignment.userSubject.subject',
            'assignment.userSubject.user',
            'assignment.group',
            'assignment.daySchedule.day',
            'assignment.daySchedule.schedule',
            'user'
        ])->whereBetween('created_at', [$startDate, $endDate]);

        // Filtros
        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($groupId) {
            $query->whereHas('assignment', function ($q) use ($groupId) {
                $q->where('group_id', $groupId);
            });
        }

        $attendanceRecords = $query->orderBy('created_at', 'desc')->get();

        // Calcular estadísticas
        $statistics = $this->calculateAttendanceStatistics($attendanceRecords);

        // Listas para filtros
        $teachers = User::role('Docente')->orderBy('name')->get();
        $groups = Group::where('is_active', true)->orderBy('name')->get();

        if ($format === 'pdf') {
            return $this->generateAttendancePDF($attendanceRecords, $statistics, $startDate, $endDate);
        }

        return view('reports.attendance', compact(
            'attendanceRecords',
            'statistics',
            'teachers',
            'groups',
            'userId',
            'groupId',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Reporte de Aulas Disponibles
     */
    public function availableClassrooms(Request $request)
    {
        $dayId = $request->input('day_id');
        $scheduleId = $request->input('schedule_id');
        $date = $request->input('date', now()->format('Y-m-d'));
        $format = $request->input('format', 'view');

        // Obtener periodo académico activo
        $academicManagement = AcademicManagement::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$academicManagement) {
            return back()->with('error', 'No hay periodo académico activo para la fecha seleccionada.');
        }

        // Obtener todas las aulas
        $allClassrooms = Classroom::with('infrastructure')->where('is_active', true)->get();

        // Obtener aulas ocupadas
        $occupiedQuery = Assignment::with([
            'classroom',
            'userSubject.subject',
            'userSubject.user',
            'group',
            'daySchedule.schedule'
        ])->where('academic_management_id', $academicManagement->id);

        if ($dayId) {
            $occupiedQuery->whereHas('daySchedule', function ($q) use ($dayId) {
                $q->where('day_id', $dayId);
            });
        }

        if ($scheduleId) {
            $occupiedQuery->whereHas('daySchedule', function ($q) use ($scheduleId) {
                $q->where('schedule_id', $scheduleId);
            });
        }

        $occupiedClassrooms = $occupiedQuery->get()->pluck('classroom_id')->toArray();

        // Clasificar aulas
        $availableClassrooms = $allClassrooms->whereNotIn('id', $occupiedClassrooms);
        $occupiedClassroomsData = $allClassrooms->whereIn('id', $occupiedClassrooms);

        // Obtener datos de las asignaciones para aulas ocupadas
        $occupiedDetails = [];
        foreach ($occupiedClassroomsData as $classroom) {
            $assignment = Assignment::with([
                'userSubject.subject',
                'userSubject.user',
                'group',
                'daySchedule.day',
                'daySchedule.schedule'
            ])->where('classroom_id', $classroom->id)
              ->where('academic_management_id', $academicManagement->id);

            if ($dayId) {
                $assignment->whereHas('daySchedule', function ($q) use ($dayId) {
                    $q->where('day_id', $dayId);
                });
            }

            if ($scheduleId) {
                $assignment->whereHas('daySchedule', function ($q) use ($scheduleId) {
                    $q->where('schedule_id', $scheduleId);
                });
            }

            $occupiedDetails[$classroom->id] = $assignment->first();
        }

        // Datos para filtros
        $days = DB::table('days')->get();
        $schedules = DB::table('schedules')->orderBy('start')->get();

        if ($format === 'pdf') {
            return $this->generateAvailableClassroomsPDF(
                $availableClassrooms, 
                $occupiedClassroomsData, 
                $occupiedDetails,
                $date
            );
        }

        return view('reports.available-classrooms', compact(
            'availableClassrooms',
            'occupiedClassroomsData',
            'occupiedDetails',
            'days',
            'schedules',
            'dayId',
            'scheduleId',
            'date'
        ));
    }

    /**
     * Organizar horarios por día
     */
    private function organizeScheduleByDay($assignments)
    {
        // Mapeo de días en inglés a español
        $daysMap = [
            'Monday' => 'Lunes',
            'Tuesday' => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday' => 'Jueves',
            'Friday' => 'Viernes',
            'Saturday' => 'Sábado',
            'Sunday' => 'Domingo'
        ];
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $organized = [];

        foreach ($days as $day) {
            $dayAssignments = $assignments->filter(function ($assignment) use ($day) {
                return trim($assignment->daySchedule->day->name) === $day;
            })->sortBy(function ($assignment) {
                return $assignment->daySchedule->schedule->start;
            })->values(); // Reindexar la colección

            if ($dayAssignments->count() > 0) {
                // Usar el nombre en español como clave
                $organized[$daysMap[$day]] = $dayAssignments;
            }
        }

        return $organized;
    }

    /**
     * Calcular estadísticas de asistencia
     */
    private function calculateAttendanceStatistics($records)
    {
        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'present_percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
            'absent_percentage' => $total > 0 ? round(($absent / $total) * 100, 2) : 0,
            'late_percentage' => $total > 0 ? round(($late / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Generar PDF de horarios semanales
     */
    private function generateWeeklySchedulePDF($scheduleData, $academicManagement, $groupId)
    {
        $group = $groupId ? Group::find($groupId) : null;
        
        $pdf = Pdf::loadView('reports.pdf.weekly-schedules', [
            'scheduleData' => $scheduleData,
            'academicManagement' => $academicManagement,
            'group' => $group,
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('a4', 'landscape');

        $filename = 'Horario_Semanal_' . ($group ? $group->name . '_' : '') . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generar PDF de asistencia
     */
    private function generateAttendancePDF($attendanceRecords, $statistics, $startDate, $endDate)
    {
        $pdf = Pdf::loadView('reports.pdf.attendance', [
            'attendanceRecords' => $attendanceRecords,
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('a4', 'portrait');

        $filename = 'Reporte_Asistencia_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Generar PDF de aulas disponibles
     */
    private function generateAvailableClassroomsPDF($availableClassrooms, $occupiedClassrooms, $occupiedDetails, $date)
    {
        $pdf = Pdf::loadView('reports.pdf.available-classrooms', [
            'availableClassrooms' => $availableClassrooms,
            'occupiedClassrooms' => $occupiedClassrooms,
            'occupiedDetails' => $occupiedDetails,
            'date' => $date,
            'generatedAt' => now()->format('d/m/Y H:i')
        ])->setPaper('a4', 'portrait');

        $filename = 'Aulas_Disponibles_' . $date . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Exportar horarios semanales a Excel
     */
    public function exportWeeklySchedules(Request $request)
    {
        $academicManagementId = $request->input('academic_management_id');
        $groupId = $request->input('group_id');

        $academicManagement = $academicManagementId 
            ? AcademicManagement::find($academicManagementId)
            : AcademicManagement::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

        if (!$academicManagement) {
            return back()->with('error', 'No hay periodo académico activo.');
        }

        $query = Assignment::with([
            'userSubject.subject',
            'userSubject.user',
            'group',
            'daySchedule.day',
            'daySchedule.schedule',
            'classroom.infrastructure'
        ])->where('academic_management_id', $academicManagement->id);

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $assignments = $query->get();
        $scheduleData = $this->organizeScheduleByDay($assignments);

        $filename = 'Horario_Semanal_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new class($scheduleData, $academicManagement, $groupId) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            private $scheduleData;
            private $academicManagement;
            private $groupId;

            public function __construct($scheduleData, $academicManagement, $groupId)
            {
                $this->scheduleData = $scheduleData;
                $this->academicManagement = $academicManagement;
                $this->groupId = $groupId;
            }

            public function collection()
            {
                $data = collect();
                
                foreach ($this->scheduleData as $day => $assignments) {
                    foreach ($assignments as $assignment) {
                        $data->push([
                            $day,
                            Carbon::parse($assignment->daySchedule->schedule->start)->format('H:i'),
                            Carbon::parse($assignment->daySchedule->schedule->end)->format('H:i'),
                            $assignment->userSubject->subject->name,
                            $assignment->userSubject->subject->code,
                            $assignment->userSubject->user->name,
                            $assignment->group->name,
                            $assignment->classroom->name,
                            $assignment->classroom->infrastructure->name
                        ]);
                    }
                }

                return $data;
            }

            public function headings(): array
            {
                return ['Día', 'Horario Inicio', 'Horario Fin', 'Materia', 'Código', 'Docente', 'Grupo', 'Aula', 'Infraestructura'];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '3B82F6']
                        ],
                    ],
                ];
            }
        }, $filename);
    }

    /**
     * Exportar asistencia a Excel
     */
    public function exportAttendance(Request $request)
    {
        $userId = $request->input('user_id');
        $groupId = $request->input('group_id');
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $query = AttendanceRecord::with([
            'assignment.userSubject.subject',
            'assignment.userSubject.user',
            'assignment.group',
            'assignment.daySchedule.day',
            'assignment.daySchedule.schedule',
            'user'
        ])->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($groupId) {
            $query->whereHas('assignment', function ($q) use ($groupId) {
                $q->where('group_id', $groupId);
            });
        }

        $attendanceRecords = $query->orderBy('created_at', 'desc')->get();
        $statistics = $this->calculateAttendanceStatistics($attendanceRecords);

        $filename = 'Reporte_Asistencia_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new class($attendanceRecords, $statistics, $startDate, $endDate) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            private $attendanceRecords;
            private $statistics;
            private $startDate;
            private $endDate;

            public function __construct($attendanceRecords, $statistics, $startDate, $endDate)
            {
                $this->attendanceRecords = $attendanceRecords;
                $this->statistics = $statistics;
                $this->startDate = $startDate;
                $this->endDate = $endDate;
            }

            public function collection()
            {
                $data = collect();
                
                foreach ($this->attendanceRecords as $record) {
                    $status = match($record->status) {
                        'present' => 'Presente',
                        'late' => 'Tardanza',
                        default => 'Ausente'
                    };

                    $data->push([
                        Carbon::parse($record->created_at)->format('d/m/Y'),
                        $record->user->name,
                        $record->assignment->userSubject->subject->name,
                        $record->assignment->userSubject->subject->code,
                        $record->assignment->group->name,
                        __($record->assignment->daySchedule->day->name),
                        Carbon::parse($record->assignment->daySchedule->schedule->start)->format('H:i'),
                        Carbon::parse($record->assignment->daySchedule->schedule->end)->format('H:i'),
                        $status,
                        $record->scan_time ? Carbon::parse($record->scan_time)->format('H:i:s') : '-'
                    ]);
                }

                return $data;
            }

            public function headings(): array
            {
                return ['Fecha', 'Docente', 'Materia', 'Código', 'Grupo', 'Día', 'Hora Inicio', 'Hora Fin', 'Estado', 'Hora Marcado'];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '3B82F6']
                        ],
                    ],
                ];
            }
        }, $filename);
    }

    /**
     * Exportar aulas disponibles a Excel
     */
    public function exportAvailableClassrooms(Request $request)
    {
        $dayId = $request->input('day_id');
        $scheduleId = $request->input('schedule_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $academicManagement = AcademicManagement::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if (!$academicManagement) {
            return back()->with('error', 'No hay periodo académico activo para la fecha seleccionada.');
        }

        $allClassrooms = Classroom::with('infrastructure')->where('is_active', true)->get();

        $occupiedQuery = Assignment::with([
            'classroom',
            'userSubject.subject',
            'userSubject.user',
            'group',
            'daySchedule.day',
            'daySchedule.schedule'
        ])->where('academic_management_id', $academicManagement->id);

        if ($dayId) {
            $occupiedQuery->whereHas('daySchedule', function ($q) use ($dayId) {
                $q->where('day_id', $dayId);
            });
        }

        if ($scheduleId) {
            $occupiedQuery->whereHas('daySchedule', function ($q) use ($scheduleId) {
                $q->where('schedule_id', $scheduleId);
            });
        }

        $occupiedClassrooms = $occupiedQuery->get()->pluck('classroom_id')->toArray();
        $availableClassrooms = $allClassrooms->whereNotIn('id', $occupiedClassrooms);
        $occupiedClassroomsData = $allClassrooms->whereIn('id', $occupiedClassrooms);

        $occupiedDetails = [];
        foreach ($occupiedClassroomsData as $classroom) {
            $assignment = Assignment::with([
                'userSubject.subject',
                'userSubject.user',
                'group',
                'daySchedule.day',
                'daySchedule.schedule'
            ])->where('classroom_id', $classroom->id)
              ->where('academic_management_id', $academicManagement->id);

            if ($dayId) {
                $assignment->whereHas('daySchedule', function ($q) use ($dayId) {
                    $q->where('day_id', $dayId);
                });
            }

            if ($scheduleId) {
                $assignment->whereHas('daySchedule', function ($q) use ($scheduleId) {
                    $q->where('schedule_id', $scheduleId);
                });
            }

            $occupiedDetails[$classroom->id] = $assignment->first();
        }

        $filename = 'Aulas_Disponibles_' . $date . '.xlsx';

        return Excel::download(new class($availableClassrooms, $occupiedClassroomsData, $occupiedDetails, $date) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            private $availableClassrooms;
            private $occupiedClassroomsData;
            private $occupiedDetails;
            private $date;

            public function __construct($availableClassrooms, $occupiedClassroomsData, $occupiedDetails, $date)
            {
                $this->availableClassrooms = $availableClassrooms;
                $this->occupiedClassroomsData = $occupiedClassroomsData;
                $this->occupiedDetails = $occupiedDetails;
                $this->date = $date;
            }

            public function collection()
            {
                $data = collect();
                
                // Aulas Disponibles
                foreach ($this->availableClassrooms as $classroom) {
                    $data->push([
                        'DISPONIBLE',
                        $classroom->name,
                        $classroom->infrastructure->name,
                        $classroom->capacity,
                        ucfirst($classroom->type),
                        '',
                        '',
                        '',
                        ''
                    ]);
                }

                // Aulas Ocupadas
                foreach ($this->occupiedClassroomsData as $classroom) {
                    $assignment = $this->occupiedDetails[$classroom->id] ?? null;
                    if ($assignment) {
                        $data->push([
                            'OCUPADA',
                            $classroom->name,
                            $classroom->infrastructure->name,
                            $classroom->capacity,
                            ucfirst($classroom->type),
                            $assignment->userSubject->subject->name,
                            $assignment->userSubject->user->name,
                            $assignment->group->name,
                            Carbon::parse($assignment->daySchedule->schedule->start)->format('H:i') . ' - ' . 
                            Carbon::parse($assignment->daySchedule->schedule->end)->format('H:i')
                        ]);
                    }
                }

                return $data;
            }

            public function headings(): array
            {
                return ['Estado', 'Aula', 'Infraestructura', 'Capacidad', 'Tipo', 'Materia', 'Docente', 'Grupo', 'Horario'];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '3B82F6']
                        ],
                    ],
                ];
            }
        }, $filename);
    }
}
