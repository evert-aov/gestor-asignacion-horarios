<div wire:poll.60s>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Notificaciones
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes flash --}}
            @if (session()->has('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="flex flex-wrap gap-2 mb-6">
                <button wire:click="setFilter('all')"
                        class="px-4 py-2 rounded-lg transition-colors {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Todas ({{ $totalCount }})
                </button>
                <button wire:click="setFilter('unread')"
                        class="px-4 py-2 rounded-lg transition-colors {{ $filter === 'unread' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    No leídas ({{ $unreadCount }})
                </button>
                <button wire:click="setFilter('automatic')"
                        class="px-4 py-2 rounded-lg transition-colors {{ $filter === 'automatic' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Automáticas ({{ $automaticCount }})
                </button>
                <button wire:click="setFilter('manual')"
                        class="px-4 py-2 rounded-lg transition-colors {{ $filter === 'manual' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Manuales ({{ $manualCount }})
                </button>
                <button wire:click="setFilter('urgent')"
                        class="px-4 py-2 rounded-lg transition-colors {{ $filter === 'urgent' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    Urgentes
                </button>

                <div class="ml-auto flex gap-2">
                    @if($unreadCount > 0)
                        <button wire:click="markAllAsRead"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                            Marcar todas como leídas
                        </button>
                    @endif
                    
                    <button wire:click="openCreateModal"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Notificación
                    </button>
                </div>
            </div>

                {{-- Lista de notificaciones --}}
                <x-container-second-div>
                    @forelse($notifications as $notification)
                        <div x-data="{ expanded: false }" 
                             class="bg-white dark:bg-gray-900 rounded-lg p-4 mb-3 border-l-4 {{ $notification->border_color }} transition-all duration-200 hover:shadow-md cursor-pointer"
                             @click="expanded = !expanded; if(expanded && !{{ $notification->read_at ? 'true' : 'false' }}) { $wire.markAsRead({{ $notification->id }}) }">
                            <div class="flex items-start gap-4">
                                {{-- Icono según tipo --}}
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    @switch($notification->notification_type)
                                        @case('attendance_pending')
                                            <x-icons.calendar class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                                            @break
                                        @case('new_subject')
                                            <x-icons.subject class="w-6 h-6 text-green-600 dark:text-green-400" />
                                            @break
                                        @case('schedule_change')
                                            <x-icons.time class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                                            @break
                                        @case('direct_message')
                                            <x-icons.email class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                                            @break
                                        @case('reservation_approved')
                                            <x-icons.save class="w-6 h-6 text-green-600 dark:text-green-400" />
                                            @break
                                        @case('reservation_rejected')
                                            <x-icons.close class="w-6 h-6 text-red-600 dark:text-red-400" />
                                            @break
                                        @case('reservation_permission')
                                            <x-icons.alerts class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                                            @break
                                        @default
                                            <x-icons.edit class="w-6 h-6 text-gray-600 dark:text-gray-400" />
                                    @endswitch
                                </div>

                                {{-- Contenido --}}
                                <div class="flex-1">
                                    {{-- Timestamp relativo --}}
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>

                                    {{-- Título --}}
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                        {{ $notification->title }}
                                        @if($notification->priority === 'urgent')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                                Urgente
                                            </span>
                                        @elseif($notification->priority === 'important')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                                Importante
                                            </span>
                                        @endif
                                    </h3>

                                    {{-- Mensaje (resumido o completo según expansión) --}}
                                    <div x-show="expanded" x-cloak class="mt-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $notification->message }}</p>
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                                            <span>Enviado el {{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                    <p x-show="!expanded" class="text-sm text-gray-700 dark:text-gray-300">
                                        {{ Str::limit($notification->message, 150) }}
                                    </p>
                                </div>

                                {{-- Indicador no leído y acciones --}}
                                <div class="flex-shrink-0 flex flex-col items-end gap-2">
                                    @if(!$notification->read_at)
                                        <span class="inline-block w-3 h-3 bg-blue-600 rounded-full animate-pulse"></span>
                                    @endif

                                    {{-- Botón eliminar --}}
                                    <button @click.stop="$wire.deleteNotification({{ $notification->id }})"
                                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                            title="Eliminar notificación">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">Sin notificaciones</p>
                            <p class="text-gray-500 dark:text-gray-400">No hay notificaciones{{ $filter !== 'all' ? ' con este filtro' : '' }}</p>
                        </div>
                    @endforelse

                    {{-- Paginación --}}
                    @if($notifications->hasPages())
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </x-container-second-div>

        </div>
    </div>


    {{-- Modal para crear notificación --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCreateModal"></div>

                {{-- Centrar el modal --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <form wire:submit.prevent="sendNotification">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    Crear Nueva Notificación
                                </h3>
                                <button type="button" wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="space-y-6">
                                {{-- Usuario destinatario --}}
                                <div>
                                    <x-input-label for="user_id" value="Usuario Destinatario" />
                                    <select id="user_id" wire:model.live="user_id"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm">
                                        <option value="">Seleccione un usuario...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} {{ $user->last_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Email (auto-completado) --}}
                                <div>
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input id="email" type="email" value="{{ $email }}" class="mt-1 block w-full" readonly />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Se completa automáticamente al seleccionar el usuario
                                    </p>
                                </div>

                                {{-- Asunto --}}
                                <div>
                                    <x-input-label for="subject" value="Asunto" />
                                    <x-text-input id="subject" type="text" wire:model="subject" class="mt-1 block w-full"
                                                  placeholder="Ej: Actualización importante del sistema" />
                                    @error('subject')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mensaje --}}
                                <div>
                                    <x-input-label for="message" value="Mensaje" />
                                    <textarea id="message" wire:model="message" rows="6"
                                              class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 rounded-md shadow-sm"
                                              placeholder="Escriba el contenido completo de la notificación..."></textarea>
                                    @error('message')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Prioridad --}}
                                <div>
                                    <x-input-label for="priority" value="Prioridad" />
                                    <div class="mt-2 grid grid-cols-3 gap-3">
                                        <label class="relative flex items-center cursor-pointer">
                                            <input type="radio" name="priority" wire:model.live="priority" value="info" class="peer sr-only" />
                                            <div class="w-full px-4 py-3 text-center border-2 border-gray-300 dark:border-gray-700 rounded-lg transition-all
                                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30
                                                        hover:border-blue-400 dark:hover:border-blue-600">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-2xl">ℹ️</span>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Informativa</span>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="relative flex items-center cursor-pointer">
                                            <input type="radio" name="priority" wire:model.live="priority" value="important" class="peer sr-only" />
                                            <div class="w-full px-4 py-3 text-center border-2 border-gray-300 dark:border-gray-700 rounded-lg transition-all
                                                        peer-checked:border-yellow-500 peer-checked:bg-yellow-50 dark:peer-checked:bg-yellow-900/30
                                                        hover:border-yellow-400 dark:hover:border-yellow-600">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-2xl">⚠️</span>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Importante</span>
                                                </div>
                                            </div>
                                        </label>

                                        <label class="relative flex items-center cursor-pointer">
                                            <input type="radio" name="priority" wire:model.live="priority" value="urgent" class="peer sr-only" />
                                            <div class="w-full px-4 py-3 text-center border-2 border-gray-300 dark:border-gray-700 rounded-lg transition-all
                                                        peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/30
                                                        hover:border-red-400 dark:hover:border-red-600">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="text-2xl">🚨</span>
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Urgente</span>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    @error('priority')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>Enviar Notificación</span>
                                <span wire:loading>Enviando...</span>
                            </button>
                            <button type="button" wire:click="closeCreateModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
