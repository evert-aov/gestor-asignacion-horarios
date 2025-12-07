<x-app-layout>
    <x-container-div>
        <!-- Header with actions -->
        <x-container-second-div class="mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center">
                    <x-input-label class="text-lg font-semibold">
                        <x-icons.table class="mr-2" />
                        Reporte: {{ $tableName }}
                    </x-input-label>
                </div>
                <div class="flex gap-3">
                    <form action="{{ route('reports.download-pdf') }}" method="POST" style="display: inline;">
                        @include('reports.partials.export-hidden-inputs')

                        <button type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:from-blue-500 hover:to-blue-600 transition-all duration-300 hover:shadow-lg hover:shadow-blue-600/25 font-medium inline-flex items-center">
                            <x-icons.save class="w-5 h-5 mr-2" />
                            PDF
                        </button>
                    </form>
                    <form action="{{ route('reports.download-excel') }}" method="POST" style="display: inline;">
                        @include('reports.partials.export-hidden-inputs')

                        <button type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-500 text-white rounded-lg hover:from-green-500 hover:to-emerald-600 transition-all duration-300 hover:shadow-lg hover:shadow-green-600/25 font-medium inline-flex items-center">
                            <x-icons.save class="w-5 h-5 mr-2" />
                            Excel
                        </button>
                    </form>
                    <form action="{{ route('reports.download-html') }}" method="POST" style="display: inline;">
                        @include('reports.partials.export-hidden-inputs')

                        <button type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-500 text-white rounded-lg hover:from-purple-500 hover:to-indigo-600 transition-all duration-300 hover:shadow-lg hover:shadow-purple-600/25 font-medium inline-flex items-center">
                            <x-icons.save class="w-5 h-5 mr-2" />
                            HTML
                        </button>
                    </form>
                    <a href="{{ route('reports.dynamic.index') }}"
                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg hover:from-blue-500 hover:to-blue-600 transition-all duration-300 hover:shadow-lg hover:shadow-blue-600/25 font-medium inline-flex items-center">
                        <x-icons.search class="w-5 h-5 mr-2" />
                        Nuevo Reporte
                    </a>
                    <button type="button" onclick="showSaveTemplateModal()"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg hover:from-purple-500 hover:to-pink-600 transition-all duration-300 hover:shadow-lg hover:shadow-purple-600/25 font-medium inline-flex items-center">
                        <x-icons.save class="w-5 h-5 mr-2" />
                        Guardar Plantilla
                    </button>
                    <a href="{{ route('reports.templates.list') }}"
                        class="px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-500 text-white rounded-lg hover:from-gray-500 hover:to-gray-600 transition-all duration-300 hover:shadow-lg font-medium inline-flex items-center">
                        <x-icons.table class="w-5 h-5 mr-2" />
                        Mis Plantillas
                    </a>
                </div>
            </div>
        </x-container-second-div>

        <!-- Stats -->
        <x-container-second-div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <x-icons.table class="w-5 h-5 text-blue-500 mr-2" />
                    <span class="text-sm text-gray-300">
                        Total de registros: <strong class="text-blue-500">{{ $data->total() }}</strong>
                    </span>
                </div>
                <div class="text-sm text-gray-400">
                    Página {{ $data->currentPage() }} de {{ $data->lastPage() }}
                </div>
            </div>
        </x-container-second-div>

        <!-- Table -->
        <x-container-second-div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead
                        class="bg-blue-100 dark:bg-gradient-to-r dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
                        <tr>
                            @foreach ($headers as $field => $label)
                                <th scope="col"
                                    class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-blue-500 uppercase tracking-wider border-b-2 border-blue-300 dark:border-blue-600/50">
                                    {{ $label }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data as $row)
                            <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-200">
                                @foreach ($displayFields as $field)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                        @php
                                            // Para foreign keys, intentar obtener el nombre relacionado
                                            $displayValue = null;
                                            $rawValue = is_object($row) ? $row->$field ?? null : $row[$field] ?? null;

                                            // Si es una foreign key y existe el campo _name del JOIN
                                            if (str_ends_with($field, '_id')) {
                                                $nameField = $field . '_name';
                                                $relatedName = is_object($row)
                                                    ? $row->$nameField ?? null
                                                    : $row[$nameField] ?? null;

                                                if ($relatedName) {
                                                    $displayValue = $relatedName;
                                                } else {
                                                    $displayValue = $rawValue;
                                                }
                                            } else {
                                                $displayValue = $rawValue;
                                            }
                                        @endphp

                                        @if ($field === 'status' && !is_null($displayValue))
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $displayValue ? 'bg-gradient-to-r from-green-600 to-emerald-500 text-white' : 'bg-gradient-to-r from-red-600 to-red-500 text-white' }}">
                                                {{ $displayValue ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        @elseif($field === 'gender' && !is_null($displayValue))
                                            <span class="flex items-center">
                                                {{ $displayValue === 'male' ? 'Masculino' : 'Femenino' }}
                                            </span>
                                        @elseif($field === 'email' && !is_null($displayValue))
                                            <span class="flex items-center">
                                                {{ $displayValue }}
                                            </span>
                                        @elseif($field === 'phone' && !is_null($displayValue))
                                            <span class="flex items-center">
                                                {{ $displayValue }}
                                            </span>
                                        @elseif(($field === 'created_at' || $field === 'updated_at') && !is_null($displayValue))
                                            <span class="flex items-center text-xs">
                                                {{ \Carbon\Carbon::parse($displayValue)->format('d/m/Y H:i') }}
                                            </span>
                                        @elseif(str_ends_with($field, '_id'))
                                            {{-- Para foreign keys, mostrar el nombre si está disponible, sino el ID --}}
                                            @if (!is_null($displayValue) && !is_numeric($displayValue))
                                                <span class="flex items-center">
                                                    <span class="font-medium text-blue-300">{{ $displayValue }}</span>
                                                </span>
                                            @else
                                                <span class="text-gray-500 text-xs">ID:
                                                    {{ $displayValue ?? 'N/A' }}</span>
                                            @endif
                                        @elseif(is_numeric($displayValue) && in_array($field, ['price', 'total', 'subtotal', 'discount']))
                                            <span class="font-semibold text-green-400">UDS.
                                                {{ number_format($displayValue, 2) }}</span>
                                        @else
                                            {{ $displayValue ?? 'N/A' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($headers) }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-icons.inexist class="w-16 h-16 text-gray-600 mb-4"></x-icons.inexist>
                                        <p class="text-gray-400 text-lg">No hay registros en esta tabla</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 border-t border-gray-700 pt-6">
                {{ $data->links() }}
            </div>
        </x-container-second-div>
    </x-container-div>

    <!-- Modal para guardar plantilla -->
    <div id="saveTemplateModal" class="hidden fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
        <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full border border-gray-700 shadow-2xl">
            <h3 class="text-xl font-bold mb-4 text-white flex items-center">
                <x-icons.save class="w-6 h-6 mr-2 text-purple-400" />
                Guardar como Plantilla
            </h3>

            <form action="{{ route('reports.templates.save') }}" method="POST">
                @csrf
                <input type="hidden" name="table" value="{{ $table }}">
                @foreach ($selectedFields as $field)
                    <input type="hidden" name="fields[]" value="{{ $field }}">
                @endforeach
                @foreach ($filters as $index => $filter)
                    <input type="hidden" name="filters[{{ $index }}][field]"
                        value="{{ $filter['field'] ?? '' }}">
                    <input type="hidden" name="filters[{{ $index }}][operator]"
                        value="{{ $filter['operator'] ?? '' }}">
                    <input type="hidden" name="filters[{{ $index }}][value]"
                        value="{{ $filter['value'] ?? '' }}">
                    @if (isset($filter['value2']))
                        <input type="hidden" name="filters[{{ $index }}][value2]"
                            value="{{ $filter['value2'] }}">
                    @endif
                @endforeach

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 text-gray-300">Nombre de la Plantilla</label>
                    <input type="text" name="name" required placeholder="Ej: Reporte mensual de ventas"
                        class="w-full bg-gray-700 border-gray-600 text-white rounded-lg focus:border-purple-500 focus:ring-purple-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2 text-gray-300">Descripción (opcional)</label>
                    <textarea name="description" rows="3" placeholder="Describe el propósito de esta plantilla..."
                        class="w-full bg-gray-700 border-gray-600 text-white rounded-lg focus:border-purple-500 focus:ring-purple-500"></textarea>
                </div>

                <div class="mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_public" value="1"
                            class="mr-3 w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500">
                        <span class="text-sm text-gray-300">
                            Hacer pública (otros usuarios podrán usar esta plantilla)
                        </span>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg hover:from-purple-500 hover:to-pink-600 transition-all font-medium">
                        💾 Guardar
                    </button>
                    <button type="button" onclick="hideSaveTemplateModal()"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition-all">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showSaveTemplateModal() {
            document.getElementById('saveTemplateModal').classList.remove('hidden');
        }

        function hideSaveTemplateModal() {
            document.getElementById('saveTemplateModal').classList.add('hidden');
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideSaveTemplateModal();
            }
        });

        // Close modal on backdrop click
        document.getElementById('saveTemplateModal').addEventListener('click', function(event) {
            if (event.target === this) {
                hideSaveTemplateModal();
            }
        });
    </script>

</x-app-layout>
