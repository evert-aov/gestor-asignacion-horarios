<?php

namespace App\Livewire\AcademicLogistics;

use App\Livewire\AcademicLogistics\Forms\ManualAssignmentForm;
use App\Models\AcademicManagement;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\DaySchedule;
use App\Models\UserSubject;
use App\Models\Group;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManualScheduleAssignment extends Component
{
    use WithPagination;

    protected $listeners = ['refreshComponent' => 'render'];
    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $editing = null;
    public bool $show = false;

    public ManualAssignmentForm $form;

    public $allUserSubject = [];
    public $allDaySchedule = [];
    public $allClassroom = [];
    public $allAcademic = [];
    public $allGroups = [];
    public $schedules = [
        ['day_schedule_id' => null, 'classroom_id' => null],
        ['day_schedule_id' => null, 'classroom_id' => null],
        ['day_schedule_id' => null, 'classroom_id' => null],
        ['day_schedule_id' => null, 'classroom_id' => null],
        ['day_schedule_id' => null, 'classroom_id' => null],
        ['day_schedule_id' => null, 'classroom_id' => null],
    ];

    // Mapeo de día en español a DaySchedule que corresponden a ese día
    private $daySchedulesByDayName = [];

    public function render(): View
    {
        $assignments = $this->getGroupedAssignments();

        return view(
            'livewire.academic-logistics.manual-schedule-assigment.manual-schedule-assignment',
            compact(
                'assignments',
                'this'
            )
        );
    }

    private function getGroupedAssignments(): Collection
    {
        $assignments = Assignment::query()
            ->with([
                'userSubject.user',
                'userSubject.subject',
                'group',
                'daySchedule.day',
                'daySchedule.schedule',
                'classroom.module',
                'academicManagement'
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('userSubject.subject', function ($subQuery) {
                        $subQuery->where('code', 'like', '%' . $this->search . '%')
                            ->orWhere('name', 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('group', function ($groupQuery) {
                            $groupQuery->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('userSubject.user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('last_name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('classroom', function ($classroomQuery) {
                            $classroomQuery->where('number', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->get()
            ->groupBy(function ($assignment) {
                return $assignment->user_subject_id . '-' . $assignment->group_id . '-' . $assignment->academic_management_id;
            });

        return $assignments->map(function ($group) {
            $first = $group->first();
            $userSubject = $first->userSubject;


            // Ordenar por día de la semana (usando nombres en inglés)
            $schedules = $group->sortBy(function ($assignment) {
                // Asegúrate de que la relación existe antes de acceder a ella
                if (!$assignment->daySchedule || !$assignment->daySchedule->day) {
                    return 8; // Enviar al final si hay datos corruptos
                }

                $dayOrder = [
                    'Monday' => 1,
                    'Tuesday' => 2,
                    'Wednesday' => 3,
                    'Thursday' => 4,
                    'Friday' => 5,
                    'Saturday' => 6,
                    'Sunday' => 7
                ];
                // Usa los nombres en inglés para ordenar
                return $dayOrder[$assignment->daySchedule->day->name] ?? 8;
            });

            // Construir un arreglo fijo por día (0 = Lunes ... 5 = Sábado)
            $schedulesByDay = array_fill(0, 6, null);
            foreach ($group as $assignment) {
                if ($assignment->daySchedule && $assignment->daySchedule->day) {
                    $dayId = (int) $assignment->daySchedule->day->id; // 1=Lunes
                    if ($dayId >= 1 && $dayId <= 6) {
                        $schedulesByDay[$dayId - 1] = $assignment;
                    }
                }
            }

            return [
                'subject_code' => $userSubject->subject->code,
                'subject_group' => $first->group->name,
                'subject_name' => $userSubject->subject->name,
                'teacher_name' => $userSubject->user->name . ' ' . $userSubject->user->last_name,
                'schedules' => $schedules,
                'schedules_by_day' => $schedulesByDay, 
                'ids' => $group->pluck('id')->toArray()
            ];
        })->values();
    }

    public function getRelations(): void
    {
        // Obtener todas las combinaciones de docente-materia
        $this->allUserSubject = UserSubject::with(['subject', 'user'])
            ->whereHas('subject', function ($q) {
                $q->where('is_active', true);
            })
            ->whereHas('user', function ($q) {
                $q->where('is_active', true);
            })
            ->get();

        $this->allDaySchedule = DaySchedule::with(['day', 'schedule'])->get();
        $this->allClassroom = Classroom::with('module')->where('is_active', true)->get();
        $this->allAcademic = AcademicManagement::all();
        $this->allGroups = Group::where('is_active', true)->get();
    }

    public function mount(): void
    {
        $this->getRelations();
    }

    public function openCreateModal(): void
    {
       
        $this->getRelations();

        $this->editing = null;
        $this->form->reset();
        $this->resetSchedules();
        $this->show = true;
    }

    public function edit($id): void
    {
        
        $this->getRelations();

        //Obtener la asignación base
        $assignment = Assignment::with(['userSubject', 'group', 'classroom', 'daySchedule', 'academicManagement'])
            ->findOrFail($id);

        $this->editing = $id;
        $this->form->setAssignment($assignment);

        //Cargar TODAS las asignaciones relacionadas
        $allAssignments = Assignment::where('user_subject_id', $assignment->user_subject_id)
            ->where('group_id', $assignment->group_id)
            ->where('academic_management_id', $assignment->academic_management_id)
            ->with('daySchedule.day')
            ->get();

        //Resetear los 6 slots de horarios
        $this->resetSchedules();

        //    Colocar cada asignación en su índice de día correcto
        foreach ($allAssignments as $assign) {

            // Asegurarnos que la relación cargó correctamente
            if ($assign->daySchedule && $assign->daySchedule->day) {

                // Obtenemos el ID del día (1=Lunes, 2=Martes, ...)
                $dayId = $assign->daySchedule->day->id;

                // Calculamos el índice del array (0=Lunes, 1=Martes, ...)
                $index = $dayId - 1;

                // Asignamos al slot correcto (solo si es de Lunes a Sábado: 0 a 5)
                if ($index >= 0 && $index < 6) {
                    $this->schedules[$index] = [
                        'day_schedule_id' => $assign->day_schedule_id,
                        'classroom_id' => $assign->classroom_id
                    ];
                }
            }
        }

        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->form->reset();
        $this->resetSchedules();
        $this->editing = null;
        $this->dispatch('modal-closed');
    }

    public function save(): void
    {
        //dd($this->all());
        $this->validate([
            'form.user_subject_id' => 'required|exists:user_subjects,id',
            'form.group_id' => 'required|exists:groups,id',
            'form.academic_id' => 'nullable|exists:academic_management,id',
            'schedules.*.day_schedule_id' => 'nullable|exists:day_schedules,id',
            'schedules.*.classroom_id' => 'nullable|exists:classrooms,id',
        ], [
            'schedules.*.day_schedule_id.exists' => 'Uno de los horarios seleccionados no es válido.',
            'schedules.*.classroom_id' => 'Debe seleccionar un aula para el horario.',
            'schedules.*.classroom_id.exists' => 'Uno de los aulas seleccionadas no es válida.',
        ]);

        try {
            // Validar que al menos un horario esté seleccionado
            $selectedSchedules = array_filter($this->schedules, function ($schedule) {
                return !empty($schedule['day_schedule_id']) && !empty($schedule['classroom_id']);
            });

            if (empty($selectedSchedules)) {
                // (Opcional) Mejora el mensaje de error
                throw new \Exception('Debe seleccionar al menos un par completo de horario y aula.');
            }

            $this->validateConflicts();

            $userSubject = UserSubject::find($this->form->user_subject_id);

            if ($this->editing) {
                // Modo edición: actualizar solo los cambios
                $this->updateExistingAssignments($selectedSchedules, $userSubject);
            } else {
                // Modo creación: crear nuevas asignaciones
                $this->createNewAssignments($selectedSchedules, $userSubject);
            }

            session()->flash(
                'assignment_message',
                $this->editing ? 'Asignación actualizada correctamente.' : 'Asignación creada correctamente.'
            );

            $this->closeModal();
            $this->getRelations();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    private function updateExistingAssignments(array $selectedSchedules, UserSubject $userSubject): void
    {
        // Obtener asignaciones existentes
        $existingAssignments = Assignment::where('user_subject_id', $this->form->user_subject_id)
            ->where('group_id', $this->form->group_id)
            ->where('academic_management_id', $this->form->academic_id)
            ->get();

        $existingScheduleIds = [];
        //        $processedSchedules = [];

        // Actualizar o crear asignaciones
        foreach ($selectedSchedules as $schedule) {
            if ($schedule['day_schedule_id'] && $schedule['classroom_id']) {
                $scheduleKey = $schedule['day_schedule_id'] . '-' . $schedule['classroom_id'];

                // Buscar si ya existe esta asignación
                $existingAssignment = $existingAssignments->first(function ($assignment) use ($schedule) {
                    return $assignment->day_schedule_id == $schedule['day_schedule_id']
                        && $assignment->classroom_id == $schedule['classroom_id'];
                });

                if ($existingAssignment) {
                    // Ya existe, mantenerla
                    $existingScheduleIds[] = $existingAssignment->id;
                    //  $processedSchedules[] = $existingAssignment;
                } else {
                    // Crear nueva asignación
                    $newAssignment = Assignment::create([
                        'user_subject_id' => $this->form->user_subject_id,
                        'subject_id' => $userSubject->subject_id,
                        'group_id' => $this->form->group_id,
                        'classroom_id' => $schedule['classroom_id'],
                        'day_schedule_id' => $schedule['day_schedule_id'],
                        'academic_management_id' => $this->form->academic_id,
                    ]);
                    $existingScheduleIds[] = $newAssignment->id;
                    // $processedSchedules[] = $newAssignment;
                }
            }
        }

        // Eliminar asignaciones que ya no están en los horarios seleccionados
        $assignmentsToDelete = $existingAssignments->whereNotIn('id', $existingScheduleIds);
        foreach ($assignmentsToDelete as $assignment) {
            $assignment->delete();
        }
    }

    private function createNewAssignments(array $selectedSchedules, UserSubject $userSubject): void
    {
        //dd($this->all());
        foreach ($selectedSchedules as $schedule) {
            if ($schedule['day_schedule_id'] && $schedule['classroom_id']) {
                Assignment::create([
                    'user_subject_id' => $this->form->user_subject_id,
                    'subject_id' => $userSubject->subject_id,
                    'group_id' => $this->form->group_id,
                    'classroom_id' => $schedule['classroom_id'],
                    'day_schedule_id' => $schedule['day_schedule_id'],
                    'academic_management_id' => $this->form->academic_id,
                ]);
            }
        }
    }

    /**
     * @throws Exception
     */
    private function validateConflicts(): void
    {
        $selectedSchedules = array_filter($this->schedules, function ($schedule) {
            return !empty($schedule['day_schedule_id']) && !empty($schedule['classroom_id']);
        });

        // Obtener IDs de asignaciones existentes (en modo edición)
        $existingAssignmentIds = [];
        if ($this->editing) {
            $existingAssignments = Assignment::where('user_subject_id', $this->form->user_subject_id)
                ->where('group_id', $this->form->group_id)
                ->where('academic_management_id', $this->form->academic_id)
                ->get();
            $existingAssignmentIds = $existingAssignments->pluck('id')->toArray();
        }

        foreach ($selectedSchedules as $dayName => $schedule) {
            // Validar conflicto de aula
            $classroomConflictQuery = Assignment::where('classroom_id', $schedule['classroom_id'])
                ->where('day_schedule_id', $schedule['day_schedule_id'])
                ->where('academic_management_id', $this->form->academic_id);

            if ($this->editing && !empty($existingAssignmentIds)) {
                $classroomConflictQuery->whereNotIn('id', $existingAssignmentIds);
            }

            if ($classroomConflictQuery->exists()) {
                $daySchedule = DaySchedule::with(['day', 'schedule'])->find($schedule['day_schedule_id']);
                $classroom = Classroom::find($schedule['classroom_id']);
                throw new \Exception(
                    "El aula {$classroom->number} ya está ocupada el {$daySchedule->day->name} de " .
                        date('H:i', strtotime($daySchedule->schedule->start)) . " a " .
                        date('H:i', strtotime($daySchedule->schedule->end)) .
                        " para el periodo académico seleccionado."
                );
            }
        }

        // Validar conflicto de horario del docente
        $userSubject = UserSubject::find($this->form->user_subject_id);
        if ($userSubject) {
            foreach ($selectedSchedules as $dayName => $schedule) {
                $teacherConflictQuery = Assignment::whereHas('userSubject', function ($q) use ($userSubject) {
                    $q->where('user_id', $userSubject->user_id);
                })
                    ->where('day_schedule_id', $schedule['day_schedule_id'])
                    ->where('academic_management_id', $this->form->academic_id);

                if ($this->editing && !empty($existingAssignmentIds)) {
                    $teacherConflictQuery->whereNotIn('id', $existingAssignmentIds);
                }

                if ($teacherConflictQuery->exists()) {
                    $conflictingAssignment = $teacherConflictQuery->with(['userSubject.subject', 'daySchedule.day', 'daySchedule.schedule'])->first();
                    $daySchedule = DaySchedule::with(['day', 'schedule'])->find($schedule['day_schedule_id']);

                    throw new \Exception(
                        "El docente {$userSubject->user->name} {$userSubject->user->last_name} ya tiene asignada la materia " .
                            "{$conflictingAssignment->userSubject->subject->name} el {$daySchedule->day->name} de " .
                            date('H:i', strtotime($daySchedule->schedule->start)) . " a " .
                            date('H:i', strtotime($daySchedule->schedule->end)) .
                            " para el periodo académico seleccionado."
                    );
                }
            }
        }


        // Validar conflicto de materia-grupo
        $userSubject = UserSubject::find($this->form->user_subject_id);
        if ($userSubject) {
            $subjectGroupConflictQuery = Assignment::where('subject_id', $userSubject->subject_id)
                ->where('group_id', $this->form->group_id)
                ->where('academic_management_id', $this->form->academic_id);

            if ($this->editing) {
                $subjectGroupConflictQuery->where('user_subject_id', '!=', $this->form->user_subject_id);
            }

            if ($subjectGroupConflictQuery->exists()) {
                throw new \Exception('Esta materia ya está asignada a este grupo en el periodo académico seleccionado.');
            }
        }
    }

    public function delete($id): void
    {
        try {
            $assignment = Assignment::findOrFail($id);

            Assignment::where('user_subject_id', $assignment->user_subject_id)
                ->where('group_id', $assignment->group_id)
                ->where('academic_management_id', $assignment->academic_management_id)
                ->delete();

            session()->flash('message', 'Asignación eliminada correctamente.');
            $this->getRelations();
        } catch (Exception $e) {
            session()->flash('error', 'Error al eliminar la asignación: ' . $e->getMessage());
        }
    }

    private function resetSchedules(): void
    {
        $this->schedules = [
            ['day_schedule_id' => null, 'classroom_id' => null],
            ['day_schedule_id' => null, 'classroom_id' => null],
            ['day_schedule_id' => null, 'classroom_id' => null],
            ['day_schedule_id' => null, 'classroom_id' => null],
            ['day_schedule_id' => null, 'classroom_id' => null],
            ['day_schedule_id' => null, 'classroom_id' => null],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }
}
