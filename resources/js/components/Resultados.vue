<script setup lang="ts">
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/ui/tooltip"
import { GitGraph, Table2, Download, Loader2, FileSpreadsheet, ChevronDown, ChevronUp, Info } from "lucide-vue-next"
import CurvexIcon from '@/icons/CurvexIcon.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import FooterComp from '@/components/FooterComp.vue';

import { BarChart } from "vue-chart-3"
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

import { ref, toRef, computed } from 'vue';
import type { Resultado } from '@/types/Resultado';

//Nueva propiedad: Suma total
const suma = computed(() => {
  if (!datos || datos.length === 0) return '0';
  const total = datos.reduce((acc, val) => acc + val, 0);
  return truncate(total, Number(decimales.value));
});


interface Props{
  resultado: Resultado;
}

const props = defineProps<Props>();
const resultado = toRef(props, 'resultado');

const emit = defineEmits<{
  goBack: []
}>();

const decimales = ref<number>(8);
const chartColor = ref<string>('#4bc0c0'); // Color por defecto (turquesa)
const isDownloading = ref<boolean>(false);
const isExporting = ref<boolean>(false);
const showAllPercentiles = ref<boolean>(false);

// Percentiles importantes para mostrar por defecto (cada 5)
const keyPercentiles = computed(() => {
  return resultado.value.percentiles.filter((percentil) => {
    const key = Number(Object.keys(percentil)[0]);
    return key % 5 === 0; // Mostrar P5, P10, P15, ... P95
  });
});

// Función helper para obtener el valor de un percentil específico
function getPercentileValue(percentileNumber: number): number | null {
  if (percentileNumber < 1 || percentileNumber > 99) return null;
  const percentil = resultado.value.percentiles.find(p => Number(Object.keys(p)[0]) === percentileNumber);
  return percentil ? Object.values(percentil)[0] : null;
}

// Función para truncar (cortar) sin redondear
const truncate = (num: number, decimals: number): string => {
  const multiplier = Math.pow(10, decimals);
  const truncated = Math.trunc(num * multiplier) / multiplier;
  return truncated.toFixed(decimals);
};

// Computed properties para formatear los valores reactivamente (truncados, no redondeados)
const promedio = computed(() => truncate(resultado.value.mean, Number(decimales.value)));
const minimo = computed(() => truncate(resultado.value.min, Number(decimales.value)));
const maximo = computed(() => truncate(resultado.value.max, Number(decimales.value)));
const rango = computed(() => truncate(resultado.value.range, Number(decimales.value)));
const varianza = computed(() => truncate(resultado.value.variance, Number(decimales.value)));
const desviacionEstandar = computed(() => truncate(resultado.value.standard_deviation, Number(decimales.value)));
const curtosis = computed(() => truncate(resultado.value.kurtosis, Number(decimales.value)));
const datos = resultado.value.data;
console.log('Datos recibidos:', datos);

// --- Generar histograma a partir de los datos ---
const histogramData = computed(() => {
  if (!datos || datos.length === 0) {
    return {
      labels: ['Sin datos'],
      datasets: [{
        label: 'Frecuencia',
        data: [0],
        backgroundColor: `${chartColor.value}99`, // Añade transparencia (60%)
        borderColor: chartColor.value,
        borderWidth: 1,
      }]
    };
  }

  // Calcular el número de bins (método de Sturges)
  const n = datos.length;
  const numBins = Math.ceil(Math.log2(n) + 1);
  
  const min = Math.min(...datos);
  const max = Math.max(...datos);
  const binWidth = (max - min) / numBins;

  // Crear bins
  const bins: number[] = new Array(numBins).fill(0);
  const labels: string[] = [];

  // Contar frecuencias
  datos.forEach((value: number) => {
    const binIndex = Math.min(Math.floor((value - min) / binWidth), numBins - 1);
    bins[binIndex]++;
  });

  // Crear etiquetas para los bins
  for (let i = 0; i < numBins; i++) {
    const start = (min + i * binWidth).toFixed(2);
    const end = (min + (i + 1) * binWidth).toFixed(2);
    labels.push(`${start}-${end}`);
  }

  return {
    labels: labels,
    datasets: [{
      label: 'Frecuencia',
      data: bins,
      backgroundColor: `${chartColor.value}99`, // Añade transparencia (60%)
      borderColor: chartColor.value,
      borderWidth: 1,
    }]
  };
});

const histogramOptions = ref({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
    title: {
      display: true,
      text: 'Histograma de Frecuencias',
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      title: {
        display: true,
        text: 'Frecuencia'
      }
    },
    x: {
      title: {
        display: true,
        text: 'Rangos de valores'
      }
    }
  }
});

function handleGoBack() {
  emit('goBack');
}

async function downloadHistogram() {
  isDownloading.value = true;
  
  try {
    // Pequeño delay para mostrar la animación de carga
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Obtener el canvas del gráfico
    const chartElement = document.querySelector('.histogram-chart canvas') as HTMLCanvasElement;
    
    if (!chartElement) {
      console.error('No se encontró el canvas del histograma');
      return;
    }

    // Convertir el canvas a imagen
    const url = chartElement.toDataURL('image/png');
    
    // Crear un enlace temporal para descargar
    const link = document.createElement('a');
    link.download = `histograma-${new Date().toISOString().split('T')[0]}.png`;
    link.href = url;
    link.click();
  } catch (error) {
    console.error('Error al descargar el histograma:', error);
  } finally {
    isDownloading.value = false;
  }
}

async function exportToExcel() {
  isExporting.value = true;
  
  try {
    // Pequeño delay para mostrar la animación de carga
    await new Promise(resolve => setTimeout(resolve, 500));
    
    let csvContent = '';
    
    // Fila 1: Título de los datos
    csvContent += 'DATOS ORIGINALES\n';
    
    // Dividir los datos en filas de 10 columnas
    const columnsPerRow = 10;
    for (let i = 0; i < datos.length; i += columnsPerRow) {
      const chunk = datos.slice(i, i + columnsPerRow);
      csvContent += chunk.join(',') + '\n';
    }
    
    // Espacios en blanco
    csvContent += '\n\n';
    
    // Estadísticas básicas
    csvContent += 'ESTADÍSTICAS BÁSICAS\n';
    csvContent += 'Estadística,Valor\n';
    csvContent += `Suma,${suma.value}\n`;
    csvContent += `Promedio,${promedio.value}\n`;
    csvContent += `Valor Mínimo,${minimo.value}\n`;
    csvContent += `Valor Máximo,${maximo.value}\n`;
    csvContent += `Rango,${rango.value}\n`;
    csvContent += `Varianza,${varianza.value}\n`;
    csvContent += `Desviación Estándar,${desviacionEstandar.value}\n`;
    csvContent += `Curtosis,${curtosis.value}\n`;
    csvContent += `Cantidad de datos,${resultado.value.count}\n\n`;
    
    // Tabla de frecuencias (si existe)
    if (resultado.value.frequency_table) {
      csvContent += 'TABLA DE FRECUENCIAS\n';
      csvContent += `Número de intervalos: ${resultado.value.frequency_table.info_intervalos.numero_intervalos}\n`;
      csvContent += `Ancho del intervalo: ${resultado.value.frequency_table.info_intervalos.ancho_intervalo}\n\n`;
      
      // Encabezados de la tabla
      csvContent += 'Clase,Lím. Inf.,Lím. Sup.,Marca,Frec. Abs.,Frec. Acum.,Frec. Rel. %,Frec. Rel. Acum. %\n';
      
      // Filas de la tabla
      resultado.value.frequency_table.tabla_frecuencias.forEach(row => {
        csvContent += `${row.clase},${row.limite_inferior},${row.limite_superior},${row.marca_de_clase},${row.frecuencia_absoluta},${row.frecuencia_abs_acumulada},${row.frecuencia_relativa_pct},${row.frecuencia_rel_acumulada_pct}\n`;
      });
      csvContent += '\n';
    }
    
    // Medidas de Posición
    csvContent += 'MEDIDAS DE POSICIÓN\n\n';
    
    // Cuartiles
    csvContent += 'CUARTILES\n';
    csvContent += 'Cuartil,Valor\n';
    resultado.value.cuartiles.forEach(cuartil => {
      const key = Number(Object.keys(cuartil)[0]);
      const value = Object.values(cuartil)[0];
      csvContent += `Q${key} (${(key / 4 * 100).toFixed(0)}%),${truncate(value, Number(decimales.value))}\n`;
    });
    csvContent += '\n';
    
    // Deciles
    csvContent += 'DECILES\n';
    csvContent += 'Decil,Valor\n';
    resultado.value.deciles.forEach(decil => {
      const key = Number(Object.keys(decil)[0]);
      const value = Object.values(decil)[0];
      csvContent += `D${key} (${(key / 10 * 100).toFixed(0)}%),${truncate(value, Number(decimales.value))}\n`;
    });
    csvContent += '\n';
    
    // Percentiles
    csvContent += 'PERCENTILES\n';
    csvContent += 'Percentil,Valor\n';
    resultado.value.percentiles.forEach(percentil => {
      const key = Number(Object.keys(percentil)[0]);
      const value = Object.values(percentil)[0];
      csvContent += `P${key},${truncate(value, Number(decimales.value))}\n`;
    });
    
    // Crear un blob con el contenido CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    
    // Crear un enlace temporal para descargar
    const link = document.createElement('a');
    link.href = url;
    link.download = `analisis-estadistico-${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
    
    // Limpiar el objeto URL
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al exportar los datos:', error);
  } finally {
    isExporting.value = false;
  }
}

</script>


<template>
  <div class="min-h-screen bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all">
    <!-- Navbar -->
    <nav class="w-full bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur border-b border-gray-200 dark:border-gray-800 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <Button
            variant="ghost"
            @click="handleGoBack"
            class="flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-neutral-800"
          >
            <CurvexIcon class="h-6 w-6" />
            <span class="text-lg font-bold">Curvex</span>
          </Button>
          <ThemeToggle />
        </div>
      </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Encabezado -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 flex items-center justify-center gap-3">
          <GitGraph class="h-8 w-8 text-primary" /> Resultados del Análisis
        </h1>
        <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
          Aquí están los valores calculados según tus datos.
        </p>
        
        <!-- Botón de exportar -->
        <div class="mt-4">
          <Button
            variant="default"
            @click="exportToExcel"
            :disabled="isExporting"
            class="flex items-center gap-2 mx-auto"
          >
            <Loader2 v-if="isExporting" class="h-5 w-5 animate-spin" />
            <FileSpreadsheet v-else class="h-5 w-5" />
            {{ isExporting ? 'Exportando...' : 'Exportar datos a Excel' }}
          </Button>
        </div>
      </div>

      <!-- Card Principal -->
      <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 md:p-8">
        <!-- Controles -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
          <!-- Control de decimales -->
          <div class="flex items-center justify-between">
            <Label for="decimales" class="text-base font-medium text-gray-700 dark:text-gray-300">
              Número de decimales
            </Label>
            <Input
              v-model.number="decimales"
              id="decimales"
              type="number"
              min="1"
              max="8"
              class="w-24 text-center"
            />
          </div>

          <!-- Control de color del gráfico -->
          <div class="flex items-center justify-between">
            <Label for="chartColor" class="text-base font-medium text-gray-700 dark:text-gray-300">
              Color del gráfico
            </Label>
            <div class="flex items-center gap-2">
              <Input
                v-model="chartColor"
                id="chartColor"
                type="color"
                class="w-20 h-10 cursor-pointer"
              />
              <span class="text-sm text-gray-500 dark:text-gray-400 font-mono">{{ chartColor }}</span>
            </div>
          </div>
        </div>

        <!-- Grid de resultados -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <!-- Tarjetas estadísticas -->
          <TooltipProvider>
            <Tooltip v-for="(item, nombre) in [
              { label: 'Suma', value: suma, tooltip: 'Suma total de todos los valores en el conjunto de datos.' },
              { label: 'Promedio', value: promedio, tooltip: 'Valor medio aritmético. Se calcula sumando todos los valores y dividiendo entre la cantidad total.' },
              { label: 'Valor Mínimo', value: minimo, tooltip: 'El valor más pequeño encontrado en el conjunto de datos.' },
              { label: 'Valor Máximo', value: maximo, tooltip: 'El valor más grande encontrado en el conjunto de datos.' },
              { label: 'Rango', value: rango, tooltip: 'Diferencia entre el valor máximo y el mínimo. Indica la dispersión de los datos.' },
              { label: 'Varianza', value: varianza, tooltip: 'Medida de dispersión que indica qué tan alejados están los datos del promedio. Varianza poblacional.' },
              { label: 'Desviación Estándar', value: desviacionEstandar, tooltip: 'Raíz cuadrada de la varianza. Indica la dispersión promedio de los datos respecto a la media.' },
              { label: 'Curtosis', value: curtosis, tooltip: 'Mide el grado de concentración de los datos alrededor de la media. Curtosis > 0 indica distribución leptocúrtica (pico alto), < 0 platicúrtica (pico bajo).' }
            ]" :key="nombre">
              <TooltipTrigger as-child>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-neutral-800 dark:to-neutral-900 rounded-xl p-4 text-center shadow-sm hover:shadow-md transition-shadow cursor-help">
                  <div class="flex items-center justify-center gap-1 mb-1">
                    <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400">{{ item.label }}</p>
                    <Info class="h-3 w-3 text-gray-400 dark:text-gray-500" />
                  </div>
                  <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ item.value }}</p>
                </div>
              </TooltipTrigger>
              <TooltipContent class="max-w-xs">
                <p class="text-sm">{{ item.tooltip }}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>

        <!-- Histograma -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                <GitGraph class="h-6 w-6 text-primary" /> Histograma de Frecuencias
              </h3>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Info class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" />
                  </TooltipTrigger>
                  <TooltipContent class="max-w-sm">
                    <p class="text-sm">El histograma muestra la distribución de frecuencias de los datos agrupados en intervalos. La altura de cada barra representa cuántos valores caen en ese rango. Útil para identificar patrones y la forma de la distribución.</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>
            <Button
              variant="outline"
              size="sm"
              @click="downloadHistogram"
              :disabled="isDownloading"
              class="flex items-center gap-2"
            >
              <Loader2 v-if="isDownloading" class="h-4 w-4 animate-spin" />
              <Download v-else class="h-4 w-4" />
              {{ isDownloading ? 'Descargando...' : 'Descargar' }}
            </Button>
          </div>
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-neutral-800 dark:to-neutral-900 rounded-xl p-6 h-96 histogram-chart">
            <BarChart :chartData="histogramData" :options="histogramOptions" class="w-full h-full" />
          </div>
        </div>

        <!-- Tabla de Frecuencias -->
        <div v-if="resultado.frequency_table" class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
          <div class="mb-4 flex items-center gap-2">
            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <Table2 class="h-6 w-6 text-primary" /> Tabla de Frecuencias
            </h3>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" />
                </TooltipTrigger>
                <TooltipContent class="max-w-sm">
                  <p class="text-sm">La tabla de frecuencias organiza los datos en intervalos (clases) y muestra cuántos valores caen en cada uno. Incluye frecuencias absolutas, acumuladas, relativas y relativas acumuladas en porcentaje.</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
            
            <!-- Info de intervalos -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-4">
              <p class="text-base"><strong>Número de intervalos:</strong> {{ resultado.frequency_table.info_intervalos.numero_intervalos }}</p>
              <p class="text-base"><strong>Ancho del intervalo:</strong> {{ resultado.frequency_table.info_intervalos.ancho_intervalo }}</p>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto bg-gradient-to-br from-gray-50 to-gray-100 dark:from-neutral-800 dark:to-neutral-900 rounded-xl shadow-md">
              <table class="w-full">
                <thead class="bg-gray-200 dark:bg-neutral-700">
                  <tr>
                    <th class="px-4 py-3 text-left font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center gap-1">
                              Clase
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Identificador del intervalo</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Lím. Inf.
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Límite inferior del intervalo</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Lím. Sup.
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Límite superior del intervalo</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Marca
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Punto medio del intervalo (promedio entre límites)</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Frec. Abs.
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Cantidad de datos en este intervalo</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Frec. Acum.
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Suma acumulada de frecuencias hasta este intervalo</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Frec. Rel. %
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Porcentaje que representa este intervalo del total</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                    <th class="px-4 py-3 text-right font-semibold">
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger as-child>
                            <span class="cursor-help flex items-center justify-end gap-1">
                              Frec. Rel. Acum. %
                              <Info class="h-3 w-3 text-gray-500" />
                            </span>
                          </TooltipTrigger>
                          <TooltipContent><p class="text-xs">Porcentaje acumulado hasta este intervalo</p></TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="(row, index) in resultado.frequency_table.tabla_frecuencias" 
                    :key="index"
                    class="border-b border-gray-200 dark:border-neutral-700 hover:bg-gray-100 dark:hover:bg-neutral-700/50 transition-colors"
                  >
                    <td class="px-4 py-3 font-medium">{{ row.clase }}</td>
                    <td class="px-4 py-3 text-right">{{ row.limite_inferior }}</td>
                    <td class="px-4 py-3 text-right">{{ row.limite_superior }}</td>
                    <td class="px-4 py-3 text-right">{{ row.marca_de_clase }}</td>
                    <td class="px-4 py-3 text-right">{{ row.frecuencia_absoluta }}</td>
                    <td class="px-4 py-3 text-right">{{ row.frecuencia_abs_acumulada }}</td>
                    <td class="px-4 py-3 text-right">{{ row.frecuencia_relativa_pct }}%</td>
                    <td class="px-4 py-3 text-right">{{ row.frecuencia_rel_acumulada_pct }}%</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        <!-- Cuartiles, Deciles y Percentiles -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
          <div class="flex items-center gap-2 mb-6">
            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
              Medidas de Posición
            </h3>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-help" />
                </TooltipTrigger>
                <TooltipContent class="max-w-sm">
                  <p class="text-sm">Las medidas de posición dividen el conjunto de datos en partes iguales. Ayudan a entender cómo se distribuyen los valores y a identificar percentiles específicos.</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>

          <!-- Cuartiles -->
          <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
              <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <span class="text-blue-500">📊</span> Cuartiles
              </h4>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Info class="h-4 w-4 text-blue-400 hover:text-blue-600 dark:hover:text-blue-300 cursor-help" />
                  </TooltipTrigger>
                  <TooltipContent class="max-w-xs">
                    <p class="text-sm">Los cuartiles dividen los datos en 4 partes iguales. Q1 (25%) es el valor por debajo del cual está el 25% de los datos, Q2 (50%) es la mediana, y Q3 (75%) deja el 75% de los datos por debajo.</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div 
                v-for="(cuartil, index) in resultado.cuartiles" 
                :key="index"
                class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 text-center shadow-sm hover:shadow-md transition-shadow"
              >
                <p class="text-xs uppercase font-semibold text-blue-700 dark:text-blue-400 mb-1">
                  Q{{ Object.keys(cuartil)[0] }} ({{ (Number(Object.keys(cuartil)[0]) / 4 * 100).toFixed(0) }}%)
                </p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">
                  {{ truncate(Object.values(cuartil)[0], Number(decimales)) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Deciles -->
          <div class="mb-6">
            <div class="flex items-center gap-2 mb-3">
              <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <span class="text-green-500">📈</span> Deciles
              </h4>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Info class="h-4 w-4 text-green-400 hover:text-green-600 dark:hover:text-green-300 cursor-help" />
                  </TooltipTrigger>
                  <TooltipContent class="max-w-xs">
                    <p class="text-sm">Los deciles dividen los datos en 10 partes iguales. D1 (10%) es el valor por debajo del cual está el 10% de los datos, D5 (50%) es la mediana, y así sucesivamente hasta D9 (90%).</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-9 gap-3">
              <div 
                v-for="(decil, index) in resultado.deciles" 
                :key="index"
                class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-3 text-center shadow-sm hover:shadow-md transition-shadow"
              >
                <p class="text-xs uppercase font-semibold text-green-700 dark:text-green-400 mb-1">
                  D{{ Object.keys(decil)[0] }}
                </p>
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                  {{ truncate(Object.values(decil)[0], Number(decimales)) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Percentiles -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-2">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                  <span class="text-purple-500">📉</span> Percentiles
                </h4>
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Info class="h-4 w-4 text-purple-400 hover:text-purple-600 dark:hover:text-purple-300 cursor-help" />
                    </TooltipTrigger>
                    <TooltipContent class="max-w-xs">
                      <p class="text-sm">Los percentiles dividen los datos en 100 partes iguales. P25 indica que el 25% de los datos está por debajo de ese valor. Los percentiles son útiles para identificar posiciones específicas en la distribución.</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
              </div>
              <Button
                variant="ghost"
                size="sm"
                @click="showAllPercentiles = !showAllPercentiles"
                class="text-xs"
              >
                {{ showAllPercentiles ? 'Mostrar menos' : 'Ver todos (99)' }}
                <ChevronDown v-if="!showAllPercentiles" class="ml-1 h-3 w-3" />
                <ChevronUp v-else class="ml-1 h-3 w-3" />
              </Button>
            </div>
            
            <!-- Percentiles clave (cada 5) -->
            <div v-if="!showAllPercentiles" class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-4 shadow-md">
              <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-10 gap-3">
                <div 
                  v-for="(percentil, index) in keyPercentiles" 
                  :key="index"
                  class="bg-white/60 dark:bg-gray-900/40 rounded-lg p-3 text-center hover:bg-white/90 dark:hover:bg-gray-900/60 transition-colors shadow-sm"
                >
                  <p class="text-xs uppercase font-semibold text-purple-700 dark:text-purple-400 mb-1">
                    P{{ Object.keys(percentil)[0] }}
                  </p>
                  <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                    {{ truncate(Object.values(percentil)[0], Number(decimales)) }}
                  </p>
                </div>
              </div>
              <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-3">
                Mostrando percentiles cada 5 unidades (P5, P10, P15, ..., P95)
              </p>
            </div>

            <!-- Todos los percentiles (tabla compacta) -->
            <div v-else class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-4 shadow-md">
              <div class="overflow-x-auto">
                <table class="w-full text-xs">
                  <thead class="bg-purple-200/50 dark:bg-purple-800/30">
                    <tr>
                      <th class="px-2 py-2 text-left font-semibold text-purple-900 dark:text-purple-200">P</th>
                      <th v-for="n in 10" :key="n" class="px-2 py-2 text-right font-semibold text-purple-900 dark:text-purple-200">
                        {{ n - 1 }}
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in 10" :key="row" class="border-t border-purple-200/50 dark:border-purple-800/30">
                      <td class="px-2 py-2 font-semibold text-purple-700 dark:text-purple-400">
                        {{ (row - 1) * 10 }}
                      </td>
                      <td v-for="col in 10" :key="col" class="px-2 py-2 text-right text-gray-900 dark:text-gray-100">
                        <template v-if="getPercentileValue((row - 1) * 10 + col - 1) !== null">
                          {{ truncate(getPercentileValue((row - 1) * 10 + col - 1)!, Number(decimales)) }}
                        </template>
                        <span v-else class="text-gray-400">-</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-3">
                Tabla completa: filas representan decenas (0, 10, 20...), columnas representan unidades (0-9)
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <FooterComp />
  </div>
</template>