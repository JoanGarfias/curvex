<script setup lang="ts">
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

import { GitGraph, Table2 } from "lucide-vue-next"
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
const desviacionEstandar = computed(() => truncate(resultado.value.standardDeviation, Number(decimales.value)));
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
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
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
      backgroundColor: 'rgba(75, 192, 192, 0.6)',
      borderColor: 'rgba(75, 192, 192, 1)',
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
      </div>

      <!-- Card Principal -->
      <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 md:p-8">
        <!-- Control de decimales -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
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

        <!-- Grid de resultados -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <!-- Tarjetas estadísticas -->
          <div v-for="(valor, nombre) in {
            Suma: suma,
            Promedio: promedio,
            'Valor Mínimo': minimo,
            'Valor Máximo': maximo,
            Rango: rango,
            Varianza: varianza,
            'Desviación Estándar': desviacionEstandar,
            Curtosis: curtosis
          }" :key="nombre"
            class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-neutral-800 dark:to-neutral-900 rounded-xl p-4 text-center shadow-sm hover:shadow-md transition-shadow">
            <p class="text-xs uppercase font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ nombre }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ valor }}</p>
          </div>
        </div>

        <!-- Histograma -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
          <div class="mb-4">
            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <GitGraph class="h-6 w-6 text-primary" /> Histograma de Frecuencias
            </h3>
          </div>
          <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-neutral-800 dark:to-neutral-900 rounded-xl p-6 h-96">
            <BarChart :chartData="histogramData" :options="histogramOptions" class="w-full h-full" />
          </div>
        </div>

        <!-- Tabla de Frecuencias -->
        <div v-if="resultado.frequency_table" class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
          <div class="mb-4">
            <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <Table2 class="h-6 w-6 text-primary" /> Tabla de Frecuencias
            </h3>
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
                    <th class="px-4 py-3 text-left font-semibold">Clase</th>
                    <th class="px-4 py-3 text-right font-semibold">Lím. Inf.</th>
                    <th class="px-4 py-3 text-right font-semibold">Lím. Sup.</th>
                    <th class="px-4 py-3 text-right font-semibold">Marca</th>
                    <th class="px-4 py-3 text-right font-semibold">Frec. Abs.</th>
                    <th class="px-4 py-3 text-right font-semibold">Frec. Acum.</th>
                    <th class="px-4 py-3 text-right font-semibold">Frec. Rel. %</th>
                    <th class="px-4 py-3 text-right font-semibold">Frec. Rel. Acum. %</th>
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
      </div>
    </div>

    <FooterComp />
  </div>
</template>