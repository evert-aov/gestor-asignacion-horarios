<x-app-layout>
    <x-container-div>
        <!-- Header -->
        <x-container-second-div class="mb-6">
            <div class="flex justify-between items-center">
                <x-input-label class="text-lg font-semibold">
                    <x-icons.table class="mr-2"></x-icons.table>
                    Plantillas de Reportes
                </x-input-label>
                <a href="{{ route('reports.dynamic.index') }}"
                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:from-blue-500 hover:to-blue-600 transition-all duration-300 hover:shadow-lg hover:shadow-blue-600/25 font-medium inline-flex items-center">
                    <x-icons.save class="w-5 h-5 mr-2"></x-icons.save>
                    Crear Nueva Plantilla
                </a>
            </div>
        </x-container-second-div>

        <!-- Success message -->
        @if (session('success'))
            <x-container-second-div class="mb-6">
                <div class="bg-green-500/10 border border-green-500 text-green-400 p-4 rounded-lg">
                    {{ session('success') }}
                </div>
            </x-container-second-div>
        @endif

        <!-- Templates Grid -->
        <x-container-second-div>
            @if ($templates->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($templates as $template)
                        <div
                            class="bg-gradient-to-br from-gray-800/90 to-gray-900/90 rounded-lg p-5 border border-gray-700 hover:border-blue-600 transition-all duration-300 hover:shadow-lg hover:shadow-blue-600/20">
                            <!-- Template Header -->
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="font-semibold text-lg text-white flex-1">{{ $template->name }}</h3>
                                @if ($template->is_public)
                                    <span
                                        class="ml-2 px-2 py-1 bg-blue-600/20 border border-blue-500 text-blue-400 text-xs rounded-full">
                                        Pública
                                    </span>
                                @endif
                            </div>

                            <!-- Description -->
                            @if ($template->description)
                                <p class="text-sm text-gray-400 mb-4 line-clamp-2">{{ $template->description }}</p>
                            @else
                                <p class="text-sm text-gray-500 italic mb-4">Sin descripción</p>
                            @endif

                            <!-- Template Info -->
                            <div class="text-xs text-gray-500 mb-4 space-y-1 bg-gray-900/50 p-3 rounded">
                                <p>
                                    <span class="text-gray-400">Tabla:</span>
                                    <span
                                        class="text-blue-400 font-medium">{{ $availableTables[$template->table_name] ?? $template->table_name }}</span>
                                </p>
                                <p>
                                    <span class="text-gray-400">Campos:</span>
                                    <span class="text-white">{{ count($template->selected_fields) }}</span>
                                </p>
                                <p>
                                    <span class="text-gray-400">Filtros:</span>
                                    <span class="text-white">{{ count($template->filters) }}</span>
                                </p>
                                <p class="text-xs text-gray-600">
                                    Creada: {{ $template->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="{{ route('reports.templates.load', $template->id) }}"
                                    class="flex-1 px-3 py-2 bg-gradient-to-r from-green-600 to-emerald-500 text-white rounded-lg hover:from-green-500 hover:to-emerald-600 transition-all text-center text-sm font-medium">
                                    ▶ Usar
                                </a>

                                @if ($template->user_id === auth()->id())
                                    <form action="{{ route('reports.templates.delete', $template->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('¿Está seguro de eliminar esta plantilla?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-2 bg-gradient-to-r from-red-600 to-red-500 text-white rounded-lg hover:from-red-500 hover:to-red-600 transition-all text-sm font-medium">
                                            <x-icons.close class="w-4 h-4"></x-icons.close>
                                        </button>
                                    </form>
                                @else
                                    <div
                                        class="px-3 py-2 bg-gray-700 text-gray-400 rounded-lg text-xs flex items-center">
                                        👁️ Solo lectura
                                    </div>
                                @endif
                            </div>

                            <!-- Owner info for public templates -->
                            @if ($template->is_public && $template->user_id !== auth()->id())
                                <div class="mt-2 text-xs text-gray-500">
                                    Por: {{ $template->user->name }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16">
                    <x-icons.inexist class="w-24 h-24 text-gray-600 mb-4"></x-icons.inexist>
                    <p class="text-gray-400 text-lg mb-2">No tienes plantillas guardadas</p>
                    <p class="text-gray-500 text-sm mb-6">Crea tu primera plantilla para generar reportes más rápido</p>
                    <a href="{{ route('reports.dynamic.index') }}"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition-all">
                        Crear Primera Plantilla
                    </a>
                </div>
            @endif
        </x-container-second-div>
    </x-container-div>
</x-app-layout>
