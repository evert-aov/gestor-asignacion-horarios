<div wire:poll.60s>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Notificaciones
        </h2>
    </x-slot>    <div class="py-6">
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

                @if($unreadCount > 0)
                    <button wire:click="markAllAsRead"
                            class="ml-auto px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                        Marcar todas como leídas
                    </button>
                @endif
            </div>

            {{-- Lista de notificaciones --}}
            <x-container-second-div>
                @forelse($notifications as $notification)
                    <div class="bg-white dark:bg-gray-900 rounded-lg p-4 mb-3 border-l-4 {{ $notification->border_color }}">
                        <div class="flex items-start gap-4">
                            {{-- Icono según tipo --}}
                            <div class="flex-shrink-0">
                                <span class="text-3xl">{{ $notification->emoji }}</span>
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

                                {{-- Resumen --}}
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                    {{ Str::limit($notification->message, 150) }}
                                </p>

                                {{-- Acción --}}
                                <button wire:click="viewNotification({{ $notification->id }})"
                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium inline-flex items-center">
                                    Ver notificación completa
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            {{-- Indicador no leído y acciones --}}
                            <div class="flex-shrink-0 flex flex-col items-end gap-2">
                                @if(!$notification->read_at)
                                    <span class="inline-block w-3 h-3 bg-blue-600 rounded-full animate-pulse"></span>
                                @endif

                                <button wire:click.stop="deleteNotification({{ $notification->id }})"
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

    {{-- Modal para ver notificación completa --}}
    @if($showModal && $selectedNotification)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Overlay --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                {{-- Centrar el modal --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal panel --}}
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        {{-- Header --}}
                        <div class="flex items-start justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-start gap-4">
                                <span class="text-5xl">{{ $selectedNotification->emoji }}</span>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        {{ $selectedNotification->title }}
                                    </h3>
                                    <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $selectedNotification->created_at->format('d/m/Y H:i') }}</span>
                                        <span>·</span>
                                        <span>{{ $selectedNotification->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Badge de prioridad --}}
                            <div>
                                @if($selectedNotification->priority === 'urgent')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                        🔴 Urgente
                                    </span>
                                @elseif($selectedNotification->priority === 'important')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                        🟡 Importante
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300">
                                        ⚪ Normal
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Mensaje completo --}}
                        <div class="prose dark:prose-invert max-w-none mb-6">
                            <div class="text-gray-800 dark:text-gray-200 text-base leading-relaxed whitespace-pre-line">
                                {{ $selectedNotification->message }}
                            </div>
                        </div>

                        {{-- Estado de lectura --}}
                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span>
                                        @if($selectedNotification->read_at)
                                            Leída el {{ $selectedNotification->read_at->format('d/m/Y H:i') }}
                                        @else
                                            Sin leer
                                        @endif
                                    </span>
                                </div>
                                <span class="text-gray-500 dark:text-gray-400">
                                    ID: #{{ $selectedNotification->id }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de acción --}}
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="closeModal"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar
                        </button>
                        <button type="button" wire:click="deleteNotification({{ $selectedNotification->id }})"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
