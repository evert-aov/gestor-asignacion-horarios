<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Reports\AttendanceReportService;
use App\Services\Reports\ClassroomReportService;
use App\Services\Reports\ScheduleReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ScheduleReportService $scheduleService,
        protected AttendanceReportService $attendanceService,
        protected ClassroomReportService $classroomService
    ) {}

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
        $data = $this->scheduleService->getWeeklyScheduleData($request);

        if (!$data) {
            return back()->with('error', 'No hay periodo académico activo.');
        }

        $filterData = $this->scheduleService->getFilterData();

        $format = $request->input('format', 'view');

        if ($format === 'pdf') {
            return $this->scheduleService->generateWeeklySchedulePDF(
                $data['scheduleData'],
                $data['academicManagement'],
                $data['groupId']
            );
        }

        return view('reports.weekly-schedules', array_merge($data, $filterData));
    }

    /**
     * Reporte de Asistencia por Docente y Grupo
     */
    public function attendanceReport(Request $request)
    {
        $data = $this->attendanceService->getAttendanceData($request);
        $filterData = $this->attendanceService->getFilterData();

        $format = $request->input('format', 'view');

        if ($format === 'pdf') {
            return $this->attendanceService->generateAttendancePDF(
                $data['attendanceRecords'],
                $data['statistics'],
                $data['startDate'],
                $data['endDate']
            );
        }

        return view('reports.attendance', array_merge($data, $filterData));
    }

    /**
     * Reporte de Aulas Disponibles
     */
    public function availableClassrooms(Request $request)
    {
        $data = $this->classroomService->getAvailableClassroomsData($request);

        if (!$data) {
            return back()->with('error', 'No hay periodo académico activo para la fecha seleccionada.');
        }

        $format = $request->input('format', 'view');

        if ($format === 'pdf') {
            return $this->classroomService->generateAvailableClassroomsPDF(
                $data['availableClassrooms'],
                $data['occupiedClassroomsData'],
                $data['occupiedDetails'],
                $data['date']
            );
        }

        // Extraer los datos de filtros del array anidado
        return view('reports.available-classrooms', [
            'availableClassrooms' => $data['availableClassrooms'],
            'occupiedClassroomsData' => $data['occupiedClassroomsData'],
            'occupiedDetails' => $data['occupiedDetails'],
            'days' => $data['filterData']['days'],
            'schedules' => $data['filterData']['schedules'],
            'dayId' => $data['dayId'],
            'scheduleId' => $data['scheduleId'],
            'date' => $data['date']
        ]);
    }

    /**
     * Exportar horarios semanales a Excel
     */
    public function exportWeeklySchedules(Request $request)
    {
        $data = $this->scheduleService->getWeeklyScheduleData($request);

        if (!$data) {
            return back()->with('error', 'No hay periodo académico activo.');
        }

        return $this->scheduleService->generateWeeklyScheduleExcel(
            $data['scheduleData'],
            $data['academicManagement'],
            $data['groupId']
        );
    }

    /**
     * Exportar asistencia a Excel
     */
    public function exportAttendance(Request $request)
    {
        $data = $this->attendanceService->getAttendanceData($request);

        return $this->attendanceService->generateAttendanceExcel(
            $data['attendanceRecords'],
            $data['statistics'],
            $data['startDate'],
            $data['endDate']
        );
    }

    /**
     * Exportar aulas disponibles a Excel
     */
    public function exportAvailableClassrooms(Request $request)
    {
        $data = $this->classroomService->getAvailableClassroomsData($request);

        if (!$data) {
            return back()->with('error', 'No hay periodo académico activo para la fecha seleccionada.');
        }

        return $this->classroomService->generateAvailableClassroomsExcel(
            $data['availableClassrooms'],
            $data['occupiedClassroomsData'],
            $data['occupiedDetails'],
            $data['date']
        );
    }
}
