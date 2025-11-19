<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from '@/lib/axios';

// Componentes
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import UploadFile from '@/icons/UploadFile.vue';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/ui/tooltip";
import { Info } from "lucide-vue-next";
import FooterComp from '@/components/FooterComp.vue';
import Textarea from '@/components/ui/textarea/Textarea.vue';

// Tipos
import type { Resultado } from '@/types/Resultado';
import Resultados from '@/components/Resultados.vue';

const resultados = ref<Resultado | null>(null);
const showResults = ref(false);

const mode = ref<'file' | 'text'>('file');
const modo_varianza = ref<'0' | '1'>('0');
const csvFile = ref<File | null>(null);
const text = ref('');
const loading = ref(false);
const errorMessage = ref('');

const hasInput = computed(() => {
  return mode.value === 'file' ? !!csvFile.value : text.value.trim().length > 0;
});

function analyze() {
  if (!hasInput.value) return;

  errorMessage.value = ''; // Reinicia el mensaje anterior

  // Validación local si el modo es texto
  if (mode.value === 'text') {
    const input = text.value.trim();
    const validPattern = /^-?\d+(\.\d+)?(\s+-?\d+(\.\d+)?)*$/;
    if (!validPattern.test(input)) {
      errorMessage.value = 'Solo se permiten números separados por espacios.';
      return;
    }
  }

  loading.value = true;

  const formData = new FormData();
  if (mode.value === 'file' && csvFile.value) {
    formData.append('file', csvFile.value);
  } else {
    formData.append('values', text.value);
  }
  if(modo_varianza.value === '1'){
    formData.append('varianza', '1');
  } else {
    formData.append('varianza', '0');
  }
  axios.post('/calculate-statistics', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  .then((response) => {
    const data = response.data;

    // Mapeo actualizado para incluir todos los campos
    resultados.value = {
      count: data.count,
      sum: data.sum,
      mean: data.mean,
      min: data.min,
      max: data.max,
      range: data.range,
      variance: data.variance,
      standard_deviation: data.standard_deviation,
      kurtosis: data.kurtosis,
      cuartiles: data.cuartiles || [],
      deciles: data.deciles || [],
      percentiles: data.percentiles || [],
      data: data.data || [],
      frequency_table: data.frequency_table || undefined
    };

    showResults.value = true;
    loading.value = false;
  })
  .catch((error) => {
    loading.value = false;
    console.error('Error:', error);

    if (error.response && error.response.data) {
      const errors = error.response.data.errors;
      errorMessage.value =
        errors?.values?.[0] ||
        errors?.file?.[0] ||
        error.response.data.message ||
        'Error al procesar los datos';
    } else {
      errorMessage.value = 'Error al conectar con el servidor';
    }
  });
}

function openCsvPicker() {
  csvInputRef.value?.click();
}

// Drag & Drop CSV
const csvInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);

const csvFileName = computed(() => (csvFile.value ? csvFile.value.name : ''));

function handleCsvDragOver(e: DragEvent) {
  e.preventDefault();
  isDragging.value = true;
}

function handleCsvDragLeave(e: DragEvent) {
  e.preventDefault();
  isDragging.value = false;
}

function handleCsvDrop(e: DragEvent) {
  e.preventDefault();
  isDragging.value = false;
  const f = e.dataTransfer?.files && e.dataTransfer.files[0];
  if (f) csvFile.value = f;
}

function handleCsvFileChange(e: Event) {
  const target = e.target as HTMLInputElement;
  const f = target.files && target.files[0];
  csvFile.value = f ?? null;
}

function handleGoBack() {
  showResults.value = false;
  resultados.value = null;
  errorMessage.value = '';
  // Limpiar inputs
  csvFile.value = null;
  text.value = '';
}
</script>

<template>
  <Head title="Curvex" />

  <!-- Vista de Resultados -->
  <Resultados 
    v-if="showResults && resultados" 
    :resultado="resultados" 
    @go-back="handleGoBack" 
  />

  <!-- Vista Principal -->
  <div v-else class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-4 sm:p-6">
    <!-- Navbar -->
    <nav class="w-full max-w-5xl flex items-center justify-between mb-6 sm:mb-8 px-2 sm:px-4">
      <div class="flex items-center gap-2 sm:gap-3">
        <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10" />
        <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
      </div>
      <ThemeToggle />
    </nav>

    <!-- Hero -->
    <section class="text-center mb-6 sm:mb-8 pt-4 sm:pt-8 px-4">
      <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold leading-tight">Curvex</h1>
      <h2 class="mt-3 sm:mt-4 text-lg sm:text-2xl md:text-3xl font-semibold text-gray-700 dark:text-gray-200 max-w-3xl mx-auto px-2">
        Analiza tus datos, calcula estadísticas y visualiza resultados de forma sencilla
      </h2>
      <p class="mt-2 sm:mt-3 text-base sm:text-lg text-gray-500 dark:text-gray-400 px-2">
        Arrastra y suelta tus archivos CSV o escribe tu matriz de datos directamente para comenzar.
      </p>
      <Link href="/correccionvarianza">
        <Button class="mt-4 w-full text-sm sm:text-base py-5 sm:py-6 h-auto">
              Si quieres corregir tus datos de varianza a partir de una muestra, da click aqui.
        </Button>
      </Link>
    </section>

    <!-- Card de controles -->
    <div class="w-full max-w-4xl bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-lg shadow-md p-4 sm:p-6 mx-auto">
      <h2 class="text-xl sm:text-2xl mb-4">Importar datos</h2>

      <div class="grid gap-4">
      <div class="w-full min-w-0 space-y-2">
        <label class="block text-sm font-medium">Tipo de varianza</label>
        <Select v-model="modo_varianza">
          <SelectTrigger class="w-full min-w-0"><SelectValue placeholder="Selecciona el modo de cálculo de varianza" /></SelectTrigger>
          <SelectContent class="w-full max-w-[calc(100vw-2rem)]">
            <SelectGroup>
              <SelectLabel>Varianzas</SelectLabel>
              <SelectItem value='0'>
                <div class="flex flex-col py-1">
                  <span class="font-medium text-sm">Varianza poblacional</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400"> : Apta para la población total en un conjunto de datos.</span>
                </div>
              </SelectItem>
              <SelectItem value='1'>
                <div class="flex flex-col py-1">
                  <span class="font-medium text-sm">Varianza muestral</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400"> : Apta para una muestra de la población total en un conjunto de datos.</span>
                </div>
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
      </div>
      
      <div class="w-full min-w-0 space-y-2">
        <label class="block text-sm font-medium">Tipo de entrada</label>
        <Select v-model="mode">
          <SelectTrigger class="w-full min-w-0"><SelectValue placeholder="Selecciona el modo de entrada" /></SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Modos</SelectLabel>
              <SelectItem value="text">Texto</SelectItem>
              <SelectItem value="file">Archivo CSV</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
      </div>

        <!-- Entrada por archivo -->
        <div v-if="mode === 'file'" class="mt-2 w-full">
          <h3 class="text-base sm:text-lg font-semibold">Seleccionar archivo CSV</h3>
          <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 mb-3">
            Arrastra tu archivo <strong>.csv</strong> aquí o haz clic para seleccionarlo.
          </p>

          <div
            class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg py-8 sm:py-10 px-4 cursor-pointer transition-colors w-full"
            :class="isDragging ? 'border-[#9a9563] bg-yellow-50/30 dark:bg-yellow-900/10' : 'border-gray-300 dark:border-gray-600 hover:border-[#9a9563]'"
            @dragover.prevent="handleCsvDragOver"
            @dragleave.prevent="handleCsvDragLeave"
            @drop.prevent="handleCsvDrop"
            @click="openCsvPicker"
          >
            <UploadFile class="w-12 h-12 sm:w-16 sm:h-16" />
            <span v-if="!csvFileName" class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-2 text-center">Suelta tu archivo aquí</span>
            <span v-else class="text-sm sm:text-base text-green-600 dark:text-green-400 font-medium mt-2 text-center break-all px-2 max-w-full">{{ csvFileName }}</span>
            <input ref="csvInputRef" type="file" accept=".csv,text/csv" class="hidden" @change="handleCsvFileChange" />
          </div>

          <Button @click="analyze" :disabled="loading || !csvFile" class="mt-4 w-full text-sm sm:text-base py-5 sm:py-6 h-auto">
            {{ loading ? 'Procesando...' : 'Subir y Analizar' }}
          </Button>

          <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-red-700 dark:text-red-400 text-xs sm:text-sm">
            {{ errorMessage }}
          </div>
        </div>

        <!-- Entrada por texto -->
        <div v-else class="mt-2 w-full">
          <label class="block text-sm font-medium mb-2">
            Pega o escribe tus datos
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                </TooltipTrigger>
                <TooltipContent><p>Escribe tus números separados por espacios</p></TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </label>

          <Textarea
            v-model="text"
            rows="8"
            placeholder="Ejemplo: 10 15.5 20 30"
            class="w-full rounded-md border px-3 py-2 bg-transparent 
                   placeholder:text-gray-500 dark:placeholder:text-gray-400
                   text-sm sm:text-base
                   text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                   resize-none"
          ></Textarea>

          <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-red-700 dark:text-red-400 text-xs sm:text-sm">
            {{ errorMessage }}
          </div>

          <Button @click="analyze" :disabled="loading || !text.trim()" class="mt-4 w-full text-sm sm:text-base py-5 sm:py-6 h-auto">
            {{ loading ? 'Procesando...' : 'Analizar' }}
          </Button>
        </div>
      </div>
    </div>

    <FooterComp />
  </div>
</template>
