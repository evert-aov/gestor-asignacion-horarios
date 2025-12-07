<div>
    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card: Asistencias de hoy -->
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Asistencias de hoy
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $asistenciasHoy }}
                </p>
                @if($tendenciaAsistencias['direccion'] !== 'neutral')
                    <div class="flex items-center mt-2 text-sm">
                        @if($tendenciaAsistencias['direccion'] === 'up')
                            <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span class="text-green-600 dark:text-green-400 font-medium">{{ $tendenciaAsistencias['porcentaje'] }}%</span>
                        @else
                            <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $tendenciaAsistencias['porcentaje'] }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-1">vs ayer</span>
                    </div>
                @endif
            </div>
            <div class="ml-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Retrasos -->
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Retrasos
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $retrasosHoy }}
                </p>
                @if($tendenciaRetrasos['direccion'] !== 'neutral')
                    <div class="flex items-center mt-2 text-sm">
                        @if($tendenciaRetrasos['direccion'] === 'up')
                            <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $tendenciaRetrasos['porcentaje'] }}%</span>
                        @else
                            <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span class="text-green-600 dark:text-green-400 font-medium">{{ $tendenciaRetrasos['porcentaje'] }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-1">vs ayer</span>
                    </div>
                @endif
            </div>
            <div class="ml-4">
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Inasistencias -->
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Inasistencias
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $inasistenciasHoy }}
                </p>
                @if($tendenciaInasistencias['direccion'] !== 'neutral')
                    <div class="flex items-center mt-2 text-sm">
                        @if($tendenciaInasistencias['direccion'] === 'up')
                            <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $tendenciaInasistencias['porcentaje'] }}%</span>
                        @else
                            <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span class="text-green-600 dark:text-green-400 font-medium">{{ $tendenciaInasistencias['porcentaje'] }}%</span>
                        @endif
                        <span class="text-gray-500 dark:text-gray-400 ml-1">vs ayer</span>
                    </div>
                @endif
            </div>
            <div class="ml-4">
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Sesiones programadas -->
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">
                    Sesiones programadas
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $sesionesSemanales }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    esta semana
                </p>
            </div>
            <div class="ml-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Gráficos de Asistencia -->
    <div class="mt-8 space-y-6">
        <!-- Fila 1: Barras y Donut -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Gráfico de Barras: Top 10 Docentes -->
            <x-container-second-div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Top 10 Docentes por Asistencia
                </h3>
                <div class="relative h-96">
                    <canvas id="barChart"></canvas>
                </div>
            </x-container-second-div>

            <!-- Gráfico Donut: Distribución de Estados -->
            <x-container-second-div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Distribución de Estados
                </h3>
                <div class="relative h-96 flex items-center justify-center">
                    <canvas id="donutChart"></canvas>
                </div>
            </x-container-second-div>
        </div>

        <!-- Fila 2: Gráfico de Líneas solo -->
        <x-container-second-div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Tendencia de Asistencia Semanal
            </h3>
            <div class="relative h-96">
                <canvas id="lineChart"></canvas>
            </div>
        </x-container-second-div>
    </div>

    @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuración de colores para dark mode
        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#e5e7eb' : '#374151';
        const gridColor = isDarkMode ? 'rgba(75, 85, 99, 0.2)' : 'rgba(229, 231, 235, 0.5)';

        // Datos desde PHP
        const topDocentes = @json($topDocentes);
        const distribucionEstados = @json($distribucionEstados);
        const tendenciaSemanal = @json($tendenciaSemanal);

        // Gráfico de Barras Horizontales con Gradientes
        const barCtx = document.getElementById('barChart').getContext('2d');

        // Crear gradientes para cada barra
        const barGradients = topDocentes.data.map((value, index) => {
            const gradient = barCtx.createLinearGradient(0, 0, 500, 0);
            if (value >= 90) {
                gradient.addColorStop(0, '#10b981');
                gradient.addColorStop(1, '#34d399');
            } else if (value >= 75) {
                gradient.addColorStop(0, '#f59e0b');
                gradient.addColorStop(1, '#fbbf24');
            } else {
                gradient.addColorStop(0, '#ef4444');
                gradient.addColorStop(1, '#f87171');
            }
            return gradient;
        });

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: topDocentes.labels,
                datasets: [{
                    label: 'Asistencia',
                    data: topDocentes.data,
                    backgroundColor: barGradients,
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    barThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',
                        borderWidth: 2,
                        padding: 12,
                        displayColors: true,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.parsed.x.toFixed(1) + '% de asistencia';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false
                        }
                    },
                    y: {
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            padding: 8
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Gráfico Donut con Gradientes y Sombras
        const donutCtx = document.getElementById('donutChart').getContext('2d');

        // Crear gradientes para el donut
        const donutGradients = distribucionEstados.colors.map((color, index) => {
            const gradient = donutCtx.createRadialGradient(250, 250, 50, 250, 250, 200);
            if (color === '#10b981') {
                gradient.addColorStop(0, '#34d399');
                gradient.addColorStop(1, '#10b981');
            } else if (color === '#fbbf24') {
                gradient.addColorStop(0, '#fcd34d');
                gradient.addColorStop(1, '#f59e0b');
            } else {
                gradient.addColorStop(0, '#f87171');
                gradient.addColorStop(1, '#dc2626');
            }
            return gradient;
        });

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: distribucionEstados.labels,
                datasets: [{
                    data: distribucionEstados.data,
                    backgroundColor: donutGradients,
                    borderWidth: 4,
                    borderColor: isDarkMode ? '#1f2937' : '#ffffff',
                    hoverOffset: 15,
                    hoverBorderWidth: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            padding: 20,
                            font: {
                                size: 14,
                                weight: '600'
                            },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            boxWidth: 12,
                            boxHeight: 12
                        }
                    },
                    tooltip: {
                        enabled: true,
                        backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',
                        borderWidth: 2,
                        padding: 16,
                        displayColors: true,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const total = distribucionEstados.total;
                                const value = context.parsed;
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return ' ' + context.label + ': ' + value + ' registros (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '70%',
                radius: '90%'
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const width = chart.width;
                    const height = chart.height;
                    const ctx = chart.ctx;
                    ctx.restore();

                    // Número grande
                    const fontSize = Math.min(width, height) / 6;
                    ctx.font = 'bold ' + fontSize + 'px Inter, sans-serif';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = textColor;

                    const text = distribucionEstados.total.toString();
                    const textX = Math.round((width - ctx.measureText(text).width) / 2);
                    const textY = height / 2 - 10;

                    ctx.fillText(text, textX, textY);

                    // Texto "Total"
                    ctx.font = 'normal ' + (fontSize / 3) + 'px Inter, sans-serif';
                    ctx.fillStyle = isDarkMode ? '#9ca3af' : '#6b7280';
                    const subText = 'Total Registros';
                    const subTextX = Math.round((width - ctx.measureText(subText).width) / 2);
                    ctx.fillText(subText, subTextX, textY + fontSize / 1.5);
                    ctx.save();
                }
            }]
        });

        // Gráfico de Líneas con Gradientes y Área
        const lineCtx = document.getElementById('lineChart').getContext('2d');

        // Gradiente para Asistencias (verde)
        const gradientAsistencias = lineCtx.createLinearGradient(0, 0, 0, 400);
        gradientAsistencias.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
        gradientAsistencias.addColorStop(0.5, 'rgba(16, 185, 129, 0.2)');
        gradientAsistencias.addColorStop(1, 'rgba(16, 185, 129, 0)');

        // Gradiente para Retrasos (amarillo)
        const gradientRetrasos = lineCtx.createLinearGradient(0, 0, 0, 400);
        gradientRetrasos.addColorStop(0, 'rgba(251, 191, 36, 0.4)');
        gradientRetrasos.addColorStop(0.5, 'rgba(251, 191, 36, 0.2)');
        gradientRetrasos.addColorStop(1, 'rgba(251, 191, 36, 0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: tendenciaSemanal.labels,
                datasets: [
                    {
                        label: 'Asistencias',
                        data: tendenciaSemanal.asistencias,
                        borderColor: '#10b981',
                        backgroundColor: gradientAsistencias,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#10b981',
                        pointHoverBorderWidth: 3
                    },
                    {
                        label: 'Retrasos',
                        data: tendenciaSemanal.retrasos,
                        borderColor: '#f59e0b',
                        backgroundColor: gradientRetrasos,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 10,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#f59e0b',
                        pointHoverBorderWidth: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: textColor,
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                size: 14,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false,
                        backgroundColor: isDarkMode ? 'rgba(31, 41, 55, 0.95)' : 'rgba(255, 255, 255, 0.95)',
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',
                        borderWidth: 2,
                        padding: 16,
                        displayColors: true,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: '600'
                            },
                            padding: 8
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: textColor,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            color: gridColor,
                            drawBorder: false
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                }
            }
        });
    });
</script>
@endpush

    <!-- Tabla de Asistencia por Docente -->
    <div class="mt-6">
        <x-container-second-div>
            <!-- Header con filtros -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    Tabla de Asistencia por Docente
                </h3>

                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Buscar
                        </label>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Buscar docente..."
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                    </div>

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

                    <!-- Filtro Materia -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Materia
                        </label>
                        <select wire:model.live="filterMateria"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todas</option>
                            @foreach($materias as $materia)
                                <option value="{{ $materia->id }}">{{ $materia->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Botón limpiar filtros -->
                <div class="flex justify-between items-center">
                    <button wire:click="clearFilters"
                            class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar filtros
                    </button>

                    <!-- Selector de registros por página -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Mostrar</span>
                        <select wire:model.live="perPage"
                                class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-600 dark:text-gray-400">registros</span>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Docente
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Sesiones
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Asistencias
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Retrasos
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Inasistencias
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                % Asistencia
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($attendanceRecords as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 dark:text-blue-400 font-semibold text-sm">
                                                {{ strtoupper(substr($record->docente_name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $record->docente_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                    {{ $record->total_sesiones }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ $record->asistencias }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-yellow-600 dark:text-yellow-400">
                                    {{ $record->retrasos }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-red-600 dark:text-red-400">
                                    {{ $record->inasistencias }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center">
                                        <div class="w-full max-w-xs">
                                            <div class="flex items-center">
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                                    <div class="h-2 rounded-full {{ $record->porcentaje_asistencia >= 90 ? 'bg-green-500' : ($record->porcentaje_asistencia >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                         style="width: {{ $record->porcentaje_asistencia }}%"></div>
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $record->porcentaje_asistencia }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button wire:click="showDetails({{ $record->docente_id }})"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-3">
                                        Ver detalle
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm">No se encontraron registros</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $attendanceRecords->links() }}
            </div>
        </x-container-second-div>
    </div>

    <!-- Modal de detalles -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:click="closeModal">
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black opacity-50"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-6xl w-full p-6 max-h-[90vh] overflow-y-auto" wire:click.stop>
                    <!-- Header del modal -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                Historial de Asistencias
                            </h3>
                        </div>
                        <button wire:click="closeModal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Tabla de detalles -->
                    @php
                        $docenteDetails = $this->getDocenteDetails();
                    @endphp
                    @if($docenteDetails && $docenteDetails->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Materia
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Grupo
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Total Sesiones
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Asistencias
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Retrasos
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Inasistencias
                                        </th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            % Asistencia
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($docenteDetails as $detail)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $detail->materia_name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $detail->materia_code }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                                    {{ $detail->grupo_name }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-gray-100">
                                                {{ $detail->total_sesiones }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm font-medium text-green-600 dark:text-green-400">
                                                {{ $detail->asistencias }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm font-medium text-yellow-600 dark:text-yellow-400">
                                                {{ $detail->retrasos }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm font-medium text-red-600 dark:text-red-400">
                                                {{ $detail->inasistencias }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center justify-center">
                                                    <div class="w-full max-w-xs">
                                                        <div class="flex items-center">
                                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                                                <div class="h-2 rounded-full {{ $detail->porcentaje_asistencia >= 90 ? 'bg-green-500' : ($detail->porcentaje_asistencia >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                                     style="width: {{ $detail->porcentaje_asistencia }}%"></div>
                                                            </div>
                                                            <span class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $detail->porcentaje_asistencia }}%
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No hay datos disponibles</p>
                        </div>
                    @endif

                    <!-- Footer del modal -->
                    <div class="mt-6 flex justify-end">
                        <button wire:click="closeModal"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
