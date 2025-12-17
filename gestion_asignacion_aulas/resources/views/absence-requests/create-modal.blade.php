<div class="flex justify-between items-start mb-6">
    <div>
        <h3 class="text-xl font-bold text-gray-100">Solicitar Licencia</h3>
        <p class="text-sm text-gray-400 mt-1">Complete el formulario para solicitar una licencia</p>
    </div>
    <button onclick="closeAbsenceModal()" class="text-gray-400 hover:text-gray-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<!-- Indicador de límite diario -->
<div class="mb-4">
    <span class="px-3 py-1 rounded-full text-xs font-medium
        {{ $remainingRequests <= 1 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-blue-500/20 text-blue-400' }}">
        Solicitudes disponibles hoy: {{ $remainingRequests }}/3
    </span>
</div>

<!-- Información de la Clase -->
@if($assignment)
<div class="mb-6 p-4 bg-gray-800 border border-gray-700 rounded-lg">
    <h4 class="text-sm font-semibold text-gray-300 mb-3">Información de la Clase</h4>
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
            <span class="text-gray-400">Materia:</span>
            <p class="text-gray-200 font-medium">{{ $assignment->subject->name ?? 'N/A' }}</p>
        </div>
        <div>
            <span class="text-gray-400">Aula:</span>
            <p class="text-gray-200 font-medium">
                @if($assignment->classroom)
                    Aula {{ $assignment->classroom->number }}
                @else
                    N/A
                @endif
            </p>
        </div>
        <div>
            <span class="text-gray-400">Día:</span>
            <p class="text-gray-200 font-medium">
                @if($assignment->daySchedule && $assignment->daySchedule->day)
                    @php
                        $dayTranslations = [
                            'Monday' => 'Lunes',
                            'Tuesday' => 'Martes',
                            'Wednesday' => 'Miércoles',
                            'Thursday' => 'Jueves',
                            'Friday' => 'Viernes',
                            'Saturday' => 'Sábado',
                            'Sunday' => 'Domingo',
                        ];
                        $dayName = $assignment->daySchedule->day->name;
                    @endphp
                    {{ $dayTranslations[$dayName] ?? $dayName }}
                @else
                    N/A
                @endif
            </p>
        </div>
        <div>
            <span class="text-gray-400">Horario:</span>
            <p class="text-gray-200 font-medium">
                @if($assignment->daySchedule && $assignment->daySchedule->schedule)
                    {{ \Carbon\Carbon::parse($assignment->daySchedule->schedule->start)->format('H:i') }} -
                    {{ \Carbon\Carbon::parse($assignment->daySchedule->schedule->end)->format('H:i') }}
                @else
                    N/A
                @endif
            </p>
        </div>
        <div>
            <span class="text-gray-400">Fecha de Licencia:</span>
            <p class="text-gray-200 font-medium">{{ \Carbon\Carbon::parse($classDate)->format('d/m/Y') }}</p>
        </div>
        @if($assignment->group)
        <div>
            <span class="text-gray-400">Grupo:</span>
            <p class="text-gray-200 font-medium">{{ $assignment->group->name }}</p>
        </div>
        @endif
    </div>
</div>
@endif

<form id="absenceRequestForm" enctype="multipart/form-data">
    @csrf

    <!-- Campos ocultos para assignment_id y absence_date -->
    <input type="hidden" id="assignment_id" name="assignment_id" value="{{ $assignment->id ?? '' }}">
    <input type="hidden" id="absence_date" name="absence_date" value="{{ $classDate ?? '' }}">

    <!-- Tipo de Licencia -->
    <div class="mb-4">
        <label for="absence_type" class="block text-sm font-medium text-gray-300 mb-2">
            Tipo de Licencia <span class="text-red-500">*</span>
        </label>
        <select id="absence_type"
                name="absence_type"
                required
                class="w-full rounded-md border-gray-600 bg-gray-700 text-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
            <option value="">Seleccione un tipo</option>
            <option value="enfermedad">Enfermedad</option>
            <option value="personal">Personal</option>
            <option value="familiar">Familiar</option>
            <option value="medico">Médico</option>
            <option value="emergencia">Emergencia</option>
            <option value="otro">Otro</option>
        </select>
    </div>

    <!-- Descripción del Motivo -->
    <div class="mb-4">
        <label for="reason" class="block text-sm font-medium text-gray-300 mb-2">
            Descripción del Motivo <span class="text-red-500">*</span>
        </label>
        <textarea id="reason"
                  name="reason"
                  rows="4"
                  required
                  maxlength="1000"
                  placeholder="Describa el motivo de su licencia..."
                  class="w-full rounded-md border-gray-600 bg-gray-700 text-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
        <p class="text-xs text-gray-400 mt-1">Máximo 1000 caracteres</p>
    </div>

    <!-- Evidencia (Opcional) -->
    <div class="mb-6">
        <label for="evidence" class="block text-sm font-medium text-gray-300 mb-2">
            Evidencia (Opcional)
        </label>
        <input type="file"
               id="evidence"
               name="evidence"
               accept=".jpg,.jpeg,.png,.pdf"
               class="w-full text-sm text-gray-300
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-md file:border-0
                      file:text-sm file:font-semibold
                      file:bg-orange-500 file:text-white
                      hover:file:bg-orange-600
                      cursor-pointer">
        <p class="text-xs text-gray-400 mt-1">
            Formatos permitidos: JPG, PNG, PDF. Tamaño máximo: 10MB
        </p>
    </div>

    <!-- Botones -->
    <div class="flex justify-end space-x-3">
        <button type="button"
                onclick="closeAbsenceModal()"
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition duration-150">
            Cancelar
        </button>
        <button type="submit"
                id="submitBtn"
                class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-md transition duration-150">
            Enviar Solicitud
        </button>
    </div>
</form>
