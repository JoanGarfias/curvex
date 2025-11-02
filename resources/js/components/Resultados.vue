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
    <DialogContent :class="showHistogram ? 'sm:max-w-[600px]' : 'sm:max-w-[425px]'" class="max-h-[90vh] flex flex-col">
      <DialogHeader>
        <DialogTitle>Resultados</DialogTitle>
        <DialogDescription>
          Aquí están los resultados de su análisis.
        </DialogDescription>
      </DialogHeader>

      <!-- Contenido con scroll -->
      <div class="overflow-y-auto flex-1 pr-2">
        <div class="flex flex-row align-center justify-between">
          <Label for="decimales">Número de decimales</Label>
          <Input v-model.number="decimales" class="w-16" id="decimales" type="number" label="Número de decimales" min="1" max="8" />
        </div>

        <div class="grid gap-4 py-4">
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="promedio">Promedio</Label>
            <Input id="promedio" type="text" :value="promedio" :defaultValue="promedio" readonly />
          </div>
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="valmin">Valor Mínimo</Label>
            <Input id="valmin" type="text" :value="minimo" :defaultValue="minimo" readonly />
          </div>
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="valmax">Valor Máximo</Label>
            <Input id="valmax" type="text" :value="maximo" :defaultValue="maximo" readonly />
          </div>
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="rango">Rango</Label>
            <Input id="rango" type="text" :value="rango" :defaultValue="rango" readonly />
          </div>
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="varianza">Varianza</Label>
            <Input id="varianza" type="text" :value="varianza" :defaultValue="varianza" readonly />
          </div>
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="desviacionEstandar">Desviación Estándar</Label>
            <Input id="desviacionEstandar" type="text" :value="desviacionEstandar" :defaultValue="desviacionEstandar" readonly />
          </div>
          <div class="grid grid-cols-2 items-center gap-4">
            <Label for="curtosis">Curtosis</Label>
            <Input id="curtosis" type="text" :value="curtosis" :defaultValue="curtosis" readonly />
          </div>
        </div>
        
        <!-- Sección del histograma -->
        <div v-if="showHistogram" class="mt-4">
          <div class="border-t pt-4">
            <h3 class="text-lg font-semibold mb-3">Histograma de Frecuencias</h3>
            <div class="w-full h-64">
              <BarChart 
                :chartData="histogramData" 
                :options="histogramOptions" 
              />
            </div>
          </div>
        </div>
      </div>

      <DialogFooter>
        <Button 
          type="button" 
          variant="outline" 
          class="w-full" 
          @click="showHistogram = !showHistogram"
          :disabled="!datos || datos.length === 0"
        >
          <GitGraph class="mr-2 h-4 w-4" /> 
          {{ showHistogram ? 'Ocultar histograma' : 'Ver histograma' }}
        </Button>
        
        <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-red-700 dark:text-red-300 text-sm">
          {{ errorMessage }}
        </div>

        <!-- Barra de progreso -->
        <div v-if="loading" class="mt-3 w-full">
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded h-3 overflow-hidden">
            <div class="h-full bg-primary transition-all animate-pulse"></div>
          </div>
          <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-1">Procesando gráfica...</p>
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>

</template>