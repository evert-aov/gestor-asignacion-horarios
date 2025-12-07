<?php

namespace App\Livewire\Dashboard;

use App\Models\Assignment;
use App\Models\Day;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Group;
use App\Models\Classroom;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class MonthlySchedule extends Component
{
    public $filterDocente = '';
    public $filterGrupo = '';
    public $filterAula = '';
    public $monthOffset = 0;
    public $selectedDate = null;
    public $showDaySchedule = false;
    public $viewMode = 'schedule'; // 'schedule' o 'occupancy'
    public $occupancyPage = 1;
    public $perPage = 20;
    public $expandedClassrooms = [];

    public function render(): View
    {
        $docentes = User::whereHas('roles', function ($query) {
            $query->where('name', 'Docente');
        })->orderBy('name')->get();

        $grupos = Group::orderBy('name')->get();
        $aulas = Classroom::orderBy('number')->get();

        // Obtener mes actual + offset
        $currentMonth = Carbon::now()->addMonths($this->monthOffset);
        $monthStart = $currentMonth->copy()->startOfMonth();
        $monthEnd = $currentMonth->copy()->endOfMonth();

        // Obtener primer día del calendario (puede ser del mes anterior)
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);

        // Obtener último día del calendario (puede ser del mes siguiente)
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

        // Generar array de días del calendario
        $calendarDays = [];
        $date = $calendarStart->copy();

        while ($date <= $calendarEnd) {
            $calendarDays[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month === $currentMonth->month,
                'isToday' => $date->isToday(),
                'dayName' => $date->locale('es')->dayName,
                'assignmentsCount' => $this->getAssignmentsCountForDate($date->copy())
            ];
            $date->addDay();
        }

        // Organizar en semanas (filas de 7 días)
        $weeks = array_chunk($calendarDays, 7);

        // Si hay un día seleccionado, obtener sus horarios
        $daySchedule = null;
        if ($this->selectedDate) {
            $daySchedule = $this->getDaySchedule(Carbon::parse($this->selectedDate));
        }

        // Si hay un día seleccionado y modo ocupación, obtener ocupación de aulas
        $classroomOccupancy = null;
        $occupancyStats = null;
        if ($this->selectedDate && $this->viewMode === 'occupancy') {
            $classroomOccupancy = $this->getClassroomOccupancy(Carbon::parse($this->selectedDate));
            if ($classroomOccupancy) {
                $occupancyStats = $this->getOccupancyStats(Carbon::parse($this->selectedDate));
            }
        }

        return view('livewire.dashboard.monthly-schedule', [
            'docentes' => $docentes,
            'grupos' => $grupos,
            'aulas' => $aulas,
            'currentMonth' => $currentMonth,
            'weeks' => $weeks,
            'daySchedule' => $daySchedule,
            'classroomOccupancy' => $classroomOccupancy,
            'occupancyStats' => $occupancyStats,
        ]);
    }

    private function getAssignmentsCountForDate($date)
    {
        $dayName = $date->format('l'); // Monday, Tuesday, etc.

        $query = Assignment::whereHas('daySchedule.day', function ($q) use ($dayName) {
            $q->where('name', $dayName);
        })
        ->whereHas('academicManagement', function ($q) use ($date) {
            $q->where('start_date', '<=', $date)
              ->where('end_date', '>=', $date);
        });

        // Aplicar filtros
        if (!empty($this->filterDocente)) {
            $query->whereHas('userSubject', function ($q) {
                $q->where('user_id', $this->filterDocente);
            });
        }

        if (!empty($this->filterGrupo)) {
            $query->where('group_id', $this->filterGrupo);
        }

        if (!empty($this->filterAula)) {
            $query->where('classroom_id', $this->filterAula);
        }

        return $query->count();
    }

    private function getDaySchedule($date)
    {
        $dayName = $date->format('l'); // Monday, Tuesday, etc.

        $query = Assignment::with([
            'userSubject.user',
            'userSubject.subject',
            'group',
            'classroom',
            'daySchedule.schedule',
            'daySchedule.day',
            'attendanceRecords' => function ($q) use ($date) {
                $q->whereDate('scan_time', $date)->latest('scan_time');
            }
        ])
        ->whereHas('daySchedule.day', function ($q) use ($dayName) {
            $q->where('name', $dayName);
        })
        ->whereHas('academicManagement', function ($q) use ($date) {
            $q->where('start_date', '<=', $date)
              ->where('end_date', '>=', $date);
        });

        // Aplicar filtros
        if (!empty($this->filterDocente)) {
            $query->whereHas('userSubject', function ($q) {
                $q->where('user_id', $this->filterDocente);
            });
        }

        if (!empty($this->filterGrupo)) {
            $query->where('group_id', $this->filterGrupo);
        }

        if (!empty($this->filterAula)) {
            $query->where('classroom_id', $this->filterAula);
        }

        return $query->get()->sortBy(function ($assignment) {
            return $assignment->daySchedule->schedule->start;
        });
    }

    public function getAttendanceStatus($assignment)
    {
        $latestAttendance = $assignment->attendanceRecords->first();

        if (!$latestAttendance) {
            return [
                'status' => 'pending',
                'label' => 'Pendiente',
                'color' => 'text-gray-600 dark:text-gray-400',
                'bgColor' => 'bg-gray-100 dark:bg-gray-800'
            ];
        }

        return match($latestAttendance->status) {
            'on_time' => [
                'status' => 'on_time',
                'label' => 'A tiempo',
                'color' => 'text-green-700 dark:text-green-400',
                'bgColor' => 'bg-green-100 dark:bg-green-900/30'
            ],
            'late' => [
                'status' => 'late',
                'label' => 'Retrasado',
                'color' => 'text-yellow-700 dark:text-yellow-400',
                'bgColor' => 'bg-yellow-100 dark:bg-yellow-900/30'
            ],
            'absent' => [
                'status' => 'absent',
                'label' => 'Ausente',
                'color' => 'text-red-700 dark:text-red-400',
                'bgColor' => 'bg-red-100 dark:bg-red-900/30'
            ],
            default => [
                'status' => 'unknown',
                'label' => 'Desconocido',
                'color' => 'text-gray-600 dark:text-gray-400',
                'bgColor' => 'bg-gray-100 dark:bg-gray-800'
            ]
        };
    }

    public function previousMonth()
    {
        if ($this->showDaySchedule) {
            // Si estamos viendo un día, navegar al día anterior
            $this->previousDay();
        } else {
            // Si estamos en el calendario, navegar al mes anterior
            $this->monthOffset--;
            $this->selectedDate = null;
            $this->showDaySchedule = false;
        }
    }

    public function nextMonth()
    {
        if ($this->showDaySchedule) {
            // Si estamos viendo un día, navegar al día siguiente
            $this->nextDay();
        } else {
            // Si estamos en el calendario, navegar al mes siguiente
            $this->monthOffset++;
            $this->selectedDate = null;
            $this->showDaySchedule = false;
        }
    }

    public function currentMonth()
    {
        $this->monthOffset = 0;
        $this->selectedDate = null;
        $this->showDaySchedule = false;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->showDaySchedule = true;
    }

    public function closeDaySchedule()
    {
        $this->reset(['selectedDate', 'showDaySchedule']);
    }

    public function switchViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->occupancyPage = 1;
        $this->expandedClassrooms = [];
    }

    public function toggleClassroomDetails($classroomId)
    {
        if (in_array($classroomId, $this->expandedClassrooms)) {
            $this->expandedClassrooms = array_filter($this->expandedClassrooms, fn($id) => $id !== $classroomId);
        } else {
            $this->expandedClassrooms[] = $classroomId;
        }
    }

    public function nextOccupancyPage()
    {
        $this->occupancyPage++;
    }

    public function previousOccupancyPage()
    {
        if ($this->occupancyPage > 1) {
            $this->occupancyPage--;
        }
    }

    public function previousDay()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate)->subDay();
            $this->selectedDate = $date->format('Y-m-d');
        }
    }

    public function nextDay()
    {
        if ($this->selectedDate) {
            $date = Carbon::parse($this->selectedDate)->addDay();
            $this->selectedDate = $date->format('Y-m-d');
        }
    }

    private function getClassroomOccupancy($date)
    {
        $dayName = $date->format('l'); // Monday, Tuesday, etc.

        // Obtener todas las aulas con paginación
        $allClassrooms = Classroom::with('infrastructure')
            ->where('is_active', true)
            ->orderBy('number')
            ->get();

        $occupancyData = [];

        foreach ($allClassrooms as $classroom) {
            // Buscar asignación para esta aula en este día
            $assignmentQuery = Assignment::with([
                'userSubject.user',
                'userSubject.subject',
                'group',
                'daySchedule.schedule',
                'daySchedule.day',
                'attendanceRecords' => function ($q) use ($date) {
                    $q->whereDate('scan_time', $date)->latest('scan_time');
                }
            ])
            ->where('classroom_id', $classroom->id)
            ->whereHas('daySchedule.day', function ($q) use ($dayName) {
                $q->where('name', $dayName);
            })
            ->whereHas('academicManagement', function ($q) use ($date) {
                $q->where('start_date', '<=', $date)
                  ->where('end_date', '>=', $date);
            });

            // Aplicar filtros
            if (!empty($this->filterDocente)) {
                $assignmentQuery->whereHas('userSubject', function ($q) {
                    $q->where('user_id', $this->filterDocente);
                });
            }

            if (!empty($this->filterGrupo)) {
                $assignmentQuery->where('group_id', $this->filterGrupo);
            }

            $assignments = $assignmentQuery->get()->sortBy(function ($assignment) {
                return $assignment->daySchedule->schedule->start;
            });

            $occupancyData[] = [
                'classroom' => $classroom,
                'assignments' => $assignments,
                'isOccupied' => $assignments->count() > 0,
                'totalAssignments' => $assignments->count()
            ];
        }

        $collection = collect($occupancyData);

        return [
            'data' => $collection->forPage($this->occupancyPage, $this->perPage),
            'total' => $collection->count(),
            'currentPage' => $this->occupancyPage,
            'lastPage' => ceil($collection->count() / $this->perPage),
            'from' => (($this->occupancyPage - 1) * $this->perPage) + 1,
            'to' => min($this->occupancyPage * $this->perPage, $collection->count())
        ];
    }

    private function getOccupancyStats($date)
    {
        $dayName = $date->format('l');

        $allClassrooms = Classroom::where('is_active', true)->get();
        $totalClassrooms = $allClassrooms->count();

        $occupiedClassrooms = 0;

        foreach ($allClassrooms as $classroom) {
            $hasAssignment = Assignment::where('classroom_id', $classroom->id)
                ->whereHas('daySchedule.day', function ($q) use ($dayName) {
                    $q->where('name', $dayName);
                })
                ->whereHas('academicManagement', function ($q) use ($date) {
                    $q->where('start_date', '<=', $date)
                      ->where('end_date', '>=', $date);
                })
                ->exists();

            if ($hasAssignment) {
                $occupiedClassrooms++;
            }
        }

        return [
            'total' => $totalClassrooms,
            'occupied' => $occupiedClassrooms,
            'available' => $totalClassrooms - $occupiedClassrooms
        ];
    }
}
