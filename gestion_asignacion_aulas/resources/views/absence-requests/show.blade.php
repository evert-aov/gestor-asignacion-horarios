<div class="flex justify-between items-start mb-6">
    <div class="flex items-center">
        <svg class="w-6 h-6 text-orange-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h3 class="text-xl font-bold text-gray-100">Solicitud de Licencia #{{ $absenceRequest->id }}</h3>
    </div>
    <button onclick="closeAbsenceModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Columna principal (2/3) -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Información de la Clase/Asignación -->
        @if($absenceRequest->assignment)
            <div class="bg-gray-700 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-gray-100 mb-4">Información de la Clase</h4>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Materia</label>
                        <p class="text-gray-100 font-semibold">{{ $absenceRequest->assignment->subject->name }}</p>
                    </div>

                    @if($absenceRequest->assignment->classroom)
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Aula</label>
                            <p class="text-gray-100">Aula {{ $absenceRequest->assignment->classroom->number }}</p>
                        </div>
                    @endif

                    @if($absenceRequest->assignment->daySchedule)
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Día</label>
                            @php
                                $dayNames = [
                                    'Monday' => 'Lunes',
                                    'Tuesday' => 'Martes',
                                    'Wednesday' => 'Miércoles',
                                    'Thursday' => 'Jueves',
                                    'Friday' => 'Viernes',
                                    'Saturday' => 'Sábado',
                                    'Sunday' => 'Domingo'
                                ];
                                $dayName = $absenceRequest->assignment->daySchedule->day->name ?? '';
                            @endphp
                            <p class="text-gray-100">{{ $dayNames[$dayName] ?? $dayName }}</p>
                        </div>

                        @if($absenceRequest->assignment->daySchedule->schedule)
                            <div>
                                <label class="block text-sm font-medium text-gray-400">Horario</label>
                                <p class="text-gray-100">
                                    {{ \Carbon\Carbon::parse($absenceRequest->assignment->daySchedule->schedule->start)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($absenceRequest->assignment->daySchedule->schedule->end)->format('H:i') }}
                                </p>
                            </div>
                        @endif
                    @endif

                    @if($absenceRequest->assignment->group)
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Grupo</label>
                            <p class="text-gray-100">{{ $absenceRequest->assignment->group->name }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Información de la Solicitud -->
        <div class="bg-gray-700 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-100 mb-4">Información de la Solicitud</h4>

            <div class="grid grid-cols-2 gap-4">
                @if($isAdmin)
                    <div>
                        <label class="block text-sm font-medium text-gray-400">Docente</label>
                        <p class="text-gray-100">{{ $absenceRequest->teacher->name }} {{ $absenceRequest->teacher->last_name }}</p>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-400">Fecha de Licencia</label>
                    <p class="text-gray-100 font-semibold">{{ \Carbon\Carbon::parse($absenceRequest->absence_date)->format('d/m/Y') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400">Tipo de Licencia</label>
                    @php
                        $typeColors = [
                            'enfermedad' => 'bg-red-500/20 text-red-400',
                            'personal' => 'bg-blue-500/20 text-blue-400',
                            'familiar' => 'bg-purple-500/20 text-purple-400',
                            'medico' => 'bg-pink-500/20 text-pink-400',
                            'emergencia' => 'bg-orange-500/20 text-orange-400',
                            'otro' => 'bg-gray-500/20 text-gray-400',
                        ];
                        $typeLabels = [
                            'enfermedad' => 'Enfermedad',
                            'personal' => 'Personal',
                            'familiar' => 'Familiar',
                            'medico' => 'Médico',
                            'emergencia' => 'Emergencia',
                            'otro' => 'Otro',
                        ];
                    @endphp
                    <span class="inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$absenceRequest->absence_type] ?? $typeColors['otro'] }}">
                        {{ $typeLabels[$absenceRequest->absence_type] ?? 'Otro' }}
                    </span>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-400">Motivo</label>
                    <p class="text-gray-100 mt-1">{{ $absenceRequest->reason }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400">Fecha de Solicitud</label>
                    <p class="text-gray-100">{{ $absenceRequest->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Evidencia -->
        @if($absenceRequest->evidence_path)
            <div class="bg-gray-700 rounded-lg p-4" x-data="{ expanded: false }">
                <button @click="expanded = !expanded"
                        class="w-full flex items-center justify-between text-left">
                    <h4 class="text-lg font-semibold text-gray-100">Evidencia Adjunta</h4>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200"
                         :class="{ 'rotate-180': expanded }"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="expanded"
                     x-collapse
                     class="mt-4">
                    @php
                        $extension = pathinfo($absenceRequest->evidence_path, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                    @endphp

                    @if($isImage)
                        <div>
                            <a href="{{ $absenceRequest->evidence_path }}" target="_blank">
                                <img src="{{ $absenceRequest->evidence_path }}"
                                     alt="Evidencia"
                                     class="max-w-xs h-auto rounded-lg border border-gray-600 hover:opacity-90 transition cursor-pointer">
                            </a>
                            <p class="text-sm text-gray-400 mt-2">Haz clic en la imagen para verla en tamaño completo</p>
                        </div>
                    @else
                        <a href="{{ $absenceRequest->evidence_path }}"
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-md transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Ver/Descargar Documento PDF
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <!-- Notas del Administrador -->
        @if($absenceRequest->admin_notes)
            <div class="bg-gray-700 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-gray-100 mb-4">Notas del Administrador</h4>
                <p class="text-gray-100">{{ $absenceRequest->admin_notes }}</p>
            </div>
        @endif
    </div>

    <!-- Columna lateral (1/3) -->
    <div class="space-y-4">
        <!-- Estado Actual -->
        <div class="bg-gray-700 rounded-lg p-4">
            <h4 class="text-sm font-medium text-gray-400 mb-2">Estado Actual</h4>
            @php
                $statusColors = [
                    'pendiente' => 'bg-yellow-500/20 text-yellow-400',
                    'en_revision' => 'bg-blue-500/20 text-blue-400',
                    'aprobada' => 'bg-green-500/20 text-green-400',
                    'rechazada' => 'bg-red-500/20 text-red-400',
                ];
                $statusLabels = [
                    'pendiente' => 'Pendiente',
                    'en_revision' => 'En Revisión',
                    'aprobada' => 'Aprobada',
                    'rechazada' => 'Rechazada',
                ];
            @endphp
            <span class="inline-block px-3 py-2 rounded-full text-sm font-medium {{ $statusColors[$absenceRequest->status] }}">
                {{ $statusLabels[$absenceRequest->status] }}
            </span>
        </div>

        <!-- Actualizar Estado (solo admin) -->
        @if($isAdmin)
            <div class="bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-400 mb-4">Actualizar Estado</h4>

                <form id="updateStatusForm" action="{{ route('absence-requests.update-status', $absenceRequest->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Nuevo Estado</label>
                        <select id="status"
                                name="status"
                                required
                                class="w-full rounded-md border-gray-600 bg-gray-800 text-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                            <option value="pendiente" {{ $absenceRequest->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en_revision" {{ $absenceRequest->status == 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                            <option value="aprobada" {{ $absenceRequest->status == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                            <option value="rechazada" {{ $absenceRequest->status == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="admin_notes" class="block text-sm font-medium text-gray-300 mb-2">Notas Administrativas</label>
                        <textarea id="admin_notes"
                                  name="admin_notes"
                                  rows="3"
                                  maxlength="1000"
                                  class="w-full rounded-md border-gray-600 bg-gray-800 text-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ $absenceRequest->admin_notes }}</textarea>
                    </div>

                    <button type="submit"
                            id="updateBtn"
                            class="w-full px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-md transition duration-150">
                        Actualizar Solicitud
                    </button>

                    <div id="updateMessage" class="mt-2 text-sm hidden"></div>
                </form>
            </div>
        @endif

        <!-- Información de Revisión -->
        @if($absenceRequest->reviewed_at)
            <div class="bg-gray-700 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-400 mb-2">Información de Revisión</h4>
                <div class="space-y-2 text-sm">
                    <div>
                        <span class="text-gray-400">Revisado por:</span>
                        <p class="text-gray-100">{{ $absenceRequest->reviewedBy ? $absenceRequest->reviewedBy->name : 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Fecha de revisión:</span>
                        <p class="text-gray-100">{{ $absenceRequest->reviewed_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Eliminar Solicitud (solo admin) -->
        @if($isAdmin)
            <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                <h4 class="text-sm font-medium text-red-400 mb-2">Zona de Peligro</h4>
                <p class="text-xs text-gray-400 mb-3">Esta acción no se puede deshacer</p>
                <form action="{{ route('absence-requests.destroy', $absenceRequest->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta solicitud?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition duration-150">
                        Eliminar Solicitud
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
