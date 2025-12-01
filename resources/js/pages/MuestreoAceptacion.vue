<template>
  <Head title="Muestreo de Aceptación" />
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center gap-3 mb-3">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
          <h1 class="text-3xl md:text-4xl font-bold text-gray-900">
            Muestreo de Aceptación
          </h1>
        </div>
        <p class="text-gray-600 max-w-2xl mx-auto">
          Calcula el plan de muestreo óptimo basado en niveles de calidad aceptable y tolerancia
        </p>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Formulario - Columna fija -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200 sticky top-4">
          <div class="flex items-center gap-2 mb-6">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <h2 class="text-xl font-semibold text-gray-900">Parámetros</h2>
          </div>

          <div class="space-y-5">
            <!-- AQL -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                AQL (Nivel de Calidad Aceptable)
                <span class="ml-1 text-gray-400 text-xs">0 - 1</span>
              </label>
              <input
                v-model="formData.AQT"
                type="number"
                step="0.01"
                min="0"
                max="1"
                :class="[
                  'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                  errors.AQT ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300 focus:border-black'
                ]"
                placeholder="Ej: 0.02"
                @input="clearError('AQT')"
              />
              <p v-if="errors.AQT" class="text-red-500 text-xs mt-1">{{ errors.AQT }}</p>
            </div>

            <!-- LTPD -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                LTPD (Tolerancia del Lote)
                <span class="ml-1 text-gray-400 text-xs">0 - 1</span>
              </label>
              <input
                v-model="formData.LTPD"
                type="number"
                step="0.01"
                min="0"
                max="1"
                :class="[
                  'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                  errors.LTPD ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300 focus:border-black'
                ]"
                placeholder="Ej: 0.10"
                @input="clearError('LTPD')"
              />
              <p v-if="errors.LTPD" class="text-red-500 text-xs mt-1">{{ errors.LTPD }}</p>
            </div>

            <!-- 1-alpha -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                1 - α (Confianza del Productor)
                <span class="ml-1 text-gray-400 text-xs">0 - 1</span>
              </label>
              <input
                v-model="formData['1-alpha']"
                type="number"
                step="0.01"
                min="0"
                max="1"
                :class="[
                  'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                  errors['1-alpha'] ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300 focus:border-black'
                ]"
                placeholder="Ej: 0.95"
                @input="clearError('1-alpha')"
              />
              <p v-if="errors['1-alpha']" class="text-red-500 text-xs mt-1">{{ errors['1-alpha'] }}</p>
            </div>

            <!-- Beta -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                β (Riesgo del Consumidor)
                <span class="ml-1 text-gray-400 text-xs">0 - 1</span>
              </label>
              <input
                v-model="formData.beta"
                type="number"
                step="0.01"
                min="0"
                max="1"
                :class="[
                  'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
                  errors.beta ? 'border-red-300 bg-red-50' : 'border-gray-200 hover:border-gray-300 focus:border-black'
                ]"
                placeholder="Ej: 0.10"
                @input="clearError('beta')"
              />
              <p v-if="errors.beta" class="text-red-500 text-xs mt-1">{{ errors.beta }}</p>
            </div>

            <button
              @click="handleSubmit"
              :disabled="loading"
              class="w-full bg-black text-white py-3 rounded-lg font-semibold hover:bg-gray-800 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Calculando...' : 'Calcular Plan de Muestreo' }}
            </button>
          </div>

          <!-- Guía -->
          <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="text-xs text-gray-600">
                <p class="font-medium mb-1">Guía rápida:</p>
                <ul class="space-y-1 list-disc list-inside">
                  <li>AQL: % defectos que consideras aceptable</li>
                  <li>LTPD: % defectos máximo tolerable</li>
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
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Plan de Muestreo Óptimo
              </h2>
              
              <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                  <p class="text-sm text-gray-600 mb-1">Tamaño Muestra</p>
                  <p class="text-3xl font-bold text-black">
                    {{ results.distancia_menor.n }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">unidades</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                  <p class="text-sm text-gray-600 mb-1">Criterio</p>
                  <p class="text-3xl font-bold text-black">
                    {{ results.distancia_menor.c }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">defectos máx</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                  <p class="text-sm text-gray-600 mb-1">Precisión</p>
                  <p class="text-2xl font-bold text-black">
                    {{ results.distancia_menor.distancia.toFixed(4) }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">distancia</p>
                </div>
              </div>

              <div class="mt-4 p-4 bg-black text-white rounded-lg">
                <p class="text-sm font-medium mb-1">Regla de Decisión:</p>
                <p class="text-sm">
                  Inspecciona <strong>{{ results.distancia_menor.n }}</strong> unidades. 
                  Acepta el lote si encuentras <strong>{{ results.distancia_menor.c }} o menos</strong> defectos, 
                  rechaza si encuentras más.
                </p>
              </div>
            </div>

            <!-- Gráfica -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Curva Característica de Operación
              </h2>
              
              <div class="w-full h-80">
                <canvas ref="chartCanvas"></canvas>
              </div>

              <div class="mt-4 flex items-center justify-center gap-6 text-xs text-gray-600">
                <div class="flex items-center gap-2">
                  <div class="w-4 h-4 rounded-full bg-white border-2 border-black"></div>
                  <span>Punto AQL</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-4 h-4 rounded-full bg-black border-2 border-white"></div>
                  <span>Punto LTPD</span>
                </div>
              </div>
            </div>

            <!-- Tabla de Probabilidades -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Tabla de Probabilidades de Aceptación
              </h2>
              
              <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                  <thead class="sticky top-0 bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">
                        Proporción (p)
                      </th>
                      <th class="px-4 py-3 text-right font-semibold text-gray-700">
                        P(Aceptar)
                      </th>
                      <th class="px-4 py-3 text-center font-semibold text-gray-700">
                        Tipo
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="(punto, index) in results.grafica"
                      :key="index"
                      :class="[
                        'border-b border-gray-100 hover:bg-gray-50 transition-colors',
                        punto.AQT ? 'bg-gray-100 font-semibold' : '',
                        punto.LTPD ? 'bg-gray-100 font-semibold' : ''
                      ]"
                    >
                      <td class="px-4 py-3 text-gray-900">
                        {{ punto.p.toFixed(4) }}
                      </td>
                      <td class="px-4 py-3 text-right text-gray-900">
                        {{ punto.res.toFixed(4) }}
                        <span class="text-xs text-gray-500 ml-1">
                          ({{ (punto.res * 100).toFixed(2) }}%)
                        </span>
                      </td>
                      <td class="px-4 py-3 text-center">
                        <span
                          v-if="punto.AQT"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white border-2 border-black text-black"
                        >
                          AQL
                        </span>
                        <span
                          v-else-if="punto.LTPD"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-black text-white"
                        >
                          LTPD
                        </span>
                        <span v-else class="text-gray-400 text-xs">
                          —
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-600">
                <p>
                  <strong>Nota:</strong> Valores más altos = mayor probabilidad de aceptar el lote.
                </p>
              </div>
            </div>

            <!-- Grid para las dos tablas pequeñas -->
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Tabla de Parámetros Utilizados -->
              <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Parámetros del Cálculo
              </h2>
              
              <table class="w-full text-sm">
                <tbody>
                  <tr class="border-b border-gray-100">
                    <td class="px-4 py-3 font-medium text-gray-700">AQL</td>
                    <td class="px-4 py-3 text-right text-gray-900">
                      {{ (results.distancia_menor.AQT * 100).toFixed(2) }}%
                    </td>
                  </tr>
                  <tr class="border-b border-gray-100">
                    <td class="px-4 py-3 font-medium text-gray-700">LTPD</td>
                    <td class="px-4 py-3 text-right text-gray-900">
                      {{ (results.distancia_menor.LTPD * 100).toFixed(2) }}%
                    </td>
                  </tr>
                  <tr class="border-b border-gray-100">
                    <td class="px-4 py-3 font-medium text-gray-700">1 - α</td>
                    <td class="px-4 py-3 text-right text-gray-900">
                      {{ (results.distancia_menor['1-alpha'] * 100).toFixed(2) }}%
                    </td>
                  </tr>
                  <tr>
                    <td class="px-4 py-3 font-medium text-gray-700">β</td>
                    <td class="px-4 py-3 text-right text-gray-900">
                      {{ (results.distancia_menor.beta * 100).toFixed(2) }}%
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Resumen Estadístico -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-200">
              <h2 class="text-xl font-semibold text-gray-900 mb-4">
                Interpretación
              </h2>
              
              <div class="space-y-3 text-sm text-gray-700">
                <div class="flex items-start gap-2">
                  <div class="w-2 h-2 rounded-full bg-black mt-2 flex-shrink-0"></div>
                  <p>
                    <strong>Tamaño de muestra (n={{ results.distancia_menor.n }}):</strong> 
                    Número de unidades que debes inspeccionar de cada lote.
                  </p>
                </div>
                <div class="flex items-start gap-2">
                  <div class="w-2 h-2 rounded-full bg-black mt-2 flex-shrink-0"></div>
                  <p>
                    <strong>Criterio de aceptación (c={{ results.distancia_menor.c }}):</strong> 
                    Acepta si hay {{ results.distancia_menor.c }} o menos defectos, rechaza si hay más.
                  </p>
                </div>
                <div class="flex items-start gap-2">
                  <div class="w-2 h-2 rounded-full bg-black mt-2 flex-shrink-0"></div>
                  <p>
                    <strong>Precisión ({{ results.distancia_menor.distancia.toFixed(4) }}):</strong> 
                    Qué tan cerca está el plan de los valores ideales deseados.
                  </p>
                </div>
              </div>
            </div>
          </div>
          </template>

          <div v-else class="bg-white rounded-2xl shadow-lg p-12 border border-gray-200 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            <p class="text-gray-500">
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
    const formData = ref({
      AQT: '',
      LTPD: '',
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
      
      Object.keys(formData.value).forEach(key => {
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

      try {
        const response = await fetch('/test-muestroaceptacion', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify(formData.value)
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

<style scoped>
/* Tailwind classes ya incluidas en el template */
</style>