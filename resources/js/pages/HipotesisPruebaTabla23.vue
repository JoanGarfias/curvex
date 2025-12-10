<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { ArrowLeft, FlaskConical, Calculator, RefreshCcw, CheckCircle2, AlertTriangle, Info, Scale } from "lucide-vue-next";
import axios from 'axios';
import { Button } from '@/components/ui/button';

// --- ESTADO REACTIVO ---
const modoSeleccionado = ref<number | null>(null);
const mostrarResultados = ref(false);
const cargando = ref(false);

// Formulario - Casos 0, 1, 2 (Una Media)
const promedio = ref('');
const u0 = ref('');
const varianza = ref(''); // S²
const cantidad = ref('');
const confiabilidad = ref('');

// Formulario - Casos 3, 4, 5 (Dos Medias)
const promedio1 = ref('');
const varianza1 = ref('');
const cantidad1 = ref('');
const promedio2 = ref('');
const varianza2 = ref('');
const cantidad2 = ref('');
const asumeVarianzasIguales = ref(true); // Controla boolEsVarianzaUnica

// Resultados
const t0 = ref<number | null>(null);
const ta = ref<number | null>(null); // t-alpha (crítico)
const veredicto = ref('');

// --- CONFIGURACIÓN DE MODOS (Tabla 2.3) ---
const modos = [
  { id: 0, titulo: 'H₀: μ = μ₀ vs H₁: μ ≠ μ₀', descripcion: 'Prueba bilateral - Una muestra (Varianza desconocida)', tipo: 'simple', criterio: '|t₀| > t(α/2, v)' },
  { id: 1, titulo: 'H₀: μ ≥ μ₀ vs H₁: μ < μ₀', descripcion: 'Prueba unilateral izquierda - Una muestra', tipo: 'simple', criterio: 't₀ < -t(α, v)' },
  { id: 2, titulo: 'H₀: μ ≤ μ₀ vs H₁: μ > μ₀', descripcion: 'Prueba unilateral derecha - Una muestra', tipo: 'simple', criterio: 't₀ > t(α, v)' },
  { id: 3, titulo: 'H₀: μ₁ = μ₂ vs H₁: μ₁ ≠ μ₂', descripcion: 'Prueba bilateral - Dos muestras (Varianzas desconocidas)', tipo: 'doble', criterio: '|t₀| > t(α/2, v)' },
  { id: 4, titulo: 'H₀: μ₁ ≥ μ₂ vs H₁: μ₁ < μ₂', descripcion: 'Prueba unilateral izquierda - Dos muestras', tipo: 'doble', criterio: 't₀ < -t(α, v)' },
  { id: 5, titulo: 'H₀: μ₁ ≤ μ₂ vs H₁: μ₁ > μ₂', descripcion: 'Prueba unilateral derecha - Dos muestras', tipo: 'doble', criterio: 't₀ > t(α, v)' }
];

const modoActual = computed(() => modos.find(m => m.id === modoSeleccionado.value));
const esDoble = computed(() => modoActual.value?.tipo === 'doble');

// Validación
const formularioValido = computed(() => {
  if (modoSeleccionado.value === null) return false;
  
  if (esDoble.value) {
    return promedio1.value && varianza1.value && cantidad1.value &&
           promedio2.value && varianza2.value && cantidad2.value && confiabilidad.value;
  } else {
    return promedio.value && u0.value && varianza.value && confiabilidad.value && cantidad.value;
  }
});

// --- LÓGICA ---

const seleccionarModo = (id: number) => {
  modoSeleccionado.value = id;
  limpiarFormulario();
};

const limpiarFormulario = () => {
  promedio.value = ''; u0.value = ''; varianza.value = ''; confiabilidad.value = ''; cantidad.value = '';
  promedio1.value = ''; varianza1.value = ''; cantidad1.value = '';
  promedio2.value = ''; varianza2.value = ''; cantidad2.value = '';
  asumeVarianzasIguales.value = true;
  t0.value = null; ta.value = null; veredicto.value = '';
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
      // Lógica para Tabla 2.3 - Dos Medias
      data = { 
        ...data,
        promedio1: parseFloat(promedio1.value),
        varianza1: parseFloat(varianza1.value),
        cantidad1: parseInt(cantidad1.value),
        promedio2: parseFloat(promedio2.value),
        varianza2: parseFloat(varianza2.value),
        cantidad2: parseInt(cantidad2.value),
        // Si asume varianzas iguales, enviamos el flag para que el backend calcule Sp (Pooled)
        // Si no, enviamos solo las varianzas y el backend usa Welch
        boolEsVarianzaUnica: asumeVarianzasIguales.value ? true : null 
      };
    } else {
      // Lógica para Tabla 2.3 - Una Media
      data = { 
        ...data,
        promedio: parseFloat(promedio.value),
        u0: parseFloat(u0.value),
        varianza: parseFloat(varianza.value),
        cantidad: parseInt(cantidad.value)
      };
    }
    
    // Llamada a la ruta definida en web.php para Tabla 2.3
    const response = await axios.post('/pruebahipotesistabla23', data);
    
    t0.value = response.data.t0;
    ta.value = response.data.ta;
    veredicto.value = response.data.veredicto;
    mostrarResultados.value = true;
    
  } catch (error) {
    console.error('Error al calcular:', error);
    alert('Ocurrió un error al procesar la solicitud. Verifica los datos ingresados.');
  } finally {
    cargando.value = false;
  }
};
</script>

<template>
  <Head title="Prueba t-Student (Tabla 2.3)" />

  <div class="min-h-screen flex flex-col bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-4 sm:p-6">
    
    <nav class="w-full max-w-6xl mx-auto flex items-center justify-between mb-8 px-2 sm:px-4">
      <div class="flex items-center gap-4">
        <Link href="/" class="flex items-center gap-2 group">
          <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10 text-sky-500 group-hover:rotate-12 transition-transform" />
          <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
        </Link>
      </div>
      <div class="flex items-center gap-4">
         <Link href="/">
             <Button variant="ghost" class="gap-2 text-sm text-gray-500 hover:text-sky-600">
                 <ArrowLeft class="w-4 h-4" /> Volver al Menú
             </Button>
         </Link>
         <ThemeToggle />
      </div>
    </nav>

    <header class="w-full max-w-6xl mx-auto text-center mb-10 px-4">
      <div class="inline-flex items-center justify-center p-3 mb-4 rounded-full bg-sky-100 dark:bg-sky-900/30 text-sky-600 dark:text-sky-400">
         <FlaskConical class="w-8 h-8" />
      </div>
      <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-4">
        Prueba t-Student
        <span class="block text-2xl mt-2 font-semibold text-gray-600 dark:text-gray-400">Varianza Desconocida (Tabla 2.3)</span>
      </h1>
      <p class="text-base sm:text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-8">
        Realiza pruebas de hipótesis utilizando la distribución t de Student cuando se desconoce la desviación estándar poblacional.
      </p>

      <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 max-w-4xl mx-auto shadow-sm text-left">
        <div class="bg-gray-100 dark:bg-gray-800/50 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
             <h2 class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">Fórmulas de Referencia</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-[#151515]">
                <th class="py-3 px-6 font-semibold text-gray-600 dark:text-gray-300">Caso</th>
                <th class="py-3 px-6 font-semibold text-gray-600 dark:text-gray-300 text-center">Estadístico t₀</th>
                <th class="py-3 px-6 font-semibold text-gray-600 dark:text-gray-300 text-center">Grados de Libertad (v)</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50">
              <tr class="hover:bg-sky-50/50 dark:hover:bg-sky-900/10 transition-colors">
                <td class="py-3 px-6 font-medium">1 Muestra</td>
                <td class="py-3 px-6 text-center font-mono text-sky-600 dark:text-sky-400 font-bold">
                  (x̄ - μ₀) / (S/√n)
                </td>
                <td class="py-3 px-6 text-center font-mono text-xs">n - 1</td>
              </tr>
              <tr class="hover:bg-sky-50/50 dark:hover:bg-sky-900/10 transition-colors">
                <td class="py-3 px-6 font-medium">2 Muestras (Varianzas Iguales)</td>
                <td class="py-3 px-6 text-center font-mono text-sky-600 dark:text-sky-400 font-bold">
                   (x̄₁ - x̄₂) / (Sp · √...)
                </td>
                <td class="py-3 px-6 text-center font-mono text-xs">n₁ + n₂ - 2</td>
              </tr>
              <tr class="hover:bg-sky-50/50 dark:hover:bg-sky-900/10 transition-colors">
                <td class="py-3 px-6 font-medium">2 Muestras (Varianzas Diferentes)</td>
                <td class="py-3 px-6 text-center font-mono text-sky-600 dark:text-sky-400 font-bold">
                   (x̄₁ - x̄₂) / √(S₁²/n₁ + S₂²/n₂)
                </td>
                <td class="py-3 px-6 text-center font-mono text-xs">Aprox. Welch</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </header>

    <main class="w-full max-w-6xl mx-auto px-2 sm:px-4 flex-grow">
      
      <div v-if="modoSeleccionado === null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
        <button
          v-for="modo in modos"
          :key="modo.id"
          @click="seleccionarModo(modo.id)"
          class="group relative p-6 rounded-xl bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-800 hover:border-sky-500 dark:hover:border-sky-500 hover:ring-1 hover:ring-sky-500 hover:shadow-xl transition-all duration-300 text-left"
        >
          <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-700 dark:text-sky-400 font-bold">
              {{ modo.id + 1 }}
            </div>
            <span :class="`text-[10px] uppercase font-bold px-2 py-1 rounded-full ${modo.tipo === 'simple' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300' : 'bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-300'}`">
              {{ modo.tipo === 'simple' ? '1 Muestra' : '2 Muestras' }}
            </span>
          </div>
          <h3 class="font-mono text-sm font-bold mb-2 text-gray-800 dark:text-gray-100">
            {{ modo.titulo }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">
            {{ modo.descripcion }}
          </p>
          <div class="pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center text-xs text-gray-400 font-mono">
            <Info class="w-3 h-3 mr-1" /> Criterio: {{ modo.criterio }}
          </div>
        </button>
      </div>

      <div v-else class="space-y-6 animate-in zoom-in-95 duration-300">
        
        <div class="bg-white dark:bg-[#0b0b0b] rounded-xl p-4 border border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-4">
                 <div class="w-10 h-10 rounded-lg bg-sky-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-sky-500/30">
                  {{ modoSeleccionado + 1 }}
                </div>
                <div>
                    <h2 class="font-mono text-lg font-bold text-gray-800 dark:text-gray-100">{{ modoActual?.titulo }}</h2>
                    <p class="text-sm text-gray-500">{{ modoActual?.descripcion }}</p>
                </div>
            </div>
            <button @click="modoSeleccionado = null" class="text-sm text-gray-500 hover:text-sky-500 underline decoration-dashed">
                Cambiar Modo
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-xl p-6 border border-gray-200 dark:border-gray-800 shadow-sm h-fit">
            <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
              <Calculator class="w-5 h-5 text-sky-500" />
              <h3 class="text-lg font-bold">Datos de Entrada</h3>
            </div>

            <form @submit.prevent="calcular" class="space-y-5">
              
              <div v-if="!esDoble" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Media Muestral (x̄)</label>
                        <input v-model="promedio" type="number" step="any" required placeholder="Ej: 50.5"
                            class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-sky-500 outline-none transition-all" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Media Hipotética (μ₀)</label>
                        <input v-model="u0" type="number" step="any" required placeholder="Ej: 50.0"
                            class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-sky-500 outline-none transition-all" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Varianza (S²)</label>
                        <input v-model="varianza" type="number" step="any" min="0" required placeholder="Ej: 2.5"
                            class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-sky-500 outline-none transition-all" />
                        <p class="text-[10px] text-gray-400">Si tienes S, elévala al cuadrado.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Tamaño Muestra (n)</label>
                        <input v-model="cantidad" type="number" min="1" required placeholder="Ej: 20"
                            class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-sky-500 outline-none transition-all" />
                    </div>
                </div>
              </div>

              <div v-else class="space-y-5">
                
                <div class="bg-sky-50 dark:bg-sky-900/10 p-4 rounded-lg border border-sky-100 dark:border-sky-800">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            <Scale class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                        </div>
                        <div class="flex-grow">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="asumeVarianzasIguales" class="w-4 h-4 text-sky-600 rounded focus:ring-sky-500 border-gray-300" />
                                <span class="font-bold text-sm text-gray-700 dark:text-gray-200">Asumir Varianzas Iguales</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ asumeVarianzasIguales ? 'Se usará la Varianza Combinada (Sp) para los cálculos.' : 'Se usarán varianzas separadas y la aproximación de Welch.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 rounded-lg border border-indigo-100 dark:border-indigo-900/30 bg-indigo-50/50 dark:bg-indigo-900/10 space-y-2">
                        <h4 class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase text-center border-b border-indigo-200 dark:border-indigo-800 pb-1 mb-2">Muestra 1</h4>
                        
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Media (x̄₁)</label>
                            <input v-model="promedio1" type="number" step="any" required 
                                class="w-full px-2 py-1.5 rounded bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-sm transition-all" />
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Varianza (S₁²)</label>
                            <input v-model="varianza1" type="number" step="any" min="0" required 
                                class="w-full px-2 py-1.5 rounded bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-sm transition-all" />
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Tamaño (n₁)</label>
                            <input v-model="cantidad1" type="number" min="1" required 
                                class="w-full px-2 py-1.5 rounded bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-sm transition-all" />
                        </div>
                    </div>

                     <div class="p-3 rounded-lg border border-teal-100 dark:border-teal-900/30 bg-teal-50/50 dark:bg-teal-900/10 space-y-2">
                        <h4 class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase text-center border-b border-teal-200 dark:border-teal-800 pb-1 mb-2">Muestra 2</h4>
                        
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Media (x̄₂)</label>
                            <input v-model="promedio2" type="number" step="any" required 
                                class="w-full px-2 py-1.5 rounded bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-sm transition-all" />
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Varianza (S₂²)</label>
                            <input v-model="varianza2" type="number" step="any" min="0" required 
                                class="w-full px-2 py-1.5 rounded bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-sm transition-all" />
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Tamaño (n₂)</label>
                            <input v-model="cantidad2" type="number" min="1" required 
                                class="w-full px-2 py-1.5 rounded bg-white dark:bg-[#0b0b0b] border border-gray-200 dark:border-gray-700 focus:border-sky-500 focus:ring-1 focus:ring-sky-500 outline-none text-sm transition-all" />
                        </div>
                    </div>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                <label class="text-xs font-semibold text-gray-500 uppercase">Nivel de Confianza (1-α)</label>
                <div class="relative mt-2">
                    <input v-model="confiabilidad" type="number" step="0.01" min="0" max="1" required placeholder="0.95"
                        class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-sky-500 outline-none font-mono text-center tracking-widest" />
                </div>
                <div class="flex gap-2 mt-2 justify-center">
                    <button type="button" @click="confiabilidad = '0.90'" class="text-[10px] px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded hover:bg-gray-200">90%</button>
                    <button type="button" @click="confiabilidad = '0.95'" class="text-[10px] px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded hover:bg-gray-200">95%</button>
                    <button type="button" @click="confiabilidad = '0.99'" class="text-[10px] px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded hover:bg-gray-200">99%</button>
                </div>
              </div>

              <div class="flex gap-3 pt-4">
                <Button type="submit" :disabled="!formularioValido || cargando" class="flex-1 bg-sky-500 hover:bg-sky-600 text-white h-12 text-base shadow-lg shadow-sky-500/20">
                  {{ cargando ? 'Calculando...' : 'Calcular t-Student' }}
                </Button>
                <Button type="button" variant="outline" @click="limpiarFormulario" class="h-12 w-12 p-0">
                    <RefreshCcw class="w-5 h-5 text-gray-500" />
                </Button>
              </div>
            </form>
          </div>

          <div class="space-y-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 px-1">Resultados de la Prueba</h3>

            <div v-if="!mostrarResultados" class="h-full min-h-[400px] flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-[#0b0b0b]/30 text-gray-400 animate-in fade-in duration-700">
              <FlaskConical class="w-12 h-12 mb-3 opacity-20" />
              <p class="text-sm font-medium">Esperando datos...</p>
              <p class="text-xs mt-1 opacity-60">Ingresa los valores y presiona calcular</p>
            </div>

            <div v-else class="space-y-4 animate-in fade-in slide-in-from-right-4 duration-500">
              
              <div :class="`relative overflow-hidden rounded-xl border p-6 shadow-md transition-all duration-500 ${veredicto.includes('rechaza') ? 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-800' : 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800'}`">
                <div class="flex items-start gap-4">
                    <div :class="`p-2 rounded-full shrink-0 ${veredicto.includes('rechaza') ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'}`">
                        <AlertTriangle v-if="veredicto.includes('rechaza')" class="w-8 h-8" />
                        <CheckCircle2 v-else class="w-8 h-8" />
                    </div>
                    <div>
                        <h4 :class="`text-xl font-bold ${veredicto.includes('rechaza') ? 'text-rose-800 dark:text-rose-200' : 'text-emerald-800 dark:text-emerald-200'}`">
                            {{ veredicto }}
                        </h4>
                        <p :class="`text-sm mt-1 leading-relaxed ${veredicto.includes('rechaza') ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300'}`">
                            {{ veredicto.includes('rechaza') 
                                ? 'La evidencia estadística es suficiente para rechazar la hipótesis nula en favor de la alternativa.' 
                                : 'No existe suficiente evidencia estadística para rechazar la hipótesis nula.' }}
                        </p>
                    </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                  <div class="bg-white dark:bg-[#0b0b0b] p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                      <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                          <Calculator class="w-12 h-12" />
                      </div>
                      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Estadístico t₀</p>
                      <p class="text-3xl font-mono font-bold text-gray-800 dark:text-gray-100 mt-2 tracking-tight">{{ t0?.toFixed(4) }}</p>
                  </div>
                   <div class="bg-white dark:bg-[#0b0b0b] p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                      <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Crítico ({{ modoActual?.tipo === 'doble' || modoSeleccionado === 0 ? 'tα/2' : 'tα' }})</p>
                      <p class="text-3xl font-mono font-bold text-gray-800 dark:text-gray-100 mt-2 tracking-tight">{{ ta?.toFixed(4) }}</p>
                  </div>
              </div>

              <div class="bg-gray-100 dark:bg-gray-900 rounded-lg p-4 text-xs font-mono text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-800">
                  <div class="flex justify-between items-center mb-2">
                      <span class="font-bold">Análisis Matemático:</span>
                      <span v-if="esDoble" class="bg-gray-200 dark:bg-gray-800 px-2 py-0.5 rounded text-[10px]">
                          {{ asumeVarianzasIguales ? 'Sp (Pooled)' : 'Welch' }}
                      </span>
                  </div>
                  <p>Regla: Rechazar H₀ si <span class="text-sky-600 dark:text-sky-400">{{ modoActual?.criterio }}</span></p>
                  <p class="mt-1">Comparación: |{{ t0?.toFixed(4) }}| {{ Math.abs(t0!) > Math.abs(ta!) ? '>' : '<' }} {{ Math.abs(ta!).toFixed(4) }}</p>
              </div>

            </div>
          </div>

        </div>
      </div>

    </main>

    <FooterComp class="mt-12" />
  </div>
</template>