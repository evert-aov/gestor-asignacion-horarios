<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $isAdmin ? 'Gestión de Solicitudes de Ausencia' : 'Mis Solicitudes de Ausencia' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-100">
                    <!-- Header con filtros -->
                    @if($isAdmin)
                        <div class="mb-6">
                            <form method="GET" action="{{ route('absence-requests.index') }}" class="flex items-center space-x-2">
                                <label for="status" class="text-sm font-medium">Filtrar por estado:</label>
                                <select name="status" id="status"
                                        onchange="this.form.submit()"
                                        class="rounded-md border-gray-600 bg-gray-700 text-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos</option>
                                    <option value="pendiente" {{ request('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_revision" {{ request('status') == 'en_revision' ? 'selected' : '' }}>En Revisión</option>
                                    <option value="aprobada" {{ request('status') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                                    <option value="rechazada" {{ request('status') == 'rechazada' ? 'selected' : '' }}>Rechazada</option>
                                </select>
                            </form>
                        </div>
                    @endif

                    <!-- Tabla de solicitudes -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                                    @if($isAdmin)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Docente</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Materia</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Día</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Horario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Fecha de Licencia</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-800 divide-y divide-gray-700">
                                @forelse($absenceRequests as $request)
                                    <tr class="hover:bg-gray-750 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            #{{ $request->id }}
                                        </td>
                                        @if($isAdmin)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $request->teacher->name }} {{ $request->teacher->last_name }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 text-sm">
                                            @if($request->assignment && $request->assignment->subject)
                                                <div class="font-medium text-gray-200">{{ $request->assignment->subject->name }}</div>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($request->assignment && $request->assignment->daySchedule && $request->assignment->daySchedule->day)
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
                                                    $dayName = $request->assignment->daySchedule->day->name;
                                                @endphp
                                                <span class="text-gray-200">{{ $dayTranslations[$dayName] ?? $dayName }}</span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($request->assignment && $request->assignment->daySchedule && $request->assignment->daySchedule->schedule)
                                                <span class="text-gray-200">
                                                    {{ \Carbon\Carbon::parse($request->assignment->daySchedule->schedule->start)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($request->assignment->daySchedule->schedule->end)->format('H:i') }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ \Carbon\Carbon::parse($request->absence_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $typeColors[$request->absence_type] ?? $typeColors['otro'] }}">
                                                {{ $typeLabels[$request->absence_type] ?? 'Otro' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$request->status] }}">
                                                {{ $statusLabels[$request->status] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button onclick="openAbsenceDetailModal({{ $request->id }})"
                                                    class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white text-xs font-semibold rounded-md transition duration-150 shadow-sm">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isAdmin ? '9' : '8' }}" class="px-6 py-4 text-center text-gray-400">
                                            No hay solicitudes de licencia
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($absenceRequests->hasPages())
                        <div class="mt-4">
                            {{ $absenceRequests->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="absenceModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-gray-800">
            <div id="absenceModalContent">
                <!-- Contenido cargado dinámicamente -->
            </div>
        </div>
    </div>

    <script>
        function openAbsenceFormModal() {
            const modal = document.getElementById('absenceModal');
            const modalContent = document.getElementById('absenceModalContent');

            modalContent.innerHTML = '<div class="text-center text-gray-300">Cargando...</div>';
            modal.classList.remove('hidden');

            fetch('{{ route("absence-requests.create") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;
            })
            .catch(error => {
                modalContent.innerHTML = '<div class="text-center text-red-400">Error al cargar el formulario</div>';
                console.error('Error:', error);
            });
        }

        function openAbsenceDetailModal(id) {
            const modal = document.getElementById('absenceModal');
            const modalContent = document.getElementById('absenceModalContent');

            modalContent.innerHTML = '<div class="text-center text-gray-300">Cargando...</div>';
            modal.classList.remove('hidden');

            fetch(`/ausencias/${id}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                modalContent.innerHTML = html;

                // Inicializar el formulario de actualización si existe
                initUpdateStatusForm();
            })
            .catch(error => {
                modalContent.innerHTML = '<div class="text-center text-red-400">Error al cargar los detalles</div>';
                console.error('Error:', error);
            });
        }

        function initUpdateStatusForm() {
            const form = document.getElementById('updateStatusForm');
            if (!form) return; // No es admin o no hay formulario

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const updateBtn = document.getElementById('updateBtn');
                const messageDiv = document.getElementById('updateMessage');
                const originalText = updateBtn.textContent;

                updateBtn.disabled = true;
                updateBtn.textContent = 'Actualizando...';
                messageDiv.classList.add('hidden');

                const formData = new FormData(this);
                formData.append('_method', 'PATCH');

                try {
                    const formAction = form.getAttribute('action') || this.action;

                    const response = await fetch(formAction, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        messageDiv.textContent = data.message;
                        messageDiv.className = 'mt-2 text-sm text-green-400';
                        messageDiv.classList.remove('hidden');

                        setTimeout(() => {
                            closeAbsenceModal();
                            window.location.reload();
                        }, 1500);
                    } else {
                        messageDiv.textContent = data.message || 'Error al actualizar';
                        messageDiv.className = 'mt-2 text-sm text-red-400';
                        messageDiv.classList.remove('hidden');
                        updateBtn.disabled = false;
                        updateBtn.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    messageDiv.textContent = 'Error al actualizar la solicitud';
                    messageDiv.className = 'mt-2 text-sm text-red-400';
                    messageDiv.classList.remove('hidden');
                    updateBtn.disabled = false;
                    updateBtn.textContent = originalText;
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
</x-app-layout>
