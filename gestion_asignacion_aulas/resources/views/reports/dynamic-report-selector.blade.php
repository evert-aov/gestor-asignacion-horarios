<x-app-layout>
    <x-container-div>
        <!-- Header -->
        <x-container-second-div class="mb-6">
            <div class="flex items-center justify-between">
                <x-input-label class="text-lg font-semibold">
                    <x-icons.table class="mr-2"></x-icons.table>
                    Generador de Reportes Dinámicos
                </x-input-label>
                <a href="{{ route('reports.templates.list') }}"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg hover:from-purple-500 hover:to-pink-600 transition-all duration-300 font-medium inline-flex items-center">
                    <x-icons.table class="w-5 h-5 mr-2"></x-icons.table>
                    Mis Plantillas
                </a>
            </div>
        </x-container-second-div>

        <!-- Error messages -->
        @if ($errors->any())
            <x-container-second-div class="mb-6">
                <div class="bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-lg">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="flex items-center">
                                <x-icons.close class="w-4 h-4 mr-2"></x-icons.close>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </x-container-second-div>
        @endif

        <!-- Form -->
        <x-container-second-div>
            <form action="{{ route('reports.dynamic.generate') }}" method="GET" id="reportForm">


                <!-- Table Selection -->
                <div class="mb-8">
                    <x-input-label for="table" class="mb-2">
                        <x-icons.table class="inline mr-2"></x-icons.table>
                        Seleccione la tabla para generar el reporte
                    </x-input-label>
                    <x-select-input name="table" id="tableSelect" required>
                        <option value="">-- Seleccionar tabla --</option>
                        @foreach ($availableTables as $tableKey => $tableName)
                            <option value="{{ $tableKey }}" {{ old('table') == $tableKey ? 'selected' : '' }}>
                                {{ $tableName }}
                            </option>
                        @endforeach
                    </x-select-input>
                    <p class="text-sm text-gray-400 mt-2">
                        Los campos disponibles cambiarán automáticamente según la tabla seleccionada
                    </p>
                </div>

                <!-- Voice Recognition Section -->
                <div
                    class="mb-8 p-6 bg-gradient-to-r from-purple-900/20 to-blue-900/20 rounded-lg border border-purple-700/50">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <x-input-label class="mb-1">
                                <svg class="inline w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Comando de Voz (Experimental)
                            </x-input-label>
                            <p class="text-xs text-gray-400">
                                Presiona el micrófono y di lo que necesitas. Ejemplo: "Quiero un reporte de productos
                                con nombre y precio"
                            </p>
                        </div>
                        <button type="button" id="voiceButton"
                            class="relative px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-full hover:from-purple-500 hover:to-blue-500 transition-all duration-300 hover:shadow-lg hover:shadow-purple-600/50 font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="micIcon" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full hidden"
                                id="recordingIndicator"></span>
                        </button>
                    </div>

                    <!-- Transcript Display -->
                    <div id="transcriptContainer" class="hidden">
                        <div
                            class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold text-purple-400 uppercase">Escuchando...</span>
                                <button type="button" id="clearTranscript"
                                    class="text-gray-400 hover:text-white text-xs">
                                    Limpiar
                                </button>
                            </div>
                            <p id="transcriptText" class="text-white text-sm min-h-[40px] italic"></p>
                            <div class="mt-3 flex gap-2">
                                <button type="button" id="applyVoiceCommand"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition-all text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                                    ✓ Aplicar Comando
                                </button>
                                <button type="button" id="cancelVoiceCommand"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition-all text-sm font-medium">
                                    ✕ Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Browser Support Warning -->
                    <div id="voiceNotSupported"
                        class="hidden bg-yellow-900/20 border border-blue-600 text-yellow-400 p-3 rounded-lg text-sm">
                        <strong>⚠️ Reconocimiento de voz no disponible</strong><br>
                        Tu navegador no soporta reconocimiento de voz. Por favor usa Chrome, Edge o Safari.
                    </div>
                </div>

                <!-- Fields Selection (will be populated by JavaScript)-->
                <div id="fieldsContainer" style="display: none;">
                    <div class="mb-6">
                        <x-input-label class="mb-2">
                            <x-icons.search class="inline mr-2"></x-icons.search>
                            Seleccione los campos que desea incluir en el reporte
                        </x-input-label>
                        <p class="text-sm text-gray-400">
                            Debe seleccionar al menos un campo para generar el reporte
                        </p>
                    </div>

                    <!-- Action buttons for select/deselect all -->
                    <div class="flex gap-4 mb-6 pb-6 border-b border-gray-700">
                        <button type="button" onclick="selectAllFields()"
                            class="px-4
                            py-2
                            bg-gradient-to-r
                            from-blue-600
                            to-blue-500
                            text-white
                            rounded-lg
                            hover:from-blue-500
                            hover:to-blue-600
                            transition-all
                            duration-300
                            hover:shadow-lg
                            hover:shadow-blue-600/25
                            font-medium
                        ">
                            <x-icons.save @class(['inline', 'w-4', 'h-4', 'mr-2'])></x-icons.save>
                            Seleccionar Todos
                        </button>
                        <button type="button" onclick="deselectAllFields()"
                            class="
                            px-4
                            py-2
                            bg-gradient-to-r
                            from-gray-600
                            to-gray-500
                            text-white
                            rounded-lg
                            hover:from-gray-500
                            hover:to-gray-600
                            transition-all
                            duration-300
                            hover:shadow-lg
                            font-medium
                        ">
                            <x-icons.close @class(['inline', 'w-4', 'h-4', 'mr-2'])></x-icons.close>
                            Deseleccionar Todos
                            <button>
                    </div>

                    <!-- Fields grid (JavaScript)-->
                    <div id="fieldsGrid" @class([
                        'grid',
                        'grid-cols-1',
                        'md:grid-cols-2',
                        'lg:grid-cols-3',
                        'gap-4',
                        'mb-8',
                    ])>
                        <!-- Fields will be inserted here by JavaScript -->
                    </div>

                    <!-- Filters Section -->
                    <div id="filtersSection" class="mb-8 pt-6 border-t border-gray-700">
                        <div class="mb-4">
                            <x-input-label class="mb-2">
                                <x-icons.search class="inline mr-2"></x-icons.search>
                                Filtros (Opcional)
                            </x-input-label>
                            <p class="text-sm text-gray-400">
                                Agregue condiciones para filtrar los resultados del reporte
                            </p>
                        </div>

                        <div id="filtersContainer" class="space-y-4 mb-4">
                            <!-- Filter rows will be added here -->
                        </div>

                        <button type="button" onclick="addFilterRow()"
                            class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-all text-sm flex items-center">
                            <x-icons.save class="w-4 h-4 mr-2"></x-icons.save>
                            Agregar Filtro
                        </button>
                    </div>

                    <!-- Submit buttons -->
                    <div class="flex gap-4 pt-6">
                        <x-primary-button type="submit" class="flex items-center justify-center">
                            <x-icons.search class="mr-2"></x-icons.search>
                            Generar Reporte
                        </x-primary-button>
                        <a href="{{ route('dashboard') }}"
                            class="px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all text-sm flex items-center justify-center">
                            <x-icons.close class="mr-2"></x-icons.close>
                            Cancelar
                        </a>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="loadingIndicator" style="display: none;" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                    <p class="mt-4 text-gray-400">Cargando campos...</p>
                </div>
            </form>
        </x-container-second-div>
    </x-container-div>

    <script>
        const tableSelect = document.getElementById('tableSelect');
        const fieldsContainer = document.getElementById('fieldsContainer');
        const fieldsGrid = document.getElementById('fieldsGrid');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const filtersSection = document.getElementById('filtersSection');
        const filtersContainer = document.getElementById('filtersContainer');

        let currentFields = {};
        let filterCount = 0;

        tableSelect.addEventListener('change', function() {
            const selectedTable = this.value;

            if (!selectedTable) {
                fieldsContainer.style.display = 'none';
                fieldsGrid.innerHTML = '';
                filtersSection.style.display = 'none';
                filtersContainer.innerHTML = '';
                return;
            }

            // Show loading indicator
            loadingIndicator.style.display = 'block';
            fieldsContainer.style.display = 'none';

            // Fetch fields for the selected table
            fetch('{{ route('reports.get-table-fields') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        table: selectedTable
                    })
                })
                .then(response => response.json())
                .then(data => {
                    loadingIndicator.style.display = 'none';

                    if (data.success) {
                        // Store fields for filters
                        currentFields = data.fields;

                        // Clear previous fields and filters
                        fieldsGrid.innerHTML = '';
                        filtersContainer.innerHTML = '';
                        filterCount = 0;

                        // Add new fields
                        Object.entries(data.fields).forEach(([fieldKey, fieldData]) => {
                            const fieldLabel = fieldData.label;
                            const fieldHtml = `
                            <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-600 transition-all duration-200">
                                <input
                                    type="checkbox"
                                    name="fields[]"
                                    value="${fieldKey}"
                                    id="field_${fieldKey}"
                                    class="field-checkbox w-5 h-5 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer">
                                <label for="field_${fieldKey}" class="ml-3 text-sm font-medium text-gray-300 cursor-pointer flex-1">
                                    ${fieldLabel}
                                </label>
                            </div>
                        `;
                            fieldsGrid.innerHTML += fieldHtml;
                        });

                        // Show containers
                        fieldsContainer.style.display = 'block';
                        filtersSection.style.display = 'block';
                    } else {
                        alert('Error al cargar los campos: ' + (data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    loadingIndicator.style.display = 'none';
                    alert('Error al cargar los campos: ' + error.message);
                    console.error('Error:', error);
                });
        });

        function selectAllFields() {
            document.querySelectorAll('.field-checkbox').forEach(checkbox => {
                checkbox.checked = true;
            });
        }

        function deselectAllFields() {
            document.querySelectorAll('.field-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
        }

        function addFilterRow() {
            const index = filterCount++;
            const rowId = `filter-row-${index}`;

            let optionsHtml = '<option value="">-- Campo --</option>';
            Object.entries(currentFields).forEach(([key, data]) => {
                optionsHtml += `<option value="${key}">${data.label}</option>`;
            });

            const rowHtml = `
                <div id="${rowId}" class="flex flex-wrap gap-3 items-start bg-gray-100 dark:bg-gray-800/30 p-3 rounded-lg border border-gray-300 dark:border-gray-700">
                    <div class="flex-1 min-w-[200px]">
                        <select name="filters[${index}][field]" onchange="handleFieldChange(this, ${index})" class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-md focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 text-sm" required>
                            ${optionsHtml}
                        </select>
                    </div>
                    <div class="w-[150px]">
                        <select name="filters[${index}][operator]" onchange="handleOperatorChange(this, ${index})" class="w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-md focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 text-sm" required>
                            <option value="=">Igual a (=)</option>
                            <option value="!=">Diferente de (!=)</option>
                            <option value="like">Contiene</option>
                            <option value=">">Mayor que (>)</option>
                            <option value="<">Menor que (<)</option>
                            <option value=">=">Mayor o igual (>=)</option>
                            <option value="<=">Menor o igual (<=)</option>
                            <option value="between">Entre (Rango)</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px] flex gap-2" id="values-container-${index}">
                        <input type="text" name="filters[${index}][value]" placeholder="Valor" class="flex-1 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-md focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 text-sm" required>
                    </div>
                    <button type="button" onclick="removeFilterRow('${rowId}')" class="text-red-400 hover:text-red-300 p-2">
                        <x-icons.close class="w-5 h-5"></x-icons.close>
                    </button>
                </div>
            `;

            filtersContainer.insertAdjacentHTML('beforeend', rowHtml);
        }

        function removeFilterRow(rowId) {
            document.getElementById(rowId).remove();
        }

        function getInputTypeForField(fieldKey) {
            if (!fieldKey || !currentFields[fieldKey]) return 'text';

            const type = currentFields[fieldKey].type;

            if (['date'].includes(type)) return 'date';
            if (['datetime', 'timestamp'].includes(type)) return 'datetime-local';
            if (['integer', 'bigint', 'smallint', 'float', 'decimal', 'double'].includes(type)) return 'number';
            if (['boolean'].includes(type)) return 'boolean';

            return 'text';
        }

        function renderInputHtml(type, name, placeholder) {
            if (type === 'boolean') {
                return `
                    <select name="${name}" class="flex-1 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-md focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 text-sm" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="1">Verdadero / Sí / Activo</option>
                        <option value="0">Falso / No / Inactivo</option>
                    </select>
                `;
            }

            return `<input type="${type}" name="${name}" placeholder="${placeholder}" class="flex-1 bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-md focus:border-blue-500 dark:focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-blue-500 text-sm" required>`;
        }

        function handleFieldChange(select, index) {
            const fieldKey = select.value;
            const inputType = getInputTypeForField(fieldKey);
            const container = document.getElementById(`values-container-${index}`);

            // Get current operator to know if we need 1 or 2 inputs
            const rowId = `filter-row-${index}`;
            const operatorSelect = document.querySelector(`#${rowId} select[name^="filters"][name$="[operator]"]`);
            const operator = operatorSelect ? operatorSelect.value : '=';

            if (operator === 'between') {
                container.innerHTML = `
                    ${renderInputHtml(inputType, `filters[${index}][value]`, 'Desde')}
                    ${renderInputHtml(inputType, `filters[${index}][value2]`, 'Hasta')}
                `;
            } else {
                container.innerHTML = renderInputHtml(inputType, `filters[${index}][value]`, 'Valor');
            }
        }

        function handleOperatorChange(select, index) {
            const container = document.getElementById(`values-container-${index}`);
            const operator = select.value;

            // Get current field to determine input type
            const rowId = `filter-row-${index}`;
            const fieldSelect = document.querySelector(`#${rowId} select[name^="filters"][name$="[field]"]`);
            const inputType = getInputTypeForField(fieldSelect.value);

            if (operator === 'between') {
                container.innerHTML = `
                    ${renderInputHtml(inputType, `filters[${index}][value]`, 'Desde')}
                    ${renderInputHtml(inputType, `filters[${index}][value2]`, 'Hasta')}
                `;
            } else {
                container.innerHTML = renderInputHtml(inputType, `filters[${index}][value]`, 'Valor');
            }
        }

        // ====================================
        // VOICE RECOGNITION INTEGRATION
        // ====================================
        let voiceRecognizer = null;
        let parsedCommand = null;

        // Initialize voice recognition
        document.addEventListener('DOMContentLoaded', function() {
            // Check if VoiceReportGenerator is available
            if (typeof VoiceReportGenerator === 'undefined') {
                console.error('VoiceReportGenerator not loaded');
                document.getElementById('voiceNotSupported').classList.remove('hidden');
                document.getElementById('voiceButton').disabled = true;
                return;
            }

            voiceRecognizer = new VoiceReportGenerator();

            if (!voiceRecognizer.init()) {
                document.getElementById('voiceNotSupported').classList.remove('hidden');
                document.getElementById('voiceButton').disabled = true;
                return;
            }

            // Override callbacks
            voiceRecognizer.onStart = function() {
                document.getElementById('recordingIndicator').classList.remove('hidden');
                document.getElementById('micIcon').classList.add('animate-pulse');
                document.getElementById('transcriptContainer').classList.remove('hidden');
                document.getElementById('transcriptText').textContent = '';
            };

            voiceRecognizer.onTranscript = function(text, isInterim) {
                const transcriptEl = document.getElementById('transcriptText');
                transcriptEl.textContent = text;
                if (!isInterim) {
                    transcriptEl.classList.remove('text-gray-400');
                    transcriptEl.classList.add('text-white');
                } else {
                    transcriptEl.classList.add('text-gray-400');
                }
            };

            voiceRecognizer.onEnd = function() {
                document.getElementById('recordingIndicator').classList.add('hidden');
                document.getElementById('micIcon').classList.remove('animate-pulse');

                if (voiceRecognizer.transcript) {
                    parsedCommand = voiceRecognizer.parseCommand(voiceRecognizer.transcript);
                    console.log('Parsed command:', parsedCommand);
                    document.getElementById('applyVoiceCommand').disabled = false;
                }
            };

            voiceRecognizer.onError = function(error) {
                document.getElementById('recordingIndicator').classList.add('hidden');
                document.getElementById('micIcon').classList.remove('animate-pulse');

                let errorMsg = 'Error en el reconocimiento de voz';
                if (error === 'not-allowed') {
                    errorMsg = 'Permiso de micrófono denegado. Por favor permite el acceso al micrófono.';
                } else if (error === 'no-speech') {
                    errorMsg = 'No se detectó voz. Intenta de nuevo.';
                }

                alert(errorMsg);
            };

            // Voice button click handler
            document.getElementById('voiceButton').addEventListener('click', function() {
                if (voiceRecognizer.isListening) {
                    voiceRecognizer.stop();
                } else {
                    voiceRecognizer.start();
                }
            });

            // Apply command button
            document.getElementById('applyVoiceCommand').addEventListener('click', function() {
                if (!parsedCommand) return;

                applyVoiceCommandToForm(parsedCommand);

                // Hide transcript container
                document.getElementById('transcriptContainer').classList.add('hidden');
                parsedCommand = null;
            });

            // Cancel command button
            document.getElementById('cancelVoiceCommand').addEventListener('click', function() {
                document.getElementById('transcriptContainer').classList.add('hidden');
                document.getElementById('transcriptText').textContent = '';
                parsedCommand = null;
            });

            // Clear transcript button
            document.getElementById('clearTranscript').addEventListener('click', function() {
                document.getElementById('transcriptText').textContent = '';
                parsedCommand = null;
            });
        });

        /**
         * Apply parsed voice command to form
         */
        function applyVoiceCommandToForm(command) {
            console.log('Applying command:', command);

            // 1. Select table
            if (command.table) {
                const tableSelect = document.getElementById('tableSelect');
                const option = Array.from(tableSelect.options).find(opt => opt.value === command.table);

                if (option) {
                    tableSelect.value = command.table;
                    // Trigger change event to load fields
                    tableSelect.dispatchEvent(new Event('change'));

                    // Wait for fields to load, then select them
                    setTimeout(() => {
                        applyFieldsAndFilters(command);
                    }, 1500); // Give time for AJAX to complete
                } else {
                    alert(`No se encontró la tabla "${command.table}". Intenta con otro nombre.`);
                }
            } else {
                alert(
                    'No se pudo identificar la tabla en el comando. Intenta de nuevo con algo como "reporte de productos".'
                );
            }
        }

        function applyFieldsAndFilters(command) {
            // 2. Select fields
            if (command.fields && command.fields.length > 0) {
                command.fields.forEach(fieldKey => {
                    const checkbox = document.getElementById(`field_${fieldKey}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }

            // 3. Add filters
            if (command.filters && command.filters.length > 0) {
                command.filters.forEach(filter => {
                    addFilterRow();

                    // Wait a bit for the filter row to be added to DOM
                    setTimeout(() => {
                        const lastFilterIndex = filterCount - 1;
                        const rowId = `filter-row-${lastFilterIndex}`;
                        const row = document.getElementById(rowId);

                        if (row) {
                            // Set field
                            const fieldSelect = row.querySelector('select[name$="[field]"]');
                            if (fieldSelect) {
                                fieldSelect.value = filter.field;
                                fieldSelect.dispatchEvent(new Event('change'));
                            }

                            // Set operator
                            const operatorSelect = row.querySelector('select[name$="[operator]"]');
                            if (operatorSelect) {
                                operatorSelect.value = filter.operator;
                                operatorSelect.dispatchEvent(new Event('change'));
                            }

                            // Set value
                            setTimeout(() => {
                                const valueInput = row.querySelector(
                                    'input[name$="[value]"], select[name$="[value]"]');
                                if (valueInput) {
                                    valueInput.value = filter.value;
                                }
                            }, 100);
                        }
                    }, 100);
                });
            }

            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className =
                'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
            successMsg.innerHTML = `
                <strong>✓ Comando aplicado</strong><br>
                <span class="text-sm">Tabla: ${command.table}, Campos: ${command.fields.length}, Filtros: ${command.filters.length}</span>
            `;
            document.body.appendChild(successMsg);

            setTimeout(() => {
                successMsg.remove();
            }, 4000);
        }

        // ====================================
        // AUTO-LOAD TEMPLATE IF EXISTS
        // ====================================
        @if (isset($template))
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Loading template:', @json($template));

                // 1. Select the table
                const tableSelect = document.getElementById('tableSelect');
                tableSelect.value = '{{ $template->table_name }}';

                // 2. Trigger change event to load fields
                tableSelect.dispatchEvent(new Event('change'));

                // 3. Wait for fields to load, then select them
                setTimeout(() => {
                    const templateFields = @json($template->selected_fields);
                    console.log('Template fields:', templateFields);

                    // Select the checkboxes
                    templateFields.forEach(fieldKey => {
                        const checkbox = document.getElementById(`field_${fieldKey}`);
                        if (checkbox) {
                            checkbox.checked = true;
                            console.log('Checked field:', fieldKey);
                        } else {
                            console.warn('Field not found:', fieldKey);
                        }
                    });

                    // 4. Add filters if any
                    const templateFilters = @json($template->filters);
                    console.log('Template filters:', templateFilters);

                    if (templateFilters && templateFilters.length > 0) {
                        templateFilters.forEach(filter => {
                            addFilterRow();

                            setTimeout(() => {
                                const lastFilterIndex = filterCount - 1;
                                const rowId = `filter-row-${lastFilterIndex}`;
                                const row = document.getElementById(rowId);

                                if (row) {
                                    // Set field
                                    const fieldSelect = row.querySelector(
                                        'select[name$="[field]"]');
                                    if (fieldSelect && filter.field) {
                                        fieldSelect.value = filter.field;
                                        fieldSelect.dispatchEvent(new Event('change'));
                                    }

                                    // Set operator
                                    setTimeout(() => {
                                        const operatorSelect = row.querySelector(
                                            'select[name$="[operator]"]');
                                        if (operatorSelect && filter.operator) {
                                            operatorSelect.value = filter.operator;
                                            operatorSelect.dispatchEvent(new Event(
                                                'change'));
                                        }

                                        // Set value(s)
                                        setTimeout(() => {
                                            const valueInput = row
                                                .querySelector(
                                                    'input[name$="[value]"], select[name$="[value]"]'
                                                );
                                            if (valueInput && filter
                                                .value) {
                                                valueInput.value = filter
                                                    .value;
                                            }

                                            // If between operator, set second value
                                            if (filter.value2) {
                                                const value2Input = row
                                                    .querySelector(
                                                        'input[name$="[value2]"]'
                                                    );
                                                if (value2Input) {
                                                    value2Input.value =
                                                        filter.value2;
                                                }
                                            }
                                        }, 100);
                                    }, 100);
                                }
                            }, 150);
                        });
                    }

                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className =
                        'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    successMsg.innerHTML = `
                    <strong>✓ Plantilla cargada</strong><br>
                    <span class="text-sm">{{ $template->name }}</span>
                `;
                    document.body.appendChild(successMsg);

                    setTimeout(() => {
                        successMsg.remove();
                    }, 4000);

                }, 1500); // Wait for AJAX to load fields
            });
        @endif
    </script>
</x-app-layout>
