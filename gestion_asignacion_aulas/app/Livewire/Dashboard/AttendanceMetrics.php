<?php

namespace App\Livewire\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\Assignment;
use App\Models\User;
use App\Models\Subject;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceMetrics extends Component
{
    use WithPagination;

    // Propiedades para la tabla de asistencias
    public $search = '';
    public $filterDocente = '';
    public $filterGrupo = '';
    public $filterMateria = '';
    public $filterFechaInicio = '';
    public $filterFechaFin = '';
    public $perPage = 10;
    public $showModal = false;
    public $selectedDocenteId = null;

    protected $paginationTheme = 'tailwind';
    protected $queryString = ['search', 'perPage'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Ejecutar el comando de marcar ausencias automáticamente al cargar el dashboard
        $this->markAbsentAttendances();
    }

    public function render(): View
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        // KPIs
        $asistenciasHoy = AttendanceRecord::whereDate('created_at', $today)
            ->whereIn('status', ['on_time', 'late'])
            ->count();

        $asistenciasAyer = AttendanceRecord::whereDate('created_at', $yesterday)
            ->whereIn('status', ['on_time', 'late'])
            ->count();

        $retrasosHoy = AttendanceRecord::whereDate('created_at', $today)
            ->where('status', 'late')
            ->count();

        $retrasosAyer = AttendanceRecord::whereDate('created_at', $yesterday)
            ->where('status', 'late')
            ->count();

        $inasistenciasHoy = AttendanceRecord::whereDate('created_at', $today)
            ->where('status', 'absent')
            ->count();

        $inasistenciasAyer = AttendanceRecord::whereDate('created_at', $yesterday)
            ->where('status', 'absent')
            ->count();

        // Sesiones programadas esta semana
        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $daysThisWeek = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            if ($date->lte($endOfWeek)) {
                $daysThisWeek[] = $dayNames[$date->dayOfWeek];
            }
        }

        $sesionesSemanales = Assignment::whereHas('academicManagement', function ($query) use ($today) {
            $query->where('start_date', '<=', $today)
                  ->where('end_date', '>=', $today);
        })
        ->whereHas('daySchedule.day', function ($query) use ($daysThisWeek) {
            $query->whereIn('name', $daysThisWeek);
        })
        ->count();

        // Calcular tendencias
        $tendenciaAsistencias = $this->calcularTendencia($asistenciasHoy, $asistenciasAyer);
        $tendenciaRetrasos = $this->calcularTendencia($retrasosHoy, $retrasosAyer);
        $tendenciaInasistencias = $this->calcularTendencia($inasistenciasHoy, $inasistenciasAyer);

        // Gráficos
        $topDocentes = $this->getTopDocentes();
        $distribucionEstados = $this->getDistribucionEstados();
        $tendenciaSemanal = $this->getTendenciaSemanal();

        // Datos para la tabla de asistencias
        $attendanceData = $this->getAttendanceData();
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('name', 'Docente');
        })->orderBy('name')->get();
        $grupos = Group::orderBy('name')->get();
        $materias = Subject::orderBy('name')->get();
        $docenteDetails = $this->showModal ? $this->getDocenteDetails() : collect();
        $selectedDocente = $this->selectedDocenteId ? User::find($this->selectedDocenteId) : null;

        return view('livewire.dashboard.attendance-metrics', [
            // KPIs
            'asistenciasHoy' => $asistenciasHoy,
            'retrasosHoy' => $retrasosHoy,
            'inasistenciasHoy' => $inasistenciasHoy,
            'sesionesSemanales' => $sesionesSemanales,
            'tendenciaAsistencias' => $tendenciaAsistencias,
            'tendenciaRetrasos' => $tendenciaRetrasos,
            'tendenciaInasistencias' => $tendenciaInasistencias,
            // Gráficos
            'topDocentes' => $topDocentes,
            'distribucionEstados' => $distribucionEstados,
            'tendenciaSemanal' => $tendenciaSemanal,
            // Tabla
            'attendanceRecords' => $attendanceData,
            'docentes' => $docentes,
            'grupos' => $grupos,
            'materias' => $materias,
            'docenteDetails' => $docenteDetails,
            'selectedDocente' => $selectedDocente,
        ]);
    }

    private function calcularTendencia($hoy, $ayer): array
    {
        if ($ayer == 0) {
            return [
                'porcentaje' => $hoy > 0 ? 100 : 0,
                'direccion' => $hoy > 0 ? 'up' : 'neutral'
            ];
        }

        $cambio = (($hoy - $ayer) / $ayer) * 100;

        return [
            'porcentaje' => abs(round($cambio, 1)),
            'direccion' => $cambio > 0 ? 'up' : ($cambio < 0 ? 'down' : 'neutral')
        ];
    }

    private function getTopDocentes()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $docentes = User::select('users.id', 'users.name')
            ->join('attendance_records', 'users.id', '=', 'attendance_records.user_id')
            ->whereBetween('attendance_records.created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw('
                COUNT(CASE WHEN attendance_records.status IN (\'on_time\', \'late\') THEN 1 END) as asistencias,
                COUNT(*) as total_sesiones,
                CASE
                    WHEN COUNT(*) > 0 THEN ROUND((COUNT(CASE WHEN attendance_records.status IN (\'on_time\', \'late\') THEN 1 END) * 100.0 / COUNT(*)), 1)
                    ELSE 0
                END as porcentaje
            ')
            ->groupBy('users.id', 'users.name')
            ->having(DB::raw('COUNT(*)'), '>', 0)
            ->orderByDesc('porcentaje')
            ->limit(10)
            ->get();

        return [
            'labels' => $docentes->pluck('name')->toArray(),
            'data' => $docentes->pluck('porcentaje')->toArray(),
            'colors' => $docentes->map(function ($docente) {
                if ($docente->porcentaje >= 90) return '#10b981'; // green
                if ($docente->porcentaje >= 75) return '#fbbf24'; // yellow
                return '#ef4444'; // red
            })->toArray()
        ];
    }

    private function getDistribucionEstados()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $estados = AttendanceRecord::selectRaw('
                status,
                COUNT(*) as total
            ')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('status')
            ->get();

        $aTiempo = $estados->where('status', 'on_time')->first()->total ?? 0;
        $tarde = $estados->where('status', 'late')->first()->total ?? 0;
        $ausente = $estados->where('status', 'absent')->first()->total ?? 0;
        $total = $aTiempo + $tarde + $ausente;

        return [
            'labels' => ['A tiempo', 'Tarde', 'Ausente'],
            'data' => [$aTiempo, $tarde, $ausente],
            'total' => $total,
            'colors' => ['#10b981', '#fbbf24', '#ef4444']
        ];
    }

    private function getTendenciaSemanal()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $datos = AttendanceRecord::selectRaw('
                DATE(created_at) as fecha,
                COUNT(CASE WHEN status = \'on_time\' THEN 1 END) as a_tiempo,
                COUNT(CASE WHEN status = \'late\' THEN 1 END) as retrasos,
                COUNT(CASE WHEN status = \'absent\' THEN 1 END) as ausentes,
                COUNT(*) as total
            ')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        // Crear array con todos los días de la semana
        $diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $asistencias = array_fill(0, 7, 0);
        $retrasos = array_fill(0, 7, 0);

        foreach ($datos as $dato) {
            $fecha = Carbon::parse($dato->fecha);
            $dayIndex = $fecha->dayOfWeek === 0 ? 6 : $fecha->dayOfWeek - 1; // Ajustar domingo

            if ($dato->total > 0) {
                // Porcentaje de asistencias (a tiempo + tarde)
                $totalAsistencias = $dato->a_tiempo + $dato->retrasos;
                $asistencias[$dayIndex] = round(($totalAsistencias / $dato->total) * 100, 1);

                // Porcentaje de retrasos
                $retrasos[$dayIndex] = round(($dato->retrasos / $dato->total) * 100, 1);
            }
        }

        return [
            'labels' => $diasSemana,
            'asistencias' => $asistencias,
            'retrasos' => $retrasos
        ];
    }

    /**
     * Marcar automáticamente las ausencias de clases expiradas
     */
    private function markAbsentAttendances()
    {
        try {
            \Artisan::call('attendance:mark-absent');
        } catch (\Exception $e) {
            \Log::error('Error al ejecutar attendance:mark-absent desde AttendanceMetrics: ' . $e->getMessage());
        }
    }

    // Métodos para la tabla de asistencias
    private function getAttendanceData()
    {
        // Subconsulta para obtener datos consolidados por docente
        $subquery = DB::table('users')
            ->join('user_subjects', 'users.id', '=', 'user_subjects.user_id')
            ->join('subjects', 'user_subjects.subject_id', '=', 'subjects.id')
            ->join('assignments', 'user_subjects.id', '=', 'assignments.user_subject_id')
            ->join('groups', 'assignments.group_id', '=', 'groups.id')
            ->leftJoin('attendance_records', function ($join) {
                $join->on('assignments.id', '=', 'attendance_records.assignment_id')
                    ->on('users.id', '=', 'attendance_records.user_id');
            })
            ->select(
                'users.id as docente_id',
                'users.name as docente_name',
                'assignments.id as assignment_id',
                'attendance_records.status'
            );

        // Aplicar filtros en la subconsulta
        if (!empty($this->filterDocente)) {
            $subquery->where('users.id', $this->filterDocente);
        }

        if (!empty($this->filterGrupo)) {
            $subquery->where('groups.id', $this->filterGrupo);
        }

        if (!empty($this->filterMateria)) {
            $subquery->where('subjects.id', $this->filterMateria);
        }

        if (!empty($this->filterFechaInicio) && !empty($this->filterFechaFin)) {
            $subquery->whereBetween('attendance_records.scan_time', [
                Carbon::parse($this->filterFechaInicio)->startOfDay(),
                Carbon::parse($this->filterFechaFin)->endOfDay()
            ]);
        }

        // Consulta principal agrupada solo por docente
        $query = DB::table(DB::raw("({$subquery->toSql()}) as data"))
            ->mergeBindings($subquery)
            ->select(
                'docente_id',
                'docente_name',
                DB::raw('COUNT(DISTINCT assignment_id) as total_sesiones'),
                DB::raw('COUNT(CASE WHEN status IN (\'on_time\', \'late\') THEN 1 END) as asistencias'),
                DB::raw('COUNT(CASE WHEN status = \'late\' THEN 1 END) as retrasos'),
                DB::raw('COUNT(CASE WHEN status = \'absent\' THEN 1 END) as inasistencias'),
                DB::raw('ROUND((COUNT(CASE WHEN status IN (\'on_time\', \'late\') THEN 1 END)::numeric / NULLIF(COUNT(DISTINCT assignment_id), 0)) * 100, 1) as porcentaje_asistencia')
            )
            ->groupBy('docente_id', 'docente_name');

        // Aplicar filtro de búsqueda
        if (!empty($this->search)) {
            $query->where('docente_name', 'ILIKE', '%' . $this->search . '%');
        }

        return $query->orderBy('porcentaje_asistencia', 'DESC')->paginate($this->perPage);
    }

    public function getDocenteDetails()
    {
        if (!$this->selectedDocenteId) {
            return collect();
        }

        return DB::table('users')
            ->join('user_subjects', 'users.id', '=', 'user_subjects.user_id')
            ->join('subjects', 'user_subjects.subject_id', '=', 'subjects.id')
            ->join('assignments', 'user_subjects.id', '=', 'assignments.user_subject_id')
            ->join('groups', 'assignments.group_id', '=', 'groups.id')
            ->leftJoin('attendance_records', function ($join) {
                $join->on('assignments.id', '=', 'attendance_records.assignment_id')
                    ->on('users.id', '=', 'attendance_records.user_id');
            })
            ->select(
                'subjects.name as materia_name',
                'subjects.code as materia_code',
                'groups.name as grupo_name',
                DB::raw('COUNT(DISTINCT assignments.id) as total_sesiones'),
                DB::raw('COUNT(CASE WHEN attendance_records.status IN (\'on_time\', \'late\') THEN 1 END) as asistencias'),
                DB::raw('COUNT(CASE WHEN attendance_records.status = \'late\' THEN 1 END) as retrasos'),
                DB::raw('COUNT(CASE WHEN attendance_records.status = \'absent\' THEN 1 END) as inasistencias'),
                DB::raw('ROUND((COUNT(CASE WHEN attendance_records.status IN (\'on_time\', \'late\') THEN 1 END)::numeric / NULLIF(COUNT(DISTINCT assignments.id), 0)) * 100, 1) as porcentaje_asistencia')
            )
            ->where('users.id', $this->selectedDocenteId)
            ->groupBy('subjects.id', 'subjects.name', 'subjects.code', 'groups.id', 'groups.name')
            ->orderBy('subjects.name')
            ->get();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterDocente', 'filterGrupo', 'filterMateria', 'filterFechaInicio', 'filterFechaFin']);
        $this->resetPage();
    }

    public function showDetails($docenteId)
    {
        $this->selectedDocenteId = $docenteId;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDocenteId = null;
    }
}
