<script setup lang="ts">
//import axios from '@/lib/axios';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"
import { GitGraph } from "lucide-vue-next"

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
  close: []
}>();

const open = ref<boolean>(true);
const decimales = ref<number>(8);
const errorMessage = ref('');
const loading = ref(false);
const showHistogram = ref(false);

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

function handleClose() {
  open.value = false;
  emit('close');
}

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

</script>


<template>
   <Dialog v-model:open="open" @update:open="(val) => !val && handleClose()">
  <DialogContent
  class="sm:max-w-[500px] bg-white dark:bg-neutral-900 shadow-xl border border-gray-200 dark:border-gray-700 rounded-2xl p-0 overflow-hidden transition-all duration-300"
>
  <div class="p-6 flex flex-col max-h-[90vh]">
    <!-- Encabezado -->
    <DialogHeader class="text-center space-y-1 mb-4">
      <DialogTitle class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center justify-center gap-2">
        <GitGraph class="h-6 w-6 text-primary" /> Resultados del Análisis
      </DialogTitle>
      <DialogDescription class="text-gray-600 dark:text-gray-400">
        Aquí están los valores calculados según tus datos.
      </DialogDescription>
    </DialogHeader>

    <!-- Contenido scrollable -->
    <div class="flex-1 overflow-y-auto pr-2">
      <div class="flex items-center justify-between mb-4">
        <Label for="decimales" class="text-sm text-gray-700 dark:text-gray-300">Número de decimales</Label>
        <Input
          v-model.number="decimales"
          id="decimales"
          type="number"
          min="1"
          max="8"
          class="w-20 text-center"
        />
      </div>

      <!-- Grid de resultados -->
      <div class="grid grid-cols-2 gap-3 mt-2">
        <!-- Tarjetas estadísticas -->
        <div v-for="(valor, nombre) in {
          suma: suma,
          Promedio: promedio,
          'Valor Mínimo': minimo,
          'Valor Máximo': maximo,
          Rango: rango,
          Varianza: varianza,
          'Desviación Estándar': desviacionEstandar,
          Curtosis: curtosis
        }" :key="nombre"
          class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-3 text-center shadow-sm col-span-1"
          :class="'col-span-1'">
          <p class="text-xs uppercase text-gray-500 dark:text-gray-400">{{ nombre }}</p>
          <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ valor }}</p>
        </div>
      </div>

      <!-- Histograma -->
      <transition name="fade">
        <div v-if="showHistogram" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold text-primary flex items-center gap-2">
              <GitGraph class="h-5 w-5" /> Histograma de Frecuencias
            </h3>
            <Button variant="ghost" size="sm" @click="showHistogram = false">✖</Button>
          </div>
          <div class="bg-gray-50 dark:bg-neutral-800 rounded-xl p-4 max-h-86">
            <BarChart :chartData="histogramData" :options="histogramOptions" class="w-full h-full" />
          </div>
        </div>
      </transition>
    </div>

    <!-- Footer fijo -->
    <DialogFooter class="mt-4">
      <div class="w-full space-y-3">
        <Button
          type="button"
          variant="default"
          class="w-full flex items-center justify-center gap-2 font-semibold"
          @click="showHistogram = !showHistogram"
          :disabled="!datos || datos.length === 0"
        >
          <GitGraph class="mr-2 h-4 w-4" />
          {{ showHistogram ? 'Ocultar histograma' : 'Ver histograma' }}
        </Button>

        <div
          v-if="errorMessage"
          class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-red-700 dark:text-red-300 text-sm"
        >
          {{ errorMessage }}
        </div>
      </div>
    </DialogFooter>
  </div>
</DialogContent>
</Dialog>

</template>