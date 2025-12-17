<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold">{{ __('Registrar Asistencia con QR') }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Mis clases de la semana - {{ now()->startOfWeek()->format('d/m/Y') }} al {{ now()->endOfWeek()->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-semibold">{{ $assignments->count() }}</span> {{ $assignments->count() === 1 ? 'clase' : 'clases' }} esta semana
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de asignaciones del día --}}
        @if($assignments->isEmpty())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay clases programadas</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No tienes asignaciones para esta semana.</p>
                    </div>
                </div>
            </div>
        @else
            {{-- Sección: Clases Pendientes --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div x-data="{ open: true }">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center cursor-pointer" @click="open = !open">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Clases Pendientes
                            </h3>
                            <span class="ml-3 px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-300 rounded-full">
                                {{ $pendingAssignments->count() }}
                            </span>
                        </div>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                    <div x-show="open" x-transition class="p-6">
                    @if($pendingAssignments->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay clases pendientes</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Materia
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Grupo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Día / Fecha
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Horario
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Aula
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($pendingAssignments as $assignment)
                                        <tr wire:key="pending-{{ $assignment->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $assignment->userSubject->subject->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $assignment->userSubject->subject->code }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $assignment->group->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $dayName = trim($assignment->daySchedule->day->name);
                                                    $dayTranslations = [
                                                        'Monday' => 'Lunes',
                                                        'Tuesday' => 'Martes',
                                                        'Wednesday' => 'Miércoles',
                                                        'Thursday' => 'Jueves',
                                                        'Friday' => 'Viernes',
                                                        'Saturday' => 'Sábado',
                                                        'Sunday' => 'Domingo'
                                                    ];
                                                    $dayNameEs = $dayTranslations[$dayName] ?? $dayName;

                                                    $today = now();
                                                    $weekStart = $today->copy()->startOfWeek();
                                                    $dayNumberMap = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
                                                    $targetDayNumber = $dayNumberMap[$dayName] ?? 1;
                                                    $classDate = $weekStart->copy()->addDays($targetDayNumber - 1);
                                                @endphp
                                                <div class="flex flex-col">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $dayNameEs }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $classDate->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ \Carbon\Carbon::parse($assignment->daySchedule->schedule->start)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($assignment->daySchedule->schedule->end)->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                Aula {{ $assignment->classroom->number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <!-- Botón Generar QR -->
                                                    <button wire:click="generateQr({{ $assignment->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="generateQr({{ $assignment->id }})"
                                                            type="button"
                                                            title="Generar código QR para asistencia"
                                                            class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50">
                                                        <svg wire:loading.remove wire:target="generateQr({{ $assignment->id }})" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                                        </svg>
                                                         Qr
                                                        <svg wire:loading wire:target="generateQr({{ $assignment->id }})" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                        </svg>
                                                    </button>

                                                    <!-- Botón Solicitar Licencia -->
                                                    <button onclick="openAbsenceFormForClass('{{ $classDate->format('Y-m-d') }}', {{ $assignment->id }})"
                                                            type="button"
                                                            title="Solicitar licencia para esta clase"
                                                            class="inline-flex items-center px-3 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                         Licencia
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    </div>
                </div>
            </div>

            {{-- Sección: Clases Finalizadas --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div x-data="{ open: false }">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center cursor-pointer" @click="open = !open">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Clases Finalizadas
                        </h3>
                        <span class="ml-3 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 rounded-full">
                            {{ $completedAssignments->count() }}
                        </span>
                    </div>
                    <svg class="w-5 h-5 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div x-show="open" x-transition class="p-6">
                    @if($completedAssignments->isEmpty())
                        <div class="text-center py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay clases finalizadas</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Materia
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Grupo
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Día / Fecha
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Horario
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Aula
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($completedAssignments as $assignment)
                                        @php
                                            $attendanceRecord = $assignment->attendanceRecords->first();
                                            $isMarked = $attendanceRecord && $attendanceRecord->isMarked();
                                        @endphp
                                        <tr class="opacity-75" wire:key="completed-{{ $assignment->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $assignment->userSubject->subject->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $assignment->userSubject->subject->code }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $assignment->group->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $dayName = trim($assignment->daySchedule->day->name);
                                                    $dayTranslations = [
                                                        'Monday' => 'Lunes',
                                                        'Tuesday' => 'Martes',
                                                        'Wednesday' => 'Miércoles',
                                                        'Thursday' => 'Jueves',
                                                        'Friday' => 'Viernes',
                                                        'Saturday' => 'Sábado',
                                                        'Sunday' => 'Domingo'
                                                    ];
                                                    $dayNameEs = $dayTranslations[$dayName] ?? $dayName;

                                                    $today = now();
                                                    $weekStart = $today->copy()->startOfWeek();
                                                    $dayNumberMap = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
                                                    $targetDayNumber = $dayNumberMap[$dayName] ?? 1;
                                                    $classDate = $weekStart->copy()->addDays($targetDayNumber - 1);
                                                @endphp
                                                <div class="flex flex-col">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $dayNameEs }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $classDate->format('d/m/Y') }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">
                                                    {{ \Carbon\Carbon::parse($assignment->daySchedule->schedule->start)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($assignment->daySchedule->schedule->end)->format('H:i') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                Aula {{ $assignment->classroom->number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($attendanceRecord)
                                                    {!! $attendanceRecord->getStatusBadge() !!}
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-200 dark:bg-gray-700 dark:text-gray-300 rounded-full">
                                                        Sin registro
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal QR --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Código QR de Asistencia
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $currentAssignment->userSubject->subject->name }} - Grupo {{ $currentAssignment->group->name }}
                                </p>
                            </div>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex flex-col items-center">
                            {{-- QR Code --}}
                            <div class="bg-white p-4 rounded-lg shadow-inner">
                                <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code" class="w-64 h-64">
                            </div>

                            {{-- Info de expiración --}}
                            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg w-full">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="text-sm">
                                        <p class="font-medium text-blue-900 dark:text-blue-100">Este QR es válido hasta las {{ $expiresAt }}</p>
                                        <p class="text-blue-700 dark:text-blue-300 mt-1">Escanea el código para marcar tu asistencia</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button wire:click="downloadQr" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Descargar QR
                        </button>
                        <button wire:click="regenerateQr" type="button"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Regenerar QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Mensajes flash --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 3000)"
             class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    <!-- Modal para solicitar licencia -->
    <div id="absenceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-gray-800">
            <div id="absenceModalContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
    function openAbsenceFormForClass(classDate, assignmentId) {
        const modal = document.getElementById('absenceModal');
        const modalContent = document.getElementById('absenceModalContent');

        modalContent.innerHTML = '<div class="text-center text-gray-300">Cargando...</div>';
        modal.classList.remove('hidden');

        fetch(`{{ route("absence-requests.create") }}?assignment_id=${assignmentId}&class_date=${classDate}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            modalContent.innerHTML = html;

            // Configurar el evento de envío del formulario
            initAbsenceRequestForm();
        })
        .catch(error => {
            modalContent.innerHTML = '<div class="text-center text-red-400">Error al cargar el formulario</div>';
            console.error('Error al cargar formulario:', error);
        });
    }

    function initAbsenceRequestForm() {
        const form = document.getElementById('absenceRequestForm');
        if (!form) {
            console.error('Formulario no encontrado');
            return;
        }

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            const formData = new FormData(this);

            try {
                const response = await fetch('{{ route("absence-requests.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    closeAbsenceModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al crear la solicitud');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            } catch (error) {
                console.error('Error en el fetch:', error);
                alert('Error al enviar la solicitud');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }

    function closeAbsenceModal() {
        document.getElementById('absenceModal').classList.add('hidden');
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('absenceModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeAbsenceModal();
        }
    });

    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAbsenceModal();
        }
    });
</script>
