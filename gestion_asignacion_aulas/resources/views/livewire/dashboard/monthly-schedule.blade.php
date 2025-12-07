<div>
    <x-container-second-div>
            <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        @if($showDaySchedule)
                            @if($viewMode === 'schedule')
                                Horarios del Día
                            @else
                                Ocupación de Aulas
                            @endif
                        @else
                            Calendario Mensual de Horarios
                        @endif
                    </h3>
                </div>

                @if($showDaySchedule)
                    <div class="flex items-center gap-3">
                        <!-- Selector de modo de vista -->
                        <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-lg">
                            <button wire:click="switchViewMode('schedule')"
                                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ $viewMode === 'schedule' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                                <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Horarios
                            </button>
                            <button wire:click="switchViewMode('occupancy')"
                                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ $viewMode === 'occupancy' ? 'bg-blue-600 text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                                <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Aulas
                            </button>
                        </div>

                        <button wire:click="closeDaySchedule"
                                class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm">
                            ← Volver
                        </button>
                    </div>
                @endif
            </div>            <!-- Filtros y navegación -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-4">
                <!-- Filtro Docente -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Docente
                    </label>
                    <select wire:model.live="filterDocente"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}">{{ $docente->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Grupo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Grupo
                    </label>
                    <select wire:model.live="filterGrupo"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todos</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Aula -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Aula
                    </label>
                    <select wire:model.live="filterAula"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Todas</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}">Aula {{ $aula->number }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Navegación de meses/días -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ $showDaySchedule ? 'Día' : 'Mes' }}
                    </label>
                    <div class="flex gap-2">
                        <button wire:click="previousMonth"
                                wire:loading.attr="disabled"
                                class="flex-1 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors disabled:opacity-50"
                                title="{{ $showDaySchedule ? 'Día anterior' : 'Mes anterior' }}">
                            <span wire:loading.remove wire:target="previousMonth">←</span>
                            <span wire:loading wire:target="previousMonth">...</span>
                        </button>
                        <button wire:click="currentMonth"
                                wire:loading.attr="disabled"
                                class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm disabled:opacity-50"
                                title="{{ $showDaySchedule ? 'Mes actual' : 'Mes actual' }}">
                            <span wire:loading.remove wire:target="currentMonth">Actual</span>
                            <span wire:loading wire:target="currentMonth">...</span>
                        </button>
                        <button wire:click="nextMonth"
                                wire:loading.attr="disabled"
                                class="flex-1 px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors disabled:opacity-50"
                                title="{{ $showDaySchedule ? 'Día siguiente' : 'Mes siguiente' }}">
                            <span wire:loading.remove wire:target="nextMonth">→</span>
                            <span wire:loading wire:target="nextMonth">...</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Indicador de mes actual -->
            <div class="text-sm text-gray-600 dark:text-gray-400 capitalize">
                {{ $currentMonth->locale('es')->isoFormat('MMMM YYYY') }}
                @if($showDaySchedule)
                    - {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                @endif
            </div>
        </div>

        @if(!$showDaySchedule)
            <!-- Calendario Mensual -->
            <div class="overflow-x-auto" wire:key="calendar-{{ $monthOffset }}">
                <div class="min-w-full">
                    <!-- Días de la semana (header) -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Lun</div>
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Mar</div>
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Mié</div>
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Jue</div>
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Vie</div>
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Sáb</div>
                        <div class="text-center text-xs font-semibold text-gray-700 dark:text-gray-300 py-2">Dom</div>
                    </div>

                    <!-- Días del calendario -->
                    @foreach($weeks as $weekIndex => $week)
                        <div class="grid grid-cols-7 gap-1 mb-1">
                            @foreach($week as $dayIndex => $day)
                                @php
                                    $isSelected = $selectedDate && \Carbon\Carbon::parse($selectedDate)->isSameDay($day['date']);
                                @endphp
                                <div
                                    wire:key="day-{{ $day['date']->format('Y-m-d') }}"
                                    wire:click="selectDate('{{ $day['date']->format('Y-m-d') }}')"
                                    class="min-h-[80px] p-2 border rounded-lg cursor-pointer transition-all hover:shadow-md
                                        {{ $day['isToday'] ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700' }}
                                        {{ !$day['isCurrentMonth'] ? 'opacity-40' : '' }}
                                        {{ $isSelected ? 'ring-2 ring-blue-500 bg-blue-100 dark:bg-blue-900/30' : 'bg-white dark:bg-gray-900' }}
                                        {{ $day['assignmentsCount'] > 0 ? 'hover:border-blue-400' : '' }}">

                                    <!-- Número del día -->
                                    <div class="text-xs font-medium {{ $day['isToday'] ? 'text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300' }} mb-1">
                                        {{ $day['date']->day }}
                                    </div>

                                    <!-- Indicador de clases -->
                                    @if($day['assignmentsCount'] > 0)
                                        <div class="text-[10px] text-center">
                                            <div class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                                {{ $day['assignmentsCount'] }} clase{{ $day['assignmentsCount'] > 1 ? 's' : '' }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                <strong>Tip:</strong> Haz clic en un día para ver los horarios detallados en formato tabla
            </div>

        @else
            @if($viewMode === 'schedule')
                <!-- Tabla de Horarios del Día -->
                @if($daySchedule && $daySchedule->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Horario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Materia</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Docente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grupo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aula</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($daySchedule as $assignment)
                                @php
                                    $status = $this->getAttendanceStatus($assignment);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ substr($assignment->daySchedule->schedule->start, 0, 5) }} -
                                        {{ substr($assignment->daySchedule->schedule->end, 0, 5) }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div class="font-medium">{{ $assignment->userSubject->subject->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $assignment->userSubject->subject->code }}</div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $assignment->userSubject->user->name }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $assignment->group->name }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        Aula {{ $assignment->classroom->number }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status['bgColor'] }} {{ $status['color'] }}">
                                            @if($status['status'] === 'on_time')
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay clases programadas para este día</p>
                </div>
            @endif

            @else
                <!-- Vista de Ocupación de Aulas -->
                @if($classroomOccupancy && isset($classroomOccupancy['data']) && $classroomOccupancy['data']->count() > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:divide-x lg:divide-gray-300 dark:lg:divide-gray-700">
                        @foreach($classroomOccupancy['data'] as $occupancy)
                            <div class="lg:px-4">
                                <table class="min-w-full">
                                    <tbody class="bg-white dark:bg-gray-900">
                                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                                <!-- Aula -->
                                                <td class="px-3 py-2 whitespace-nowrap w-2/5">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 rounded flex items-center justify-center {{ $occupancy['isOccupied'] ? 'bg-red-100 dark:bg-red-900/30' : 'bg-green-100 dark:bg-green-900/30' }}">
                                                            <svg class="w-3.5 h-3.5 {{ $occupancy['isOccupied'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs font-bold text-gray-900 dark:text-gray-100">{{ $occupancy['classroom']->number }}</p>
                                                            <p class="text-[10px] text-gray-500 dark:text-gray-400">{{ $occupancy['classroom']->infrastructure->code ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Capacidad -->
                                                <td class="px-3 py-2 whitespace-nowrap text-center text-xs text-gray-900 dark:text-gray-100 w-1/6">
                                                    {{ $occupancy['classroom']->capacity }}
                                                </td>

                                                <!-- Estado -->
                                                <td class="px-3 py-2 whitespace-nowrap text-center w-1/6">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $occupancy['isOccupied'] ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' }}">
                                                        {{ $occupancy['isOccupied'] ? 'Ocupada' : 'Libre' }}
                                                    </span>
                                                </td>

                                                <!-- Acciones -->
                                                <td class="px-3 py-2 text-center w-1/6">
                                                    @if($occupancy['isOccupied'])
                                                        <button wire:click="toggleClassroomDetails({{ $occupancy['classroom']->id }})"
                                                                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-xs font-medium">
                                                            @if(in_array($occupancy['classroom']->id, $expandedClassrooms))
                                                                Ocultar
                                                            @else
                                                                Ver más
                                                            @endif
                                                        </button>
                                                    @else
                                                        <span class="text-xs text-gray-400">-</span>
                                                    @endif
                                                </td>
                                            </tr>

                                        <!-- Horarios expandidos -->
                                        @if($occupancy['isOccupied'] && in_array($occupancy['classroom']->id, $expandedClassrooms))
                                            <tr class="bg-gray-50 dark:bg-gray-800">
                                                <td colspan="4" class="px-3 py-2">
                                                    <div class="space-y-1.5">
                                                        @foreach($occupancy['assignments'] as $assignment)
                                                            @php
                                                                $status = $this->getAttendanceStatus($assignment);
                                                            @endphp
                                                            <div class="bg-white dark:bg-gray-900 rounded p-2 border-l-2 {{ $status['status'] === 'on_time' ? 'border-green-500' : ($status['status'] === 'late' ? 'border-yellow-500' : ($status['status'] === 'absent' ? 'border-red-500' : 'border-gray-400')) }}">
                                                                <div class="flex items-center justify-between mb-1">
                                                                    <span class="text-[10px] font-bold text-gray-900 dark:text-gray-100">
                                                                        {{ substr($assignment->daySchedule->schedule->start, 0, 5) }} - {{ substr($assignment->daySchedule->schedule->end, 0, 5) }}
                                                                    </span>
                                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium {{ $status['bgColor'] }} {{ $status['color'] }}">
                                                                        {{ $status['label'] }}
                                                                    </span>
                                                                </div>
                                                                <p class="text-[10px] font-medium text-gray-900 dark:text-gray-100">
                                                                    {{ $assignment->userSubject->subject->code }} - {{ $assignment->userSubject->subject->name }}
                                                                </p>
                                                                <p class="text-[9px] text-gray-600 dark:text-gray-400">
                                                                    Docente: {{ $assignment->userSubject->user->name }}
                                                                </p>
                                                                <p class="text-[9px] text-gray-600 dark:text-gray-400">
                                                                    Grupo: {{ $assignment->group->name }}
                                                                </p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Mostrando <span class="font-medium">{{ $classroomOccupancy['from'] }}</span> a <span class="font-medium">{{ $classroomOccupancy['to'] }}</span> de <span class="font-medium">{{ $classroomOccupancy['total'] }}</span> aulas
                        </div>
                        <div class="flex gap-2">
                            <button wire:click="previousOccupancyPage"
                                    @if($classroomOccupancy['currentPage'] <= 1) disabled @endif
                                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                Anterior
                            </button>
                            <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
                                Página {{ $classroomOccupancy['currentPage'] }} de {{ $classroomOccupancy['lastPage'] }}
                            </span>
                            <button wire:click="nextOccupancyPage"
                                    @if($classroomOccupancy['currentPage'] >= $classroomOccupancy['lastPage']) disabled @endif
                                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                Siguiente
                            </button>
                        </div>
                    </div>

                    <!-- Resumen de ocupación -->
                    @if($occupancyStats)
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Aulas</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $occupancyStats['total'] }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-red-200 dark:border-red-700">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Aulas Ocupadas</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $occupancyStats['occupied'] }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-green-200 dark:border-green-700">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Aulas Disponibles</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $occupancyStats['available'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay aulas registradas</p>
                    </div>
                @endif
            @endif
        @endif
    </x-container-second-div>
</div>
