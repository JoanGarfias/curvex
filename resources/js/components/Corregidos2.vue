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
import type { Resultado } from '@/types/corregido2';



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
const limite = computed(() => truncate(resultado.value.limite, Number(decimales.value)));
const varianza = computed(() => truncate(resultado.value.variance, Number(decimales.value)));
const alpha = computed(() => truncate(resultado.value.alpha, Number(decimales.value)));
const valor_critico = computed(() => truncate(resultado.value.valor_critico, Number(decimales.value)));
const varianzacorregida = computed(() => truncate(resultado.value.variance2, Number(decimales.value)));
const desviacion = computed(() => truncate(resultado.value.desviacion2, Number(decimales.value)));
const promedio = computed(() => truncate(resultado.value.promedio, Number(decimales.value)));
console.log('Datos recibidos:', resultado);



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
        </div>

        <!-- Grid de resultados -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <!-- Tarjetas estadísticas -->
          <TooltipProvider>
            <Tooltip v-for="(item, nombre) in [
              { 
    label: 'Varianza original', 
    value: varianza, 
    tooltip: 'Varianza muestral (S²) calculada de la muestra de tamaño corregido. Es un estimador usado cuando se trabaja con una muestra y no con toda la población.' 
  },
  { 
    label: 'Varianza corregida', 
    value: varianzacorregida, 
    tooltip: 'Valor nuevo de la varianza muestral (S²) obtenido a partir de la corrección del tamaño indicado.' 
  },
  { 
    label: 'Desviación corregida', 
    value: desviacion, 
    tooltip: 'Desviación estándar muestral (S) obtenida de la varianza corregida. Indica cuánto se alejan los datos del promedio en las mismas unidades de medida que los datos originales.' 
  },
  { 
    label: 'Alpha (α)', 
    value: alpha, 
    tooltip: 'Nivel de significancia estadística utilizado para calcular los intervalos de confianza.' 
  },
  { 
    label: 'Valor crítico', 
    value: valor_critico, 
    tooltip: 'Punto de corte en la distribución estadística (T) necesario para construir el intervalo de confianza.' 
  },
  { 
    label: 'Límite', 
    value: limite, 
    tooltip: 'Frontera del intervalo calculado (Superior o Inferior). Indica el rango dentro del cual se espera encontrar la mayor cantidad de datos de la muestra: ' + promedio + ' ± ' + limite 
  }
              
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
      </div>
    </div>
    </div>

    <FooterComp />
</template>