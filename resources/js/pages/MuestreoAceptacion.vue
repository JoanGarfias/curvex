<template>
  <Head title="Muestreo de Aceptación" />
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-3 mb-3">
          <svg class="w-8 h-8 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
            Muestreo de Aceptación
          </h1>
        </div>
        <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
          Calcula el plan de muestreo óptimo basado en niveles de calidad aceptable y tolerancia
        </p>

        <!-- Selector de Modo -->
        <div class="mt-6 flex justify-center">
          <div class="inline-flex bg-white dark:bg-gray-800 rounded-lg p-1 border-2 border-gray-200 dark:border-gray-700">
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
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Formulario - Columna fija -->
        <div class="lg:col-span-1">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 sticky top-4">
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
                <div>
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
                <div>
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
              <div>
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
              <div>
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
                @click="handleSubmit"
                :disabled="loading"
                class="w-full bg-black dark:bg-white text-white dark:text-black py-3 rounded-lg font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors disabled:bg-gray-400 dark:disabled:bg-gray-600 disabled:cursor-not-allowed"
              >
                {{ loading ? 'Calculando...' : 'Calcular Plan de Muestreo' }}
              </button>
            </div>

            <!-- Guía -->
            <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
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
          <template v-if="results">
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
                    {{ mode === 'aql-ltpd' ? results.distancia_menor.n : (results.distancia_menor.AQL * 100).toFixed(2) + '%' }}
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
                    {{ mode === 'aql-ltpd' ? results.distancia_menor.c : (results.distancia_menor.LTPD * 100).toFixed(2) + '%' }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ mode === 'aql-ltpd' ? 'defectos máx' : 'tolerancia lote' }}
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
                    <tr v-if="mode === 'n-c'" class="border-b border-gray-100 dark:border-gray-700">
                      <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">n</td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ results.distancia_menor.n }}
                      </td>
                    </tr>
                    <tr v-if="mode === 'n-c'" class="border-b border-gray-100 dark:border-gray-700">
                      <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">c</td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ results.distancia_menor.c }}
                      </td>
                    </tr>
                    <tr v-if="mode === 'aql-ltpd'" class="border-b border-gray-100 dark:border-gray-700">
                      <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">AQL</td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ (results.distancia_menor.AQT * 100).toFixed(2) }}%
                      </td>
                    </tr>
                    <tr v-if="mode === 'aql-ltpd'" class="border-b border-gray-100 dark:border-gray-700">
                      <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">LTPD</td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ (results.distancia_menor.LTPD * 100).toFixed(2) }}%
                      </td>
                    </tr>
                    <tr v-if="mode === 'n-c'" class="border-b border-gray-100 dark:border-gray-700">
                      <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">AQL (calculado)</td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ (results.distancia_menor.AQL * 100).toFixed(2) }}%
                      </td>
                    </tr>
                    <tr v-if="mode === 'n-c'" class="border-b border-gray-100 dark:border-gray-700">
                      <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">LTPD (calculado)</td>
                      <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                        {{ (results.distancia_menor.LTPD * 100).toFixed(2) }}%
                      </td>
                    </tr>
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
    </div>
  </div>
</template>

<script>
import { ref, nextTick } from 'vue';
import { Head } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';

export default {
  name: 'MuestreoAceptacion',
  components: {
    Head
  },
  
  setup() {
    const mode = ref('aql-ltpd'); // 'aql-ltpd' o 'n-c'
    
    const formData = ref({
      AQT: '',
      LTPD: '',
      n: '',
      c: '',
      '1-alpha': '',
      beta: ''
    });

    const errors = ref({});
    const loading = ref(false);
    const results = ref(null);
    const chartCanvas = ref(null);
    let chartInstance = null;

    const clearError = (field) => {
      if (errors.value[field]) {
        delete errors.value[field];
      }
    };

    const validate = () => {
      const newErrors = {};
      
      if (mode.value === 'aql-ltpd') {
        // Validar AQL, LTPD, 1-alpha, beta
        ['AQT', 'LTPD', '1-alpha', 'beta'].forEach(key => {
          const value = parseFloat(formData.value[key]);
          if (!formData.value[key]) {
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
          const value = parseFloat(formData.value[key]);
          if (!formData.value[key]) {
            newErrors[key] = 'Campo requerido';
          } else if (isNaN(value) || value < 0 || value > 1) {
            newErrors[key] = 'Debe ser entre 0 y 1';
          }
        });
      }

      errors.value = newErrors;
      return Object.keys(newErrors).length === 0;
    };

    const renderChart = async (data) => {
      await nextTick();
      
      if (!chartCanvas.value) return;

      // Destruir gráfica anterior si existe
      if (chartInstance) {
        chartInstance.destroy();
      }

      const ctx = chartCanvas.value.getContext('2d');
      
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
              const point = context.raw;
              return (point.isAQT || point.isLTPD) ? 6 : 0;
            },
            pointBackgroundColor: (context) => {
              const point = context.raw;
              if (point.isAQT) return '#fff';
              if (point.isLTPD) return '#000';
              return '#000';
            },
            pointBorderColor: (context) => {
              const point = context.raw;
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
              max: Math.max(...chartData.map(d => d.x)) * 1.1
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
      
      // Limpiar resultados anteriores
      results.value = null;

      try {
        // Decidir qué endpoint usar según el modo
        const endpoint = mode.value === 'aql-ltpd' ? '/test-muestroaceptacion' : '/test-muestroaceptacion2';
        
        // Preparar datos según el modo
        const dataToSend = mode.value === 'aql-ltpd' 
          ? {
              AQT: formData.value.AQT,
              LTPD: formData.value.LTPD,
              '1-alpha': formData.value['1-alpha'],
              beta: formData.value.beta
            }
          : {
              n: formData.value.n,
              c: formData.value.c,
              '1-alpha': formData.value['1-alpha'],
              beta: formData.value.beta
            };

        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(dataToSend)
        });

        const data = await response.json();
        
        if (response.ok) {
          results.value = data;
          await renderChart(data);
        } else {
          // Manejar errores del servidor
          if (data.errors) {
            errors.value = data.errors;
          }
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Hubo un error al calcular. Por favor intenta de nuevo.');
      } finally {
        loading.value = false;
      }
    };

    return {
      mode,
      formData,
      errors,
      loading,
      results,
      chartCanvas,
      clearError,
      handleSubmit
    };
  }
};
</script>