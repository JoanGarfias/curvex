<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { ArrowLeft, FlaskConical, Calculator, ChevronDown } from "lucide-vue-next";
import axios from 'axios';

// Estado reactivo
const modoSeleccionado = ref<number | null>(null);
const mostrarResultados = ref(false);
const cargando = ref(false);

// Formulario - Casos 0, 1, 2 (5 campos)
const promedio = ref('');
const u0 = ref('');
const desviacion = ref('');
const confiabilidad = ref('');
const cantidad = ref('');

// Formulario adicional - Casos 3, 4, 5 (7 campos)
const promedio1 = ref('');
const desviacion1 = ref('');
const cantidad1 = ref('');
const promedio2 = ref('');
const desviacion2 = ref('');
const cantidad2 = ref('');

// Resultados
const z0 = ref<number | null>(null);
const za = ref<number | null>(null);
const veredicto = ref('');

// Opciones de modo
const modos = [
  {
    id: 0,
    titulo: 'H₀: μ = μ₀ vs H₁: μ ≠ μ₀',
    descripcion: 'Prueba bilateral - Una muestra',
    tipo: 'simple',
    criterio: '|z₀| > z(α/2)'
  },
  {
    id: 1,
    titulo: 'H₀: μ ≥ μ₀ vs H₁: μ < μ₀',
    descripcion: 'Prueba unilateral izquierda - Una muestra',
    tipo: 'simple',
    criterio: 'z₀ < -zα'
  },
  {
    id: 2,
    titulo: 'H₀: μ ≤ μ₀ vs H₁: μ > μ₀',
    descripcion: 'Prueba unilateral derecha - Una muestra',
    tipo: 'simple',
    criterio: 'z₀ > zα'
  },
  {
    id: 3,
    titulo: 'H₀: μ₁ = μ₂ vs H₁: μ₁ ≠ μ₂',
    descripcion: 'Prueba bilateral - Dos muestras',
    tipo: 'doble',
    criterio: '|z₀| > z(α/2)'
  },
  {
    id: 4,
    titulo: 'H₀: μ₁ ≥ μ₂ vs H₁: μ₁ < μ₂',
    descripcion: 'Prueba unilateral izquierda - Dos muestras',
    tipo: 'doble',
    criterio: 'z₀ < -zα'
  },
  {
    id: 5,
    titulo: 'H₀: μ₁ ≤ μ₂ vs H₁: μ₁ > μ₂',
    descripcion: 'Prueba unilateral derecha - Dos muestras',
    tipo: 'doble',
    criterio: 'z₀ > zα'
  }
];

// Computed
const modoActual = computed(() => {
  return modos.find(m => m.id === modoSeleccionado.value);
});

const esDoble = computed(() => {
  return modoActual.value?.tipo === 'doble';
});

const formularioValido = computed(() => {
  if (modoSeleccionado.value === null) return false;
  
  if (esDoble.value) {
    return promedio1.value && desviacion1.value && cantidad1.value &&
           promedio2.value && desviacion2.value && cantidad2.value && confiabilidad.value;
  } else {
    return promedio.value && u0.value && desviacion.value && confiabilidad.value && cantidad.value;
  }
});

// Métodos
const seleccionarModo = (id: number) => {
  modoSeleccionado.value = id;
  limpiarFormulario();
  mostrarResultados.value = false;
};

const limpiarFormulario = () => {
  promedio.value = '';
  u0.value = '';
  desviacion.value = '';
  confiabilidad.value = '';
  cantidad.value = '';
  promedio1.value = '';
  desviacion1.value = '';
  cantidad1.value = '';
  promedio2.value = '';
  desviacion2.value = '';
  cantidad2.value = '';
  z0.value = null;
  za.value = null;
  veredicto.value = '';
  mostrarResultados.value = false;
};

const calcular = async () => {
  if (!formularioValido.value) return;
  
  cargando.value = true;
  
  try {
    let data: any = {
      modo: modoSeleccionado.value,
      confiabilidad: parseFloat(confiabilidad.value)
    };
    
    if (esDoble.value) {
      data = {
        ...data,
        promedio1: parseFloat(promedio1.value),
        desviacion1: parseFloat(desviacion1.value),
        cantidad1: parseInt(cantidad1.value),
        promedio2: parseFloat(promedio2.value),
        desviacion2: parseFloat(desviacion2.value),
        cantidad2: parseInt(cantidad2.value)
      };
    } else {
      data = {
        ...data,
        promedio: parseFloat(promedio.value),
        u0: parseFloat(u0.value),
        desviacion: parseFloat(desviacion.value),
        cantidad: parseInt(cantidad.value)
      };
    }
    
    const response = await axios.post('/pruebahipotesistabla22', data);
    
    z0.value = response.data.z0;
    za.value = response.data.za;
    veredicto.value = response.data.veredicto;
    mostrarResultados.value = true;
    
  } catch (error) {
    console.error('Error al calcular:', error);
    alert('Ocurrió un error al realizar el cálculo. Por favor, verifica los datos ingresados.');
  } finally {
    cargando.value = false;
  }
};
</script>

<template>
  <Head title="Pruebas de Hipótesis - Curvex" />

  <div class="min-h-screen flex flex-col bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-4 sm:p-6">
    
    <!-- Navegación -->
    <nav class="w-full max-w-6xl mx-auto flex items-center justify-between mb-8 px-2 sm:px-4">
      <div class="flex items-center gap-4">
        <Link href="/" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
          <ArrowLeft class="w-5 h-5" />
          <span class="hidden sm:inline">Volver</span>
        </Link>
        <div class="flex items-center gap-2 sm:gap-3">
          <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10 text-gray-900 dark:text-gray-100" />
          <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
        </div>
      </div>
      <ThemeToggle />
    </nav>

    <!-- Header -->
    <header class="w-full max-w-6xl mx-auto text-center mb-10 px-4">
      <div class="flex items-center justify-center gap-3 mb-4">
        <FlaskConical class="w-10 h-10 text-gray-900 dark:text-gray-100" />
      </div>
      <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
        Pruebas de Hipótesis
      </h1>
      <p class="text-base sm:text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
        Realiza pruebas estadísticas para validar o rechazar hipótesis sobre parámetros poblacionales
      </p>
    </header>

    <!-- Contenido Principal -->
    <main class="w-full max-w-6xl mx-auto px-2 sm:px-4 flex-grow">
      
      <!-- Selección de Modo -->
      <div v-if="modoSeleccionado === null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <button
          v-for="modo in modos"
          :key="modo.id"
          @click="seleccionarModo(modo.id)"
          class="group relative p-6 rounded-xl bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur border border-gray-200 dark:border-gray-800 hover:border-gray-400 dark:hover:border-gray-600 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 text-left"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-900 dark:text-gray-100 font-bold">
              {{ modo.id + 1 }}
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
              {{ modo.tipo === 'simple' ? '1 muestra' : '2 muestras' }}
            </span>
          </div>
          <h3 class="font-mono text-sm font-semibold mb-2 text-gray-900 dark:text-gray-100">
            {{ modo.titulo }}
          </h3>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
            {{ modo.descripcion }}
          </p>
          <div class="text-xs text-gray-500 dark:text-gray-500 font-mono">
            Criterio: {{ modo.criterio }}
          </div>
        </button>
      </div>

      <!-- Formulario y Resultados -->
      <div v-else class="space-y-6">
        
        <!-- Info del modo seleccionado -->
        <div class="bg-gradient-to-r from-gray-50 to-white dark:from-[#151515] dark:to-[#0b0b0b] rounded-xl p-6 border border-gray-200 dark:border-gray-800">
          <div class="flex items-start justify-between">
            <div class="flex-grow">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-gray-900 dark:bg-gray-100 flex items-center justify-center text-white dark:text-gray-900 font-bold text-sm">
                  {{ modoSeleccionado + 1 }}
                </div>
                <h2 class="font-mono text-lg font-bold">{{ modoActual?.titulo }}</h2>
              </div>
              <p class="text-sm text-gray-600 dark:text-gray-400 ml-11">
                {{ modoActual?.descripcion }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-500 font-mono mt-2 ml-11">
                Criterio de rechazo: {{ modoActual?.criterio }}
              </p>
            </div>
            <button
              @click="modoSeleccionado = null"
              class="ml-4 px-4 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors"
            >
              Cambiar
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <!-- Formulario -->
          <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-xl p-6 border border-gray-200 dark:border-gray-800">
            <div class="flex items-center gap-2 mb-6">
              <Calculator class="w-5 h-5 text-gray-700 dark:text-gray-300" />
              <h3 class="text-xl font-bold">Datos de Entrada</h3>
            </div>

            <form @submit.prevent="calcular" class="space-y-4">
              
              <!-- Formulario Simple (Una muestra) -->
              <div v-if="!esDoble" class="space-y-4">
                <div>
                  <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    Promedio muestral (x̄)
                  </label>
                  <input
                    v-model="promedio"
                    type="number"
                    step="any"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 transition-all outline-none"
                    placeholder="Ej: 25.5"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    Media poblacional hipotética (μ₀)
                  </label>
                  <input
                    v-model="u0"
                    type="number"
                    step="any"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 transition-all outline-none"
                    placeholder="Ej: 24.0"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    Desviación estándar (σ)
                  </label>
                  <input
                    v-model="desviacion"
                    type="number"
                    step="any"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 transition-all outline-none"
                    placeholder="Ej: 3.5"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    Tamaño de muestra (n)
                  </label>
                  <input
                    v-model="cantidad"
                    type="number"
                    min="1"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 transition-all outline-none"
                    placeholder="Ej: 30"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    Nivel de confianza (1 - α)
                  </label>
                  <input
                    v-model="confiabilidad"
                    type="number"
                    step="0.01"
                    min="0"
                    max="1"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 transition-all outline-none"
                    placeholder="Ej: 0.95"
                  />
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Valores comunes: 0.90, 0.95, 0.99
                  </p>
                </div>
              </div>

              <!-- Formulario Doble (Dos muestras) -->
              <div v-else class="space-y-4">
                <div class="bg-gray-50 dark:bg-[#151515] rounded-lg p-4 space-y-3">
                  <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Muestra 1</h4>
                  
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">
                      Promedio (x̄₁)
                    </label>
                    <input
                      v-model="promedio1"
                      type="number"
                      step="any"
                      required
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#0b0b0b] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 transition-all outline-none text-sm"
                      placeholder="Ej: 25.5"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">
                      Desviación estándar (σ₁)
                    </label>
                    <input
                      v-model="desviacion1"
                      type="number"
                      step="any"
                      required
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#0b0b0b] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 transition-all outline-none text-sm"
                      placeholder="Ej: 3.5"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">
                      Tamaño (n₁)
                    </label>
                    <input
                      v-model="cantidad1"
                      type="number"
                      min="1"
                      required
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#0b0b0b] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 transition-all outline-none text-sm"
                      placeholder="Ej: 30"
                    />
                  </div>
                </div>

                <div class="bg-gray-50 dark:bg-[#151515] rounded-lg p-4 space-y-3">
                  <h4 class="font-semibold text-sm text-gray-700 dark:text-gray-300">Muestra 2</h4>
                  
                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">
                      Promedio (x̄₂)
                    </label>
                    <input
                      v-model="promedio2"
                      type="number"
                      step="any"
                      required
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#0b0b0b] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 transition-all outline-none text-sm"
                      placeholder="Ej: 23.8"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">
                      Desviación estándar (σ₂)
                    </label>
                    <input
                      v-model="desviacion2"
                      type="number"
                      step="any"
                      required
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#0b0b0b] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 transition-all outline-none text-sm"
                      placeholder="Ej: 4.2"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">
                      Tamaño (n₂)
                    </label>
                    <input
                      v-model="cantidad2"
                      type="number"
                      min="1"
                      required
                      class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#0b0b0b] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 transition-all outline-none text-sm"
                      placeholder="Ej: 35"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">
                    Nivel de confianza (1 - α)
                  </label>
                  <input
                    v-model="confiabilidad"
                    type="number"
                    step="0.01"
                    min="0"
                    max="1"
                    required
                    class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:border-gray-500 dark:focus:border-gray-500 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 transition-all outline-none"
                    placeholder="Ej: 0.95"
                  />
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Valores comunes: 0.90, 0.95, 0.99
                  </p>
                </div>
              </div>

              <div class="flex gap-3 pt-4">
                <button
                  type="submit"
                  :disabled="!formularioValido || cargando"
                  class="flex-1 px-6 py-3 rounded-lg bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 font-semibold hover:bg-gray-800 dark:hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                >
                  {{ cargando ? 'Calculando...' : 'Calcular' }}
                </button>
                <button
                  type="button"
                  @click="limpiarFormulario"
                  class="px-6 py-3 rounded-lg bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-all font-semibold"
                >
                  Limpiar
                </button>
              </div>
            </form>
          </div>

          <!-- Resultados -->
          <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-xl p-6 border border-gray-200 dark:border-gray-800">
            <h3 class="text-xl font-bold mb-6">Resultados</h3>

            <div v-if="!mostrarResultados" class="flex flex-col items-center justify-center h-64 text-gray-400">
              <FlaskConical class="w-16 h-16 mb-4 opacity-20" />
              <p class="text-center">Los resultados aparecerán aquí después del cálculo</p>
            </div>

            <div v-else class="space-y-6">
              
              <!-- Estadístico de prueba -->
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-[#151515] dark:to-[#0f0f0f] rounded-xl p-5 border border-gray-200 dark:border-gray-800">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Estadístico de prueba</p>
                <p class="text-3xl font-bold font-mono text-gray-900 dark:text-gray-100">
                  z₀ = {{ z0?.toFixed(4) }}
                </p>
              </div>

              <!-- Valor crítico -->
              <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-[#151515] dark:to-[#0f0f0f] rounded-xl p-5 border border-gray-200 dark:border-gray-800">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Valor crítico</p>
                <p class="text-3xl font-bold font-mono text-gray-900 dark:text-gray-100">
                  {{ modoSeleccionado === 0 || modoSeleccionado === 3 ? 'z(α/2)' : 'zα' }} = {{ za?.toFixed(4) }}
                </p>
              </div>

              <!-- Veredicto -->
              <div :class="[
                'rounded-xl p-6 border-2',
                veredicto.includes('rechaza') 
                  ? 'bg-red-50 dark:bg-red-950/20 border-red-300 dark:border-red-900' 
                  : 'bg-green-50 dark:bg-green-950/20 border-green-300 dark:border-green-900'
              ]">
                <p class="text-sm font-semibold mb-2" :class="veredicto.includes('rechaza') ? 'text-red-700 dark:text-red-400' : 'text-green-700 dark:text-green-400'">
                  Decisión
                </p>
                <p class="text-lg font-bold" :class="veredicto.includes('rechaza') ? 'text-red-900 dark:text-red-300' : 'text-green-900 dark:text-green-300'">
                  {{ veredicto }}
                </p>
              </div>

              <!-- Interpretación -->
              <div class="bg-gray-50 dark:bg-[#151515] rounded-xl p-5 border border-gray-200 dark:border-gray-800">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Interpretación</p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                  {{ veredicto.includes('rechaza') 
                    ? 'Existe evidencia estadística suficiente para rechazar la hipótesis nula al nivel de confianza especificado.' 
                    : 'No existe evidencia estadística suficiente para rechazar la hipótesis nula al nivel de confianza especificado.' 
                  }}
                </p>
              </div>

            </div>
          </div>

        </div>
      </div>

    </main>

    <FooterComp class="mt-12" />
  </div>
</template>