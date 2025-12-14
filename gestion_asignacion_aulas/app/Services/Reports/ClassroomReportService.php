<?php

namespace App\Services\Reports;

use App\Models\Assignment;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClassroomReportService extends BaseReportService
{
    /**
     * Obtener datos de aulas disponibles
     */
    public function getAvailableClassroomsData(Request $request): ?array
    {
        $dayId = $request->input('day_id');
        $scheduleId = $request->input('schedule_id');
        $date = $request->input('date', now()->format('Y-m-d'));

        $academicManagement = $this->getAcademicManagement();

        if (!$academicManagement) {
            return null;
        }

        $allClassrooms = Classroom::with('infrastructure')->where('is_active', true)->get();

        $occupiedQuery = Assignment::with([
            'classroom',
            'userSubject.subject',
            'userSubject.user',
            'group',
            'daySchedule.schedule'
        ])->where('academic_management_id', $academicManagement->id);

        $occupiedQuery = $this->applyScheduleFilters($occupiedQuery, $dayId, $scheduleId);

        $occupiedClassrooms = $occupiedQuery->get()->pluck('classroom_id')->toArray();

        $availableClassrooms = $allClassrooms->whereNotIn('id', $occupiedClassrooms);
        $occupiedClassroomsData = $allClassrooms->whereIn('id', $occupiedClassrooms);

        $occupiedDetails = $this->getOccupiedClassroomDetails($occupiedClassroomsData, $academicManagement->id, $dayId, $scheduleId);

        $filterData = [
            'days' => DB::table('days')->get(),
            'schedules' => DB::table('schedules')->orderBy('start')->get(),
        ];

        return compact(
            'availableClassrooms',
            'occupiedClassroomsData',
            'occupiedDetails',
            'filterData',
            'dayId',
            'scheduleId',
            'date'
        );
    }

    /**
     * Aplicar filtros de horario
     */
    private function applyScheduleFilters($query, $dayId, $scheduleId)
    {
        if ($dayId) {
            $query->whereHas('daySchedule', function ($q) use ($dayId) {
                $q->where('day_id', $dayId);
            });
        }

        if ($scheduleId) {
            $query->whereHas('daySchedule', function ($q) use ($scheduleId) {
                $q->where('schedule_id', $scheduleId);
            });
        }

        return $query;
    }

    /**
     * Obtener detalles de aulas ocupadas
     */
    private function getOccupiedClassroomDetails($occupiedClassrooms, $academicManagementId, $dayId, $scheduleId): array
    {
        $occupiedDetails = [];

        foreach ($occupiedClassrooms as $classroom) {
            $assignment = Assignment::with([
                'userSubject.subject',
                'userSubject.user',
                'group',
                'daySchedule.day',
                'daySchedule.schedule'
            ])->where('classroom_id', $classroom->id)
                ->where('academic_management_id', $academicManagementId);

            $assignment = $this->applyScheduleFilters($assignment, $dayId, $scheduleId);

            $occupiedDetails[$classroom->id] = $assignment->first();
        }

        return $occupiedDetails;
    }

    /**
     * Generar PDF de aulas disponibles
     */
    public function generateAvailableClassroomsPDF($availableClassrooms, $occupiedClassrooms, $occupiedDetails, $date): Response
    {
        $filename = 'Aulas_Disponibles_' . $date . '.pdf';

        return $this->generatePDF('reports.pdf.available-classrooms', [
            'availableClassrooms' => $availableClassrooms,
            'occupiedClassrooms' => $occupiedClassrooms,
            'occupiedDetails' => $occupiedDetails,
            'date' => $date,
        ], $filename);
    }

    /**
     * Generar Excel de aulas disponibles
     */
    public function generateAvailableClassroomsExcel($availableClassrooms, $occupiedClassrooms, $occupiedDetails, $date): BinaryFileResponse
    {
        $filename = 'Aulas_Disponibles_' . $date . '.xlsx';

        $headers = ['Estado', 'Aula', 'Infraestructura', 'Capacidad', 'Tipo', 'Materia', 'Docente', 'Grupo', 'Horario'];

        return $this->generateExcel($filename, compact('availableClassrooms', 'occupiedClassrooms', 'occupiedDetails'), $headers, function($data) {
            $collection = collect();

            // Aulas Disponibles
            foreach ($data['availableClassrooms'] as $classroom) {
                $collection->push([
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
            foreach ($data['occupiedClassrooms'] as $classroom) {
                $assignment = $data['occupiedDetails'][$classroom->id] ?? null;
                if ($assignment) {
                    $collection->push([
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

            return $collection;
        });
    }
}
