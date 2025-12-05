<template>
  <div class="space-y-6">
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
        <div v-if="mode === 'aql-ltpd'">
          <table class="w-full text-sm">
          <tbody>
            <tr class="border-b border-gray-100 dark:border-gray-700">
              <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">AQL</td>
              <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                {{ ((results.distancia_menor.AQT || results.distancia_menor.AQL || 0) * 100).toFixed(2) }}%
              </td>
            </tr>
            <tr class="border-b border-gray-100 dark:border-gray-700">
              <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">LTPD</td>
              <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                {{ ((results.distancia_menor.LTPD || 0) * 100).toFixed(2) }}%
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
        <div v-else>
          <table class="w-full text-sm">
          <tbody>
            <tr class="border-b border-gray-100 dark:border-gray-700">
              <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">n</td>
              <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                {{ ((results.distancia_menor.n || 0))}}
              </td>
            </tr>
            <tr class="border-b border-gray-100 dark:border-gray-700">
              <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">c</td>
              <td class="px-4 py-3 text-right text-gray-900 dark:text-gray-100">
                {{ ((results.distancia_menor.c || 0) )}}
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
      </div>

      <!-- Resumen Estadístico -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
          Interpretación
        </h2>
        <div v-if="mode === 'aql-ltpd'">
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
        <div v-else>
          <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
          <div class="flex items-start gap-2">
            <div class="w-2 h-2 rounded-full bg-black dark:bg-white mt-2 flex-shrink-0"></div>
            <p>
              <strong>Límite de calidad aceptable (AQL={{ ((results.distancia_menor.AQT || results.distancia_menor.AQL || 0) * 100).toFixed(2) }}%):</strong> 
              Representa el máximo porcentaje de defectos que puede considerarse satisfactorio para una muestra específica.
            </p>
          </div>
          <div class="flex items-start gap-2">
            <div class="w-2 h-2 rounded-full bg-black dark:bg-white mt-2 flex-shrink-0"></div>
            <p>
              <strong>Porcentaje de tolerancia de lote defectuoso (LTPD={{ ((results.distancia_menor.LTPD || 0) * 100).toFixed(2) }}%):</strong> 
              Representa el peor nivel de calidad que el consumidor está dispuesto a tolerar en cualquier lote.
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
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import type { ResultData, ModeType } from '@/types/muestreo';
import { useMuestreoChart } from '@/composables/useMuestreoChart';

const props = defineProps<{
  results: ResultData;
  mode: ModeType;
}>();

const { chartCanvas, renderChart, destroyChart } = useMuestreoChart();

onMounted(() => {
  renderChart(props.results);
});

onUnmounted(() => {
  destroyChart();
});
</script>
