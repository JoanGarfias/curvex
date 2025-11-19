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
import type { Resultado } from '@/types/corregido';



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
const varianza = computed(() => truncate(resultado.value.variance, Number(decimales.value)));
const alpha = computed(() => truncate(resultado.value.alpha, Number(decimales.value)));
const valor_critico = computed(() => truncate(resultado.value.valor_critico, Number(decimales.value)));
const h = computed(() => truncate(resultado.value.h, Number(decimales.value)));
const hreal = computed(() => truncate(resultado.value.hreal, Number(decimales.value)));
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
    label: 'Promedio', 
    value: promedio, 
    tooltip: 'Media aritmética. Es el valor central calculado sumando todos los datos y dividiendo entre el total.' 
  },
  { 
    label: 'Varianza', 
    value: varianza, 
    tooltip: 'Medida de dispersión que representa la variabilidad promedio de los datos respecto a la media, elevada al cuadrado.' 
  },
  { 
    label: 'Alpha (α)', 
    value: alpha, 
    tooltip: 'Nivel de significancia. Representa la probabilidad de error aceptada (ej. 0.05 equivale a un 5% de riesgo o 95% de confianza).' 
  },
  { 
    label: 'Distribución normal (Z)', 
    value: valor_critico, 
    tooltip: 'Valor límite en la distribución normal estándar que separa la zona de aceptación de la zona de rechazo según el Alpha elegido.' 
  },
  { 
    label: 'Estimación de población (n)', 
    value: h, 
    tooltip: 'Tamaño estimado de la población obtenido a partir de los parámetros introducidos.' 
  },
  { 
    label: 'Muestra corregida (n)', 
    value: hreal, 
    tooltip: 'Tamaño final de la muestra necesario. Es la cantidad de datos sugerida ajustando por el tamaño de la población, y la confiabilidad y porcentaje de error introducidos.' 
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