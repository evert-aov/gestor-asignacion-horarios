<?php

namespace App\Services\Reports;

use App\Models\Assignment;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleReportService extends BaseReportService
{
    /**
     * Obtener datos de horarios semanales
     */
    public function getWeeklyScheduleData(Request $request)
    {
        $academicManagementId = $request->input('academic_management_id');
        $groupId = $request->input('group_id');

        $academicManagement = $this->getAcademicManagement($academicManagementId);

        if (!$academicManagement) {
            return null;
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

        return compact('scheduleData', 'academicManagement', 'groupId');
    }

    /**
     * Organizar horarios por día
     */
    private function organizeScheduleByDay($assignments)
    {
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
            })->values();

            if ($dayAssignments->count() > 0) {
                $organized[$daysMap[$day]] = $dayAssignments;
            }
        }

        return $organized;
    }

    /**
     * Generar PDF de horarios
     */
    public function generateWeeklySchedulePDF($scheduleData, $academicManagement, $groupId)
    {
        $group = $groupId ? Group::find($groupId) : null;

        $filename = 'Horario_Semanal_' . ($group ? $group->name . '_' : '') . now()->format('Y-m-d') . '.pdf';

        return $this->generatePDF('reports.pdf.weekly-schedules', [
            'scheduleData' => $scheduleData,
            'academicManagement' => $academicManagement,
            'group' => $group,
        ], $filename, 'landscape');
    }

    /**
     * Generar Excel de horarios
     */
    public function generateWeeklyScheduleExcel($scheduleData, $academicManagement, $groupId)
    {
        $filename = 'Horario_Semanal_' . now()->format('Y-m-d') . '.xlsx';

        $headers = ['Día', 'Horario Inicio', 'Horario Fin', 'Materia', 'Código', 'Docente', 'Grupo', 'Aula', 'Infraestructura'];

        return $this->generateExcel($filename, $scheduleData, $headers, function($data) {
            $collection = collect();

            foreach ($data as $day => $assignments) {
                foreach ($assignments as $assignment) {
                    $collection->push([
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

            return $collection;
        });
    }
}
