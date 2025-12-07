<x-app-layout>
    <div x-data="{
        openMetrics: true,
        openSchedule: true
    }">
        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">
                Dashboard de Asistencias
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Resumen general de asistencias, horarios y ocupación de aulas
            </p>
        </div>

        <!-- Attendance Metrics Section -->
        <div class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <button @click="openMetrics = !openMetrics"
                        class="w-full px-6 py-4 flex items-center justify-between transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            Métricas y Tabla de Asistencia
                        </h3>
                    </div>
                    <svg :class="{'rotate-180': openMetrics}" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openMetrics"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="px-6 pb-6">
                    @livewire('dashboard.attendance-metrics')
                </div>
            </div>
        </div>

        <!-- Weekly Schedule Section -->
        <div class="mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <button @click="openSchedule = !openSchedule"
                        class="w-full px-6 py-4 flex items-center justify-between transition-colors">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                            Calendario
                        </h3>
                    </div>
                    <svg :class="{'rotate-180': openSchedule}" class="w-5 h-5 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openSchedule"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="px-6 pb-6">
                    @livewire('dashboard.monthly-schedule')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
