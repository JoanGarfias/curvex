<template>
  <Head title="Muestreo de Aceptación" />
  
  <MainLayout :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="text-center mb-6 sm:mb-8 pt-4 sm:pt-8">
      <div class="flex items-center justify-center gap-3 mb-3">
        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
          Muestreo de Aceptación
        </h1>
      </div>
      <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-2xl mx-auto px-2">
        Calcula el plan de muestreo óptimo basado en niveles de calidad aceptable y tolerancia
      </p>
    </div>


    
    <!-- Selector de Modo y Tour Button -->
    <div class="mt-6 flex justify-center items-center gap-4 pb-5">
      <div id="mode-selector" class="inline-flex bg-white dark:bg-gray-800 rounded-lg p-1 border-2 border-gray-200 dark:border-gray-700">
        <button
          @click="mode = 'aql-ltpd'"
          :class="[
            'px-6 py-2 rounded-md font-medium transition-all',
            mode === 'aql-ltpd'
              ? 'bg-black dark:bg-white text-white dark:text-black'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
          ]"
        >
          Modo AQL/LTPD
        </button>
        <button
          @click="mode = 'n-c'"
          :class="[
            'px-6 py-2 rounded-md font-medium transition-all',
            mode === 'n-c'
              ? 'bg-black dark:bg-white text-white dark:text-black'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
          ]"
        >
          Modo n/c
        </button>
      </div>
      
      <!-- Tour Button -->
      <button
        @click="startTour"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors"
        title="Ver guía interactiva"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-medium">Guía</span>
      </button>
    </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Formulario - Columna fija -->
        <div class="lg:col-span-1">
          <div id="form-container" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 sticky top-4">
            <div class="flex items-center gap-2 mb-6">
              <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Parámetros</h2>
            </div>

            <div class="space-y-5">
              <!-- Modo AQL/LTPD -->
              <template v-if="mode === 'aql-ltpd'">
                <!-- AQL -->
                <div id="aql-input">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    AQL (Nivel de Calidad Aceptable)
                    <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
                  </label>
                  <input
                    v-model="formData.AQT"
                    type="number"
                    step="0.01"
                    min="0"
                    max="1"
                    :class="[
                      'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                      'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                      errors.AQT ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
                    ]"
                    placeholder="Ej: 0.02"
                    @input="clearError('AQT')"
                  />
                  <p v-if="errors.AQT" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.AQT }}</p>
                </div>

                <!-- LTPD -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    LTPD (Tolerancia del Lote)
                    <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
                  </label>
                  <input
                    v-model="formData.LTPD"
                    type="number"
                    step="0.01"
                    min="0"
                    max="1"
                    :class="[
                      'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                      'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                      errors.LTPD ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
                    ]"
                    placeholder="Ej: 0.10"
                    @input="clearError('LTPD')"
                  />
                  <p v-if="errors.LTPD" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.LTPD }}</p>
                </div>
              </template>

              <!-- Modo n/c -->
              <template v-else>
                <!-- n -->
                <div id="n-input">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    n (Tamaño de Muestra)
                    <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">≥ 2</span>
                  </label>
                  <input
                    v-model="formData.n"
                    type="number"
                    min="2"
                    step="1"
                    :class="[
                      'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                      'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                      errors.n ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
                    ]"
                    placeholder="Ej: 50"
                    @input="clearError('n')"
                  />
                  <p v-if="errors.n" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.n }}</p>
                </div>

                <!-- c -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    c (Criterio de Aceptación)
                    <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 7</span>
                  </label>
                  <input
                    v-model="formData.c"
                    type="number"
                    min="0"
                    max="7"
                    step="1"
                    :class="[
                      'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                      'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                      errors.c ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
                    ]"
                    placeholder="Ej: 2"
                    @input="clearError('c')"
                  />
                  <p v-if="errors.c" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.c }}</p>
                </div>
              </template>

              <!-- 1-alpha (común para ambos modos) -->
              <div id="alpha-input">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                  1 - α (Confianza del Productor)
                  <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
                </label>
                <input
                  v-model="formData['1-alpha']"
                  type="number"
                  step="0.01"
                  min="0"
                  max="1"
                  :class="[
                    'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                    'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                    errors['1-alpha'] ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
                  ]"
                  placeholder="Ej: 0.95"
                  @input="clearError('1-alpha')"
                />
                <p v-if="errors['1-alpha']" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors['1-alpha'] }}</p>
              </div>

              <!-- Beta (común para ambos modos) -->
              <div id="beta-input">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                  β (Riesgo del Consumidor)
                  <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
                </label>
                <input
                  v-model="formData.beta"
                  type="number"
                  step="0.01"
                  min="0"
                  max="1"
                  :class="[
                    'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                    'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
                    errors.beta ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
                  ]"
                  placeholder="Ej: 0.10"
                  @input="clearError('beta')"
                />
                <p v-if="errors.beta" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.beta }}</p>
              </div>

              <button
                id="submit-button"
                @click="handleSubmit"
                :disabled="loading"
                class="w-full bg-black dark:bg-white text-white dark:text-black py-3 rounded-lg font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors disabled:bg-gray-400 dark:disabled:bg-gray-600 disabled:cursor-not-allowed"
              >
                {{ loading ? 'Calculando...' : 'Calcular Plan de Muestreo' }}
              </button>
            </div>

            <!-- Guía -->
            <div id="guide-info" class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
              <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-xs text-gray-600 dark:text-gray-300">
                  <p class="font-medium mb-1">Guía rápida:</p>
                  <ul v-if="mode === 'aql-ltpd'" class="space-y-1 list-disc list-inside">
                    <li>AQL: % defectos que consideras aceptable</li>
                    <li>LTPD: % defectos máximo tolerable</li>
                    <li>1-α: Confianza (típico: 0.95)</li>
                    <li>β: Riesgo consumidor (típico: 0.10)</li>
                  </ul>
                  <ul v-else class="space-y-1 list-disc list-inside">
                    <li>n: Tamaño de la muestra a inspeccionar</li>
                    <li>c: Defectos permitidos para aceptar</li>
                    <li>1-α: Confianza (típico: 0.95)</li>
                    <li>β: Riesgo consumidor (típico: 0.10)</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resultados - 2 columnas -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Skeletons mientras carga -->
          <template v-if="loading">
            <!-- Skeleton: Resultados numéricos -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
              <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"></div>
              
              <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
                  <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-2/3 mx-auto mb-2"></div>
                  <div class="h-10 bg-gray-200 dark:bg-gray-600 rounded w-16 mx-auto mb-2"></div>
                  <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-1/2 mx-auto"></div>
                </div>
                
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
                  <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-2/3 mx-auto mb-2"></div>
                  <div class="h-10 bg-gray-200 dark:bg-gray-600 rounded w-16 mx-auto mb-2"></div>
                  <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-1/2 mx-auto"></div>
                </div>
                
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
                  <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-2/3 mx-auto mb-2"></div>
                  <div class="h-10 bg-gray-200 dark:bg-gray-600 rounded w-16 mx-auto mb-2"></div>
                  <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-1/2 mx-auto"></div>
                </div>
              </div>

              <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-1/4 mb-2"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-full"></div>
              </div>
            </div>

            <!-- Skeleton: Gráfica -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
              <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
              
              <div class="w-full h-80 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
              </div>

              <div class="mt-4 flex items-center justify-center gap-6">
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
                <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
              </div>
            </div>

            <!-- Skeleton: Tabla -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
              <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-2/3 mb-4"></div>
              
              <div class="space-y-3">
                <div class="flex gap-4">
                  <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded flex-1"></div>
                  <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded flex-1"></div>
                  <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded flex-1"></div>
                </div>
                <div class="h-8 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                <div class="h-8 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                <div class="h-8 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                <div class="h-8 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                <div class="h-8 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
              </div>
            </div>

            <!-- Skeleton: Grid 2 columnas -->
            <div class="grid md:grid-cols-2 gap-6">
              <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
                <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
                <div class="space-y-3">
                  <div class="h-10 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                  <div class="h-10 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                  <div class="h-10 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                  <div class="h-10 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
                <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
                <div class="space-y-3">
                  <div class="h-6 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                  <div class="h-6 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                  <div class="h-6 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
                </div>
              </div>
            </div>
          </template>

          <template v-else-if="results">
            <!-- Resultados numéricos -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                {{ mode === 'aql-ltpd' ? 'Plan de Muestreo Óptimo' : 'Resultados del Análisis' }}
              </h2>
              
              <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                    {{ mode === 'aql-ltpd' ? 'Tamaño Muestra' : 'AQL Calculado' }}
                  </p>
                  <p class="text-3xl font-bold text-black dark:text-white">
                    {{ mode === 'aql-ltpd' ? results.distancia_menor.n : ((results.distancia_menor.AQL || 0) * 100).toFixed(2) + '%' }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ mode === 'aql-ltpd' ? 'unidades' : 'nivel aceptable' }}
                  </p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                    {{ mode === 'aql-ltpd' ? 'Criterio' : 'LTPD Calculado' }}
                  </p>
                  <p class="text-3xl font-bold text-black dark:text-white">
                    {{ mode === 'aql-ltpd' ? results.distancia_menor.c : ((results.distancia_menor.LTPD || 0) * 100).toFixed(2) + '%' }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ mode === 'aql-ltpd' ? 'defectos máx' : 'tolerancia' }}
                  </p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
                  <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">Precisión</p>
                  <p class="text-2xl font-bold text-black dark:text-white">
                    {{ results.distancia_menor.distancia.toFixed(4) }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">distancia</p>
                </div>
              </div>

              <div class="mt-4 p-4 bg-black dark:bg-white text-white dark:text-black rounded-lg">
                <p class="text-sm font-medium mb-1">Regla de Decisión:</p>
                <p class="text-sm">
                  Inspecciona <strong>{{ results.distancia_menor.n }}</strong> unidades. 
                  Acepta el lote si encuentras <strong>{{ results.distancia_menor.c }} o menos</strong> defectos, 
                  rechaza si encuentras más.
                </p>
              </div>
            </div>

            <!-- Gráfica -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Curva Característica de Operación
              </h2>
              
              <div class="w-full h-80">
                <canvas ref="chartCanvas"></canvas>
              </div>

              <div class="mt-4 flex items-center justify-center gap-6 text-xs text-gray-600 dark:text-gray-300">
                <div class="flex items-center gap-2">
                  <div class="w-4 h-4 rounded-full bg-white dark:bg-gray-800 border-2 border-black dark:border-white"></div>
                  <span>Punto AQL</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-4 h-4 rounded-full bg-black dark:bg-white border-2 border-white dark:border-black"></div>
                  <span>Punto LTPD</span>
                </div>
              </div>
            </div>

            <!-- Tabla de Probabilidades -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Tabla de Probabilidades de Aceptación
              </h2>
              
              <div class="max-h-80 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg">
                <table class="w-full text-sm">
                  <thead class="sticky top-0 bg-gray-50 dark:bg-gray-700 border-b-2 border-gray-200 dark:border-gray-600">
                    <tr>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                        Proporción (p)
                      </th>
                      <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-200">
                        P(Aceptar)
                      </th>
                      <th class="px-4 py-3 text-center font-semibold text-gray-700 dark:text-gray-200">
                        Tipo
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(punto, index) in results.grafica"
                      :key="index"
                      :class="[
                        'border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors',
                        punto.AQT ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '',
                        punto.LTPD ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : ''
                      ]"
                    >
                      <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                        {{ punto.p.toFixed(4) }}
                      </td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ punto.res.toFixed(4) }}
                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">
                          ({{ (punto.res * 100).toFixed(2) }}%)
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span
                          v-if="punto.AQT"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 border-2 border-black dark:border-white text-black dark:text-white"
                        >
                          AQL
                        </span>
                        <span
                          v-else-if="punto.LTPD"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-black dark:bg-white text-white dark:text-black"
                        >
                          LTPD
                        </span>
                        <span v-else class="text-gray-400 dark:text-gray-500 text-xs">
                          —
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-xs text-gray-600 dark:text-gray-300">
                <p>
                  <strong>Nota:</strong> Valores más altos = mayor probabilidad de aceptar el lote.
                </p>
              </div>
            </div>

            <!-- Grid para las dos tablas pequeñas -->
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Tabla de Parámetros Utilizados -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
  <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
    Parámetros del Cálculo
  </h2>
  
  <table class="w-full text-sm">
    <tbody>
      <!-- Modo AQL/LTPD: Mostrar parámetros de entrada -->
      <template v-if="mode === 'aql-ltpd'">
        <tr class="border-b border-gray-100 dark:border-gray-700">
          <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">AQL (entrada)</td>
          <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
            {{ ((results.distancia_menor.AQT || 0) * 100).toFixed(2) }}%
          </td>
        </tr>
        <tr class="border-b border-gray-100 dark:border-gray-700">
          <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">LTPD (entrada)</td>
          <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
            {{ ((results.distancia_menor.LTPD || 0) * 100).toFixed(2) }}%
          </td>
        </tr>
      </template>

      <!-- Modo n/c: Mostrar n, c como entrada y AQL/LTPD calculados -->
      <template v-else>
        <tr class="border-b border-gray-100 dark:border-gray-700">
          <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">n (entrada)</td>
          <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
            {{ results.distancia_menor.n }}
          </td>
        </tr>
        <tr class="border-b border-gray-100 dark:border-gray-700">
          <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">c (entrada)</td>
          <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
            {{ results.distancia_menor.c }}
          </td>
        </tr>
        <tr class="border-b border-gray-100 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
          <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
            AQL (calculado)
          </td>
          <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
            {{ ((results.distancia_menor.AQL || 0) * 100).toFixed(2) }}%
          </td>
        </tr>
        <tr class="border-b border-gray-100 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20">
          <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
            LTPD (calculado)
          </td>
          <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
            {{ ((results.distancia_menor.LTPD || 0) * 100).toFixed(2) }}%
          </td>
        </tr>
      </template>

      <!-- Parámetros comunes para ambos modos -->
      <tr class="border-b border-gray-100 dark:border-gray-700">
        <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">1 - α</td>
        <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
          {{ (results.distancia_menor['1-alpha'] * 100).toFixed(2) }}%
        </td>
      </tr>
      <tr>
        <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">β</td>
        <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
          {{ (results.distancia_menor.beta * 100).toFixed(2) }}%
        </td>
      </tr>
    </tbody>
  </table>
</div>

            <!-- Resumen Estadístico -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                Interpretación
              </h2>
              
              <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                <div class="flex items-start gap-2">
                  <div class="w-2 h-2 rounded-full bg-black dark:bg-white mt-2 flex-shrink-0"></div>
                  <p>
                    <strong>Tamaño de muestra (n={{ results.distancia_menor.n }}):</strong> 
                    Número de unidades que debes inspeccionar de cada lote.
                  </p>
                </div>
                <div class="flex items-start gap-2">
                  <div class="w-2 h-2 rounded-full bg-black dark:bg-white mt-2 flex-shrink-0"></div>
                  <p>
                    <strong>Criterio de aceptación (c={{ results.distancia_menor.c }}):</strong> 
                    Acepta si hay {{ results.distancia_menor.c }} o menos defectos, rechaza si hay más.
                  </p>
                </div>
                <div class="flex items-start gap-2">
                  <div class="w-2 h-2 rounded-full bg-black dark:bg-white mt-2 flex-shrink-0"></div>
                  <p>
                    <strong>Precisión ({{ results.distancia_menor.distancia.toFixed(4) }}):</strong> 
                    Qué tan cerca está el plan de los valores ideales deseados.
                  </p>
                </div>
              </div>
            </div>
          </div>
          </template>

          <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 border border-gray-200 dark:border-gray-700 text-center">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            <p class="text-gray-500 dark:text-gray-400">
              Ingresa los parámetros y haz clic en calcular para ver los resultados
            </p>
          </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import type { Chart as ChartType } from 'chart.js/auto';
import MainLayout from '@/layouts/MainLayout.vue';
import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

const breadcrumbs = [
  { title: 'Inicio', href: '/' },
  { title: 'Muestreo de Aceptación' }
];

interface FormData {
  AQT: string;
  LTPD: string;
  '1-alpha': string;
  beta: string;
  n: string;
  c: string;
}

interface FormErrors {
  [key: string]: string;
}

interface GraficaPoint {
  p: number;
  res: number;
  AQT?: boolean;
  LTPD?: boolean;
}

interface ResultData {
  distancia_menor: {
    n: number;
    c: number;
    distancia: number;
    AQT?: number;
    LTPD?: number;
    AQL?: number;
    '1-alpha': number;
    beta: number;
  };
  grafica: GraficaPoint[];
}

const formData = ref<FormData>({
  AQT: '',
  LTPD: '',
  '1-alpha': '',
  n: '',
  c: '',
  beta: ''
});

const mode = ref('aql-ltpd'); // 'aql-ltpd' o 'n-c'
const errors = ref<FormErrors>({});
const loading = ref(false);
const results = ref<ResultData | null>(null);
const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: ChartType | null = null;

const clearError = (field: string) => {
  if (errors.value[field]) {
    delete errors.value[field];
  }
};

const validate = () => {
  const newErrors: FormErrors = {};
  
  if (mode.value === 'aql-ltpd') {
    // Validar AQL, LTPD, 1-alpha, beta
    ['AQT', 'LTPD', '1-alpha', 'beta'].forEach(key => {
      const value = parseFloat(formData.value[key as keyof FormData]);
      if (!formData.value[key as keyof FormData]) {
        newErrors[key] = 'Campo requerido';
      } else if (isNaN(value) || value < 0 || value > 1) {
        newErrors[key] = 'Debe ser entre 0 y 1';
      }
    });

    if (!newErrors.AQT && !newErrors.LTPD) {
      if (parseFloat(formData.value.LTPD) < parseFloat(formData.value.AQT)) {
        newErrors.LTPD = 'LTPD debe ser mayor que AQL';
      }
    }
  } else {
    // Validar n, c, 1-alpha, beta
    const n = parseInt(formData.value.n);
    const c = parseInt(formData.value.c);
    
    if (!formData.value.n) {
      newErrors.n = 'Campo requerido';
    } else if (isNaN(n) || n < 2) {
      newErrors.n = 'Debe ser entero mayor o igual a 2';
    }

    if (formData.value.c !== '0' && !formData.value.c) {
      newErrors.c = 'Campo requerido';
    } else if (isNaN(c) || c < 0 || c > 7) {
      newErrors.c = 'Debe ser entero entre 0 y 7';
    }

    ['1-alpha', 'beta'].forEach(key => {
      const value = parseFloat(formData.value[key as keyof FormData]);
      if (!formData.value[key as keyof FormData]) {
        newErrors[key] = 'Campo requerido';
      } else if (isNaN(value) || value < 0 || value > 1) {
        newErrors[key] = 'Debe ser entre 0 y 1';
      }
    });
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

const renderChart = async (data: ResultData) => {
  await nextTick();
  
  if (!chartCanvas.value) return;

  // Destruir gráfica anterior si existe
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');
  if (!ctx) return;
  
  // Preparar datos
  const chartData = data.grafica.map(point => ({
    x: point.p,
    y: point.res,
    isAQT: point.AQT,
    isLTPD: point.LTPD
  }));

  chartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      datasets: [{
        label: 'Probabilidad de Aceptación',
        data: chartData,
        borderColor: '#000',
        backgroundColor: 'rgba(0, 0, 0, 0.1)',
        borderWidth: 2,
        pointRadius: (context) => {
          const point = context.raw as { isAQT?: boolean; isLTPD?: boolean };
          return (point.isAQT || point.isLTPD) ? 6 : 0;
        },
        pointBackgroundColor: (context) => {
          const point = context.raw as { isAQT?: boolean; isLTPD?: boolean };
          if (point.isAQT) return '#fff';
          if (point.isLTPD) return '#000';
          return '#000';
        },
        pointBorderColor: (context) => {
          const point = context.raw as { isAQT?: boolean; isLTPD?: boolean };
          if (point.isAQT) return '#000';
          if (point.isLTPD) return '#fff';
          return '#000';
        },
        pointBorderWidth: 2,
        tension: 0.3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top'
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              return `P(Aceptar): ${context.parsed.y.toFixed(4)}`;
            },
            title: (context) => {
              return `p = ${context[0].parsed.x.toFixed(4)}`;
            }
          }
        }
      },
      scales: {
        x: {
          type: 'linear',
          title: {
            display: true,
            text: 'Proporción de Defectos (p)'
          },
          min: 0,
          max: Math.max(...chartData.map((d: { x: number }) => d.x)) * 1.1
        },
        y: {
          title: {
            display: true,
            text: 'P(Aceptar)'
          },
          min: 0,
          max: 1
        }
      }
    }
  });
};

const handleSubmit = async () => {
  if (!validate()) return;

  loading.value = true;
  const endpoint = mode.value === 'aql-ltpd' ? '/calc-muestroaceptacion' : '/calc-muestroaceptacion2';
  
  // Preparar datos según el modo
  const dataToSend = mode.value === 'aql-ltpd' 
    ? {
        AQT: formData.value.AQT,
        LTPD: formData.value.LTPD,
        '1-alpha': formData.value['1-alpha'],
        beta: formData.value.beta
      }
    : {
        n: parseInt(formData.value.n),
        c: parseInt(formData.value.c),
        '1-alpha': formData.value['1-alpha'],
        beta: formData.value.beta
      };

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken?.content || ''
      },
      body: JSON.stringify(dataToSend)
    });

    const data = await response.json();
    
    if (response.ok) {
      results.value = data;
      loading.value = false;
      await nextTick(); // Esperar a que el DOM se actualice
      await renderChart(data);
    } else {
      // Manejar errores del servidor
      if (data.errors) {
        errors.value = data.errors;
      }
      loading.value = false;
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Hubo un error al calcular. Por favor intenta de nuevo.');
    loading.value = false;
  }
};

// Driver.js tour configuration
const startTour = () => {
  const driverObj = driver({
    showProgress: true,
    showButtons: ['next', 'previous', 'close'],
    steps: [
      {
        element: '#mode-selector',
        popover: {
          title: '🎯 Selector de Modo',
          description: 'Elige entre dos modos de cálculo: <strong>AQL/LTPD</strong> para calcular n y c, o <strong>n/c</strong> para calcular AQL y LTPD.',
          side: 'bottom',
          align: 'center'
        }
      },
      {
        element: '#form-container',
        popover: {
          title: '📝 Formulario de Parámetros',
          description: 'Ingresa los valores necesarios según el modo seleccionado. Todos los campos son obligatorios y deben estar en el rango válido.',
          side: 'right',
          align: 'start'
        }
      },
      {
        element: mode.value === 'aql-ltpd' ? '#aql-input' : '#n-input',
        popover: {
          title: mode.value === 'aql-ltpd' ? '📊 AQL (Nivel de Calidad Aceptable)' : '🔢 Tamaño de Muestra (n)',
          description: mode.value === 'aql-ltpd' 
            ? 'Porcentaje de defectos que consideras <strong>aceptable</strong> en tu proceso. Valor entre 0 y 1 (ej: 0.02 = 2%).'
            : 'Número de unidades que deseas inspeccionar de cada lote. Debe ser al menos 2.',
          side: 'left',
          align: 'start'
        }
      },
      {
        element: '#alpha-input',
        popover: {
          title: '✨ Confianza del Productor (1-α)',
          description: 'Probabilidad de <strong>aceptar</strong> un lote bueno. Valor típico: <strong>0.95</strong> (95% de confianza).',
          side: 'left',
          align: 'start'
        }
      },
      {
        element: '#beta-input',
        popover: {
          title: '⚠️ Riesgo del Consumidor (β)',
          description: 'Probabilidad de <strong>aceptar</strong> un lote malo. Valor típico: <strong>0.10</strong> (10% de riesgo).',
          side: 'left',
          align: 'start'
        }
      },
      {
        element: '#submit-button',
        popover: {
          title: '🚀 Calcular Resultados',
          description: 'Haz clic aquí para ejecutar el cálculo. Verás skeletons mientras procesa y luego los resultados completos.',
          side: 'top',
          align: 'center'
        }
      },
      {
        element: '#guide-info',
        popover: {
          title: '💡 Guía Rápida',
          description: 'Aquí siempre encontrarás recordatorios sobre el significado de cada parámetro según el modo activo.',
          side: 'top',
          align: 'start'
        }
      }
    ],
    nextBtnText: 'Siguiente →',
    prevBtnText: '← Anterior',
    doneBtnText: '¡Entendido! ✓',
    progressText: '{{current}} de {{total}}',
    onDestroyStarted: () => {
      // Guardar en localStorage que ya se vio el tour
      localStorage.setItem('muestreo-tour-completed', 'true');
      driverObj.destroy();
    }
  });

  driverObj.drive();
};

// Verificar si mostrar el tour automáticamente
onMounted(() => {
  const tourCompleted = localStorage.getItem('muestreo-tour-completed');
  if (!tourCompleted) {
    // Esperar un poco para que la página se cargue completamente
    setTimeout(() => {
      startTour();
    }, 800);
  }
});

</script>

<style>
@import '../../css/driver.css';
</style>