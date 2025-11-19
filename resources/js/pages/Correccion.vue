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
import type { Resultado } from '@/types/corregido';
import Resultados from '@/components/Corregidos1.vue';

import type { Resultado2 } from '@/types/corregido2';
import Resultados2 from '@/components/Corregidos2.vue';

const resultados = ref<Resultado | null>(null);
const resultados2 = ref<Resultado2 | null>(null);
const showResults = ref(false);

const mode = 'text';
const infinito = ref<'0' | '1'>('0');
const csvFile = ref<File | null>(null);
const text = ref('');
const error = ref('');
const cantdatoscorregido = ref('');
const varianza = ref('');
const promedio = ref('');
const cantdatos = ref('');
const confiabilidad = ref('');
const loading = ref(false);
const errorMessage = ref('');

// Validación para el bloque de Muestra Finita (Bloque 1)
const isForm1Valid = computed(() => {
  return varianza.value.trim() !== '' && 
         promedio.value.trim() !== '' && 
         cantdatos.value.trim() !== '' &&
         cantdatoscorregido.value.trim() !== '';
});

// Validación para el bloque de Muestra Infinita (Bloque 2)
const isForm2Valid = computed(() => {
  return text.value.trim() !== '' && 
         error.value.trim() !== '' && 
         confiabilidad.value.trim() !== '';
});

function analyze() {

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
  if(infinito.value === '0'){
    formData.append('infinito', '1');
    formData.append('values', text.value);
    formData.append('confiabilidad', confiabilidad.value);
    formData.append('cantdatos', cantdatos.value);
    formData.append('error', error.value);
  } else {
    formData.append('infinito', '0');
    formData.append('confiabilidad', confiabilidad.value);
    formData.append('cantdatos', cantdatos.value);
    formData.append('cantdatoscorregido', cantdatoscorregido.value);
    formData.append('varianza', varianza.value);
    formData.append('promedio', promedio.value);
  }
  console.log(infinito.value);
  axios.post('/correct-frequency', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  .then((response) => {
    const data = response.data;
    if(infinito.value === '1' && promedio.value < 0){
      errorMessage.value = 'El promedio no puede ser negativo.';
      loading.value = false;
      return;
    }

    if(cantdatos.value < 0){
      errorMessage.value = 'La cantidad de datos totales no puede ser menor a cero.';
      loading.value = false;
      return;
    }

    // Mapeo actualizado para incluir todos los campos
    if(infinito.value === '0'){
        resultados.value = {
            count: data.count,
            mean: data.mean,
            variance: data.variance,
            standard_deviation: data.standard_deviation,
            alpha: data.alpha,
            valor_critico: data.valor_critico,
            h: data.h,
            hreal: data.hreal
        };

        showResults.value = true;
        loading.value = false;
    }else{
        resultados2.value = {
            variance: data.variance,
            variance2: data.variance2,
            desviacion2: data.desviacion2,
            alpha: data.alpha,
            valor_critico: data.valor_critico,
            limite: data.limite,
            promedio: data.promedio,
        };

        showResults.value = true;
        loading.value = false;
    }
    
  })
  .catch((error) => {
    loading.value = false;
    console.error('Error:', error);

    if (error.response && error.response.data) {
      errorMessage.value =
        error.response.data.error ||
        'Error al procesar los datos';
    } else {
      errorMessage.value = 'Error al conectar con el servidor';
    }
  });
}


function handleGoBack() {
  showResults.value = false;
  resultados.value = null;
  resultados2.value = null;
  errorMessage.value = '';
  // Limpiar inputs
  text.value = '';
  error.value = '';
  cantdatoscorregido.value = '';
  varianza.value = '';
  promedio.value = '';
  cantdatos.value = '';
  confiabilidad.value = '';
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

  <Resultados2 
    v-else-if="showResults && resultados2" 
    :resultado="resultados2" 
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
        Elige una acción en la caja de selección.
      </p>
    </section>

    <!-- Card de controles -->
    <div class="w-full max-w-4xl bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-lg shadow-md p-4 sm:p-6 mx-auto">
      <h2 class="text-xl sm:text-2xl mb-4">Importar datos</h2>

      <div class="grid gap-4">
      <div class="w-full min-w-0 space-y-2">
        <label class="block text-sm font-medium">Tipo de corrección de datos</label>
        <Select v-model="infinito">
          <SelectTrigger class="w-full min-w-0"><SelectValue placeholder="Selecciona el tipo de corrección de datos" /></SelectTrigger>
          <SelectContent class="w-full max-w-[calc(100vw-2rem)]">
            <SelectGroup>
              <SelectLabel>Modo</SelectLabel>
              <SelectItem value='0'>
                <div class="flex flex-col py-1">
                  <span class="font-medium text-sm">Obtención de tamaño ajustado de datos para una muestra infinita</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400"> : Para una muestra muy grande de datos. Retorna la cantidad ajustada de datos de acuerdo a la confiabilidad y al error.</span>
                </div>
              </SelectItem>
              <SelectItem value='1'>
                <div class="flex flex-col py-1">
                  <span class="font-medium text-sm">Varianza corregida para una muestra finita</span>
                  <span class="text-xs text-gray-500 dark:text-gray-400"> : Obtenida a partir de una cantidad de datos y su promedio. Retorna la varianza corregida y la desviación corregida.</span>
                </div>
              </SelectItem>
            </SelectGroup>
          </SelectContent>
        </Select>
      </div>

        <div v-if="infinito === '1'" class="mt-2 w-full">
        <!-- Modo finito. Lo se me equivoque y esta al reves pero ya seria un rollo cambiarlo. Por que estas leyendo esto jajaja-->
            <label class="block text-sm font-medium mb-2">
                Escribe tu cantidad de datos totales
                <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                    <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                    </TooltipTrigger>
                    <TooltipContent><p>La cantidad total de datos, NO la cantidad de datos de la muestra corregida.</p></TooltipContent>
                </Tooltip>
                </TooltipProvider>
            </label>

            <Textarea
                v-model="cantdatos"
                rows="1"
                placeholder="Ejemplo: 1000"
                class="w-full rounded-md border px-3 py-2 bg-transparent 
                    placeholder:text-gray-500 dark:placeholder:text-gray-400
                    text-sm sm:text-base
                    text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                    resize-none"
            ></Textarea>

            <label class="block text-sm font-medium mb-2">
                Escribe tu cantidad de datos de la muestra corregida
                <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                    <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                    </TooltipTrigger>
                    <TooltipContent><p>Si no la conoces, la puedes obtener con la opción de "Obtención de tamaño ajustado" de la caja de selección de acción.</p></TooltipContent>
                </Tooltip>
                </TooltipProvider>
            </label>

            <Textarea
                v-model="cantdatoscorregido"
                rows="1"
                placeholder="Ejemplo: 50"
                class="w-full rounded-md border px-3 py-2 bg-transparent 
                    placeholder:text-gray-500 dark:placeholder:text-gray-400
                    text-sm sm:text-base
                    text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                    resize-none"
            ></Textarea>

            <label class="block text-sm font-medium mb-2">
                Escribe la varianza de tu muestra corregida
                <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                    <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                    </TooltipTrigger>
                    <TooltipContent><p>La varianza obtenida al usar la cantidad de datos indicada en el tamaño de la muestra corregida.</p></TooltipContent>
                </Tooltip>
                </TooltipProvider>
            </label>

            <Textarea
                v-model="varianza"
                rows="1"
                placeholder="Ejemplo: 1.9955"
                class="w-full rounded-md border px-3 py-2 bg-transparent 
                    placeholder:text-gray-500 dark:placeholder:text-gray-400
                    text-sm sm:text-base
                    text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                    resize-none"
            ></Textarea>

            <label class="block text-sm font-medium mb-2">
                Escribe el promedio de tu muestra corregida
                <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                    <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                    </TooltipTrigger>
                    <TooltipContent><p>El promedio obtenido al usar la cantidad de datos indicada en el tamaño de la muestra corregida.</p></TooltipContent>
                </Tooltip>
                </TooltipProvider>
            </label>

            <Textarea
                v-model="promedio"
                rows="1"
                placeholder="Ejemplo: 6.62"
                class="w-full rounded-md border px-3 py-2 bg-transparent 
                    placeholder:text-gray-500 dark:placeholder:text-gray-400
                    text-sm sm:text-base
                    text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                    resize-none"
            ></Textarea>

            <label class="block text-sm font-medium mb-2">
                Escribe tu confiabilidad en una escala de 0 a 100
                <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                    <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                    </TooltipTrigger>
                    <TooltipContent><p>La confiabilidad del proceso, de una escala de 0 a 100.</p></TooltipContent>
                </Tooltip>
                </TooltipProvider>
            </label>

            <Textarea
                v-model="confiabilidad"
                rows="1"
                placeholder="Ejemplo: 95, para representar 95%"
                class="w-full rounded-md border px-3 py-2 bg-transparent 
                    placeholder:text-gray-500 dark:placeholder:text-gray-400
                    text-sm sm:text-base
                    text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                    resize-none"
            ></Textarea>

          <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-red-700 dark:text-red-400 text-xs sm:text-sm">
            {{ errorMessage }}
          </div>

          <Button @click="analyze" :disabled="loading || !isForm1Valid" class="mt-4 w-full text-sm sm:text-base py-5 sm:py-6 h-auto">
            {{ loading ? 'Procesando...' : 'Analizar' }}
          </Button>
        </div>

        
        <div v-else class="mt-2 w-full">
        <!-- Modo infinito -->
          <label class="block text-sm font-medium mb-2">
            Pega o escribe los datos de tu muestra
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                </TooltipTrigger>
                <TooltipContent><p>Escribe tus números separados por espacios, no pueden haber más números que la cantidad de datos totales, ingresada en el recuadro de abajo.</p></TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </label>

          <Textarea
            v-model="text"
            rows="4"
            placeholder="Ejemplo: 6 7 9 8 5 4 7 8 7 6"
            class="w-full rounded-md border px-3 py-2 bg-transparent 
                   placeholder:text-gray-500 dark:placeholder:text-gray-400
                   text-sm sm:text-base
                   text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                   resize-none"
          ></Textarea>

          <label class="block text-sm font-medium mb-2">
            Escribe tu cantidad de datos totales
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                </TooltipTrigger>
                <TooltipContent><p>La cantidad total de datos, NO la cantidad de datos de tu muestra ingresada.</p></TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </label>

          <Textarea
            v-model="cantdatos"
            rows="1"
            placeholder="Ejemplo: 1000"
            class="w-full rounded-md border px-3 py-2 bg-transparent 
                   placeholder:text-gray-500 dark:placeholder:text-gray-400
                   text-sm sm:text-base
                   text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                   resize-none"
          ></Textarea>

          <label class="block text-sm font-medium mb-2">
            Escribe tu confiabilidad en una escala de 0 a 100
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                </TooltipTrigger>
                <TooltipContent><p>La confiabilidad del proceso, de una escala de 0 a 100.</p></TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </label>

          <Textarea
            v-model="confiabilidad"
            rows="1"
            placeholder="Ejemplo: 95, para representar 95%"
            class="w-full rounded-md border px-3 py-2 bg-transparent 
                   placeholder:text-gray-500 dark:placeholder:text-gray-400
                   text-sm sm:text-base
                   text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                   resize-none"
          ></Textarea>

          <label class="block text-sm font-medium mb-2">
            Escribe tu error
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger as-child>
                  <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                </TooltipTrigger>
                <TooltipContent><p>El error del proceso, en una escala decimal de 0 a 1.</p></TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </label>

          <Textarea
            v-model="error"
            rows="1"
            placeholder="Ejemplo: 0.469"
            class="w-full rounded-md border px-3 py-2 bg-transparent 
                   placeholder:text-gray-500 dark:placeholder:text-gray-400
                   text-sm sm:text-base
                   text-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-[#9a9563]
                   resize-none"
          ></Textarea>

          <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-red-700 dark:text-red-400 text-xs sm:text-sm">
            {{ errorMessage }}
          </div>

          <Button @click="analyze" :disabled="loading || !isForm2Valid" class="mt-4 w-full text-sm sm:text-base py-5 sm:py-6 h-auto">
            {{ loading ? 'Procesando...' : 'Analizar' }}
          </Button>
        </div>
      </div>
    </div>

    <FooterComp />
  </div>
</template>
