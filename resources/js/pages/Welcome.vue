<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from '@/lib/axios';

// Componentes
import { Head } from '@inertiajs/vue3';
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

const mode = ref<'file' | 'text'>('text');
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
  <div v-else class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-6">
    <!-- Navbar -->
    <nav class="w-full max-w-5xl flex items-center justify-between mb-8 px-4">
      <div class="flex items-center gap-3">
        <CurvexIcon />
        <span class="text-2xl font-extrabold tracking-tight">Curvex</span>
      </div>
      <ThemeToggle />
    </nav>

    <!-- Hero -->
    <section class="text-center mb-8 pt-8 px-4">
      <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-tight">Curvex</h1>
      <h2 class="mt-4 text-2xl sm:text-3xl font-semibold text-gray-700 dark:text-gray-200 max-w-3xl mx-auto">
        Analiza tus datos, calcula estadísticas y visualiza resultados de forma sencilla
      </h2>
      <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
        Arrastra y suelta tus archivos CSV o escribe tu matriz de datos directamente para comenzar.
      </p>
    </section>

    <!-- Card de controles -->
    <div class="w-full max-w-4xl bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-lg shadow-md p-6 mx-auto">
      <h2 class="text-2xl mb-4">Importar datos</h2>

      <div class="grid gap-4">
        <label class="block text-sm font-medium">Tipo de entrada</label>
        <Select v-model="mode" class="w-full sm:w-64">
          <SelectTrigger><SelectValue placeholder="Selecciona el modo de entrada" /></SelectTrigger>
          <SelectContent>
            <SelectGroup>
              <SelectLabel>Modos</SelectLabel>
              <SelectItem value="text">Texto</SelectItem>
              <SelectItem value="file">Archivo CSV</SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>

        <!-- Entrada por archivo -->
        <div v-if="mode === 'file'" class="mt-2 w-full">
          <h3 class="text-lg font-semibold">Seleccionar archivo CSV</h3>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
            Arrastra tu archivo <strong>.csv</strong> aquí o haz clic para seleccionarlo.
          </p>

          <div
            class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg py-10 cursor-pointer transition-colors w-full"
            :class="isDragging ? 'border-[#9a9563] bg-yellow-50/30 dark:bg-yellow-900/10' : 'border-gray-300 hover:border-[#9a9563]'"
            @dragover.prevent="handleCsvDragOver"
            @dragleave.prevent="handleCsvDragLeave"
            @drop.prevent="handleCsvDrop"
            @click="openCsvPicker"
          >
            <UploadFile />
            <span v-if="!csvFileName" class="text-gray-600">Suelta tu archivo aquí</span>
            <span v-else class="text-green-600 font-medium">{{ csvFileName }}</span>
            <input ref="csvInputRef" type="file" accept=".csv,text/csv" class="hidden" @change="handleCsvFileChange" />
          </div>

          <Button @click="analyze" :disabled="loading || !csvFile" class="mt-4 w-full">
            {{ loading ? 'Procesando...' : 'Subir y Analizar' }}
          </Button>

          <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 rounded text-red-700 text-sm">
            {{ errorMessage }}
          </div>
        </div>

        <!-- Entrada por texto -->
        <div v-else class="mt-2">
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
                   text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]"
          ></Textarea>

          <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 rounded text-red-700 text-sm">
            {{ errorMessage }}
          </div>

          <Button @click="analyze" :disabled="loading || !text.trim()" class="mt-4 w-full">
            {{ loading ? 'Procesando...' : 'Analizar' }}
          </Button>
        </div>
      </div>
    </div>

    <FooterComp />
  </div>
</template>
