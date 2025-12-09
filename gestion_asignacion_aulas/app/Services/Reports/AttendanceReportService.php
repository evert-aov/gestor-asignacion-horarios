<?php

namespace App\Services\Reports;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceReportService extends BaseReportService
{
    /**
     * Obtener datos de asistencia
     */
    public function getAttendanceData(Request $request)
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

        return compact('attendanceRecords', 'statistics', 'userId', 'groupId', 'startDate', 'endDate');
    }

    /**
     * Calcular estadísticas de asistencia
     */
    public function calculateAttendanceStatistics($records)
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
     * Generar PDF de asistencia
     */
    public function generateAttendancePDF($attendanceRecords, $statistics, $startDate, $endDate)
    {
        $filename = 'Reporte_Asistencia_' . now()->format('Y-m-d') . '.pdf';

        return $this->generatePDF('reports.pdf.attendance', [
            'attendanceRecords' => $attendanceRecords,
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ], $filename);
    }

    /**
     * Generar Excel de asistencia
     */
    public function generateAttendanceExcel($attendanceRecords, $statistics, $startDate, $endDate)
    {
        $filename = 'Reporte_Asistencia_' . now()->format('Y-m-d') . '.xlsx';

        $headers = ['Fecha', 'Docente', 'Materia', 'Código', 'Grupo', 'Día', 'Hora Inicio', 'Hora Fin', 'Estado', 'Hora Marcado'];

        return $this->generateExcel($filename, $attendanceRecords, $headers, function($records) {
            $collection = collect();

            foreach ($records as $record) {
                $status = match($record->status) {
                    'present' => 'Presente',
                    'late' => 'Tardanza',
                    default => 'Ausente'
                };

                $collection->push([
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

            return $collection;
        });
    }
}
