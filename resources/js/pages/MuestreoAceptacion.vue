<template>
  <Head title="Muestreo de Aceptación" />
  
  <MainLayout :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="text-center mb-6 sm:mb-8 pt-4 sm:pt-8">
      <div class="flex items-center justify-center gap-3 mb-3">
        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
          Muestreo de Aceptación
        </h1>
      </div>
      <p class="text-sm sm:text-base text-gray-600 dark:text-gray-300 max-w-2xl mx-auto px-2">
        Calcula el plan de muestreo óptimo basado en niveles de calidad aceptable y tolerancia
      </p>
    </div>

    <!-- Selector de Modo y Tour Button -->
    <div class="mt-6 flex justify-center items-center gap-4 pb-5">
      <div id="mode-selector" class="inline-flex bg-white dark:bg-gray-800 rounded-lg p-1 border-2 border-gray-200 dark:border-gray-700">
        <button
          @click="mode = 'aql-ltpd'"
          :class="[
            'px-6 py-2 rounded-md font-medium transition-all',
            mode === 'aql-ltpd'
              ? 'bg-black dark:bg-white text-white dark:text-black'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
          ]"
        >
          Modo AQL/LTPD
        </button>
        <button
          @click="mode = 'n-c'"
          :class="[
            'px-6 py-2 rounded-md font-medium transition-all',
            mode === 'n-c'
              ? 'bg-black dark:bg-white text-white dark:text-black'
              : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100'
          ]"
        >
          Modo n/c
        </button>
      </div>
      
      <!-- Tour Button -->
      <button
        @click="startTour"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors"
        title="Ver guía interactiva"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-medium">Guía</span>
      </button>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
      <!-- Formulario - Columna fija -->
      <div class="lg:col-span-1">
        <div id="form-container" class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 sticky top-4">
          <div class="flex items-center gap-2 mb-6">
            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Parámetros</h2>
          </div>

          <!-- Formularios específicos por modo -->
          <MuestreoFormAQLLTPD 
            v-if="mode === 'aql-ltpd'"
            v-model="formData"
            :errors="errors"
            @clearError="clearError"
          />
          
          <MuestreoFormNC 
            v-else
            v-model="formData"
            :errors="errors"
            @clearError="clearError"
          />

          <!-- Campos comunes -->
          <MuestreoFormCommon
            v-model="formData"
            :errors="errors"
            @clearError="clearError"
          />

          <button
            id="submit-button"
            @click="handleSubmit"
            :disabled="loading"
            class="w-full mt-5 bg-black dark:bg-white text-white dark:text-black py-3 rounded-lg font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors disabled:bg-gray-400 dark:disabled:bg-gray-600 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Calculando...' : 'Calcular Plan de Muestreo' }}
          </button>

          <!-- Guía -->
          <div id="guide-info" class="mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="text-xs text-gray-600 dark:text-gray-300">
                <p class="font-medium mb-1">Guía rápida:</p>
                <ul v-if="mode === 'aql-ltpd'" class="space-y-1 list-disc list-inside">
                  <li>AQL: % defectos que consideras aceptable</li>
                  <li>LTPD: % defectos máximo tolerable</li>
                  <li>1-α: Confianza (típico: 0.95)</li>
                  <li>β: Riesgo consumidor (típico: 0.10)</li>
                </ul>
                <ul v-else class="space-y-1 list-disc list-inside">
                  <li>n: Tamaño de la muestra a inspeccionar</li>
                  <li>c: Defectos permitidos para aceptar</li>
                  <li>1-α: Confianza (típico: 0.95)</li>
                  <li>β: Riesgo consumidor (típico: 0.10)</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Resultados - 2 columnas -->
      <div class="lg:col-span-2">
        <MuestreoSkeletons v-if="loading" />
        
        <MuestreoResults 
          v-else-if="results"
          :results="results"
          :mode="mode"
        />

        <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-12 border border-gray-200 dark:border-gray-700 text-center">
          <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
          <p class="text-gray-500 dark:text-gray-400">
            Ingresa los parámetros y haz clic en calcular para ver los resultados
          </p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/layouts/MainLayout.vue';
import MuestreoFormAQLLTPD from '@/components/muestreo/MuestreoFormAQLLTPD.vue';
import MuestreoFormNC from '@/components/muestreo/MuestreoFormNC.vue';
import MuestreoFormCommon from '@/components/muestreo/MuestreoFormCommon.vue';
import MuestreoSkeletons from '@/components/muestreo/MuestreoSkeletons.vue';
import MuestreoResults from '@/components/muestreo/MuestreoResults.vue';
import { useMuestreoValidation } from '@/composables/useMuestreoValidation';
import { useMuestreoTour } from '@/composables/useMuestreoTour';
import { MuestreoApiService } from '@/services/muestreoApi';
import type { FormData, ResultData, ModeType } from '@/types/muestreo';

const breadcrumbs = [
  { title: 'Inicio', href: '/' },
  { title: 'Muestreo de Aceptación' }
];

const mode = ref<ModeType>('aql-ltpd');
const loading = ref(false);
const results = ref<ResultData | null>(null);

const formData = ref<FormData>({
  AQT: '',
  LTPD: '',
  '1-alpha': '',
  n: '',
  c: '',
  beta: ''
});

const { errors, clearError, validate, changeMode : changeValidationMode } = useMuestreoValidation(mode.value);
const { startTour, initTour, changeMode: changeTourMode } = useMuestreoTour(mode.value);


watch(mode, (newMode: ModeType) => {
  // Limpiar errores al cambiar de modo
  Object.keys(errors.value).forEach(key => {
    clearError(key as keyof FormData);
  });

  console.log(`Modo cambiado a: ${newMode}`);
  // Cambiar modo en los composables
  changeValidationMode(newMode);
  changeTourMode(newMode);
});

// Inicializar tour
initTour();

const handleSubmit = async () => {
  if (!validate(formData.value)){
    console.log(`Errores: ${JSON.stringify(errors.value)}`);
    return; 
  }

  loading.value = true;
  try {
    const data = await MuestreoApiService.calculate(mode.value, formData.value);
    results.value = data;
  } catch (error) {
    console.error('Error:', error);
    alert('Hubo un error al calcular. Por favor intenta de nuevo.');
  } finally {
    loading.value = false;
  }
};
</script>

<style>
@import '../../css/driver.css';
</style>
