<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { Button } from '@/components/ui/button';
import { 
    ArrowLeft, FlaskConical, Calculator, RefreshCcw, 
    CheckCircle2, AlertTriangle, Info, ChevronDown, ChevronUp,
    Microscope, Scale 
} from "lucide-vue-next";
import axios from 'axios';

// Estado del Acordeón (Por defecto abrimos el Z-Test)
const activeAccordion = ref<string | null>('z-test');

const toggleAccordion = (id: string) => {
    if (activeAccordion.value === id) {
        activeAccordion.value = null;
    } else {
        activeAccordion.value = id;
    }
};

// ==========================================
//  LÓGICA TABLA 2.2 (PRUEBA Z)
// ==========================================

const z_modoSeleccionado = ref<number | null>(null);
const z_mostrarResultados = ref(false);
const z_cargando = ref(false);

const z_promedio = ref('');
const z_u0 = ref('');
const z_desviacion = ref('');
const z_confiabilidad = ref('');
const z_cantidad = ref('');
const z_promedio1 = ref('');
const z_desviacion1 = ref('');
const z_cantidad1 = ref('');
const z_promedio2 = ref('');
const z_desviacion2 = ref('');
const z_cantidad2 = ref('');

const z_z0 = ref<number | null>(null);
const z_za = ref<number | null>(null);
const z_veredicto = ref('');

const z_modos = [
  { id: 0, titulo: 'H₀: μ = μ₀ vs H₁: μ ≠ μ₀', descripcion: 'Bilateral - 1 Muestra', tipo: 'simple', criterio: '|z₀| > z(α/2)' },
  { id: 1, titulo: 'H₀: μ ≥ μ₀ vs H₁: μ < μ₀', descripcion: 'Unilateral Izq - 1 Muestra', tipo: 'simple', criterio: 'z₀ < -zα' },
  { id: 2, titulo: 'H₀: μ ≤ μ₀ vs H₁: μ > μ₀', descripcion: 'Unilateral Der - 1 Muestra', tipo: 'simple', criterio: 'z₀ > zα' },
  { id: 3, titulo: 'H₀: μ₁ = μ₂ vs H₁: μ₁ ≠ μ₂', descripcion: 'Bilateral - 2 Muestras', tipo: 'doble', criterio: '|z₀| > z(α/2)' },
  { id: 4, titulo: 'H₀: μ₁ ≥ μ₂ vs H₁: μ₁ < μ₂', descripcion: 'Unilateral Izq - 2 Muestras', tipo: 'doble', criterio: 'z₀ < -zα' },
  { id: 5, titulo: 'H₀: μ₁ ≤ μ₂ vs H₁: μ₁ > μ₂', descripcion: 'Unilateral Der - 2 Muestras', tipo: 'doble', criterio: 'z₀ > zα' }
];

const z_modoActual = computed(() => z_modos.find(m => m.id === z_modoSeleccionado.value));
const z_esDoble = computed(() => z_modoActual.value?.tipo === 'doble');

const z_formularioValido = computed(() => {
  if (z_modoSeleccionado.value === null) return false;
  if (z_esDoble.value) {
    return z_promedio1.value && z_desviacion1.value && z_cantidad1.value &&
           z_promedio2.value && z_desviacion2.value && z_cantidad2.value && z_confiabilidad.value;
  } else {
    return z_promedio.value && z_u0.value && z_desviacion.value && z_confiabilidad.value && z_cantidad.value;
  }
});

const z_seleccionarModo = (id: number) => { z_modoSeleccionado.value = id; z_limpiarFormulario(); };
const z_limpiarFormulario = () => {
    z_promedio.value = ''; z_u0.value = ''; z_desviacion.value = ''; z_confiabilidad.value = ''; z_cantidad.value = '';
    z_promedio1.value = ''; z_desviacion1.value = ''; z_cantidad1.value = '';
    z_promedio2.value = ''; z_desviacion2.value = ''; z_cantidad2.value = '';
    z_z0.value = null; z_za.value = null; z_veredicto.value = ''; z_mostrarResultados.value = false;
};

const calcularZ = async () => {
    if (!z_formularioValido.value) return;
    z_cargando.value = true;
    try {
        let data: any = { modo: z_modoSeleccionado.value, confiabilidad: parseFloat(z_confiabilidad.value) };
        if (z_esDoble.value) {
            data = { ...data, promedio1: parseFloat(z_promedio1.value), desviacion1: parseFloat(z_desviacion1.value), cantidad1: parseInt(z_cantidad1.value), promedio2: parseFloat(z_promedio2.value), desviacion2: parseFloat(z_desviacion2.value), cantidad2: parseInt(z_cantidad2.value) };
        } else {
            data = { ...data, promedio: parseFloat(z_promedio.value), u0: parseFloat(z_u0.value), desviacion: parseFloat(z_desviacion.value), cantidad: parseInt(z_cantidad.value) };
        }
        const response = await axios.post('/pruebahipotesistabla22', data);
        z_z0.value = response.data.z0; z_za.value = response.data.za; z_veredicto.value = response.data.veredicto; z_mostrarResultados.value = true;
    } catch (error) { console.error(error); alert('Error en cálculo Z'); } finally { z_cargando.value = false; }
};

// ==========================================
//  LÓGICA TABLA 2.3 (PRUEBA T-STUDENT)
// ==========================================

const t_modoSeleccionado = ref<number | null>(null);
const t_mostrarResultados = ref(false);
const t_cargando = ref(false);

const t_promedio = ref('');
const t_u0 = ref('');
const t_varianza = ref('');
const t_cantidad = ref('');
const t_confiabilidad = ref('');
const t_promedio1 = ref('');
const t_varianza1 = ref('');
const t_cantidad1 = ref('');
const t_promedio2 = ref('');
const t_varianza2 = ref('');
const t_cantidad2 = ref('');
const t_asumeVarianzasIguales = ref(true);

const t_t0 = ref<number | null>(null);
const t_ta = ref<number | null>(null);
const t_veredicto = ref('');

const t_modos = [
  { id: 0, titulo: 'H₀: μ = μ₀ vs H₁: μ ≠ μ₀', descripcion: 'Bilateral - 1 Muestra', tipo: 'simple', criterio: '|t₀| > t(α/2, v)' },
  { id: 1, titulo: 'H₀: μ ≥ μ₀ vs H₁: μ < μ₀', descripcion: 'Unilateral Izq - 1 Muestra', tipo: 'simple', criterio: 't₀ < -t(α, v)' },
  { id: 2, titulo: 'H₀: μ ≤ μ₀ vs H₁: μ > μ₀', descripcion: 'Unilateral Der - 1 Muestra', tipo: 'simple', criterio: 't₀ > t(α, v)' },
  { id: 3, titulo: 'H₀: μ₁ = μ₂ vs H₁: μ₁ ≠ μ₂', descripcion: 'Bilateral - 2 Muestras', tipo: 'doble', criterio: '|t₀| > t(α/2, v)' },
  { id: 4, titulo: 'H₀: μ₁ ≥ μ₂ vs H₁: μ₁ < μ₂', descripcion: 'Unilateral Izq - 2 Muestras', tipo: 'doble', criterio: 't₀ < -t(α, v)' },
  { id: 5, titulo: 'H₀: μ₁ ≤ μ₂ vs H₁: μ₁ > μ₂', descripcion: 'Unilateral Der - 2 Muestras', tipo: 'doble', criterio: 't₀ > t(α, v)' }
];

const t_modoActual = computed(() => t_modos.find(m => m.id === t_modoSeleccionado.value));
const t_esDoble = computed(() => t_modoActual.value?.tipo === 'doble');

const t_formularioValido = computed(() => {
  if (t_modoSeleccionado.value === null) return false;
  if (t_esDoble.value) {
    return t_promedio1.value && t_varianza1.value && t_cantidad1.value &&
           t_promedio2.value && t_varianza2.value && t_cantidad2.value && t_confiabilidad.value;
  } else {
    return t_promedio.value && t_u0.value && t_varianza.value && t_confiabilidad.value && t_cantidad.value;
  }
});

const t_seleccionarModo = (id: number) => { t_modoSeleccionado.value = id; t_limpiarFormulario(); };
const t_limpiarFormulario = () => {
    t_promedio.value = ''; t_u0.value = ''; t_varianza.value = ''; t_confiabilidad.value = ''; t_cantidad.value = '';
    t_promedio1.value = ''; t_varianza1.value = ''; t_cantidad1.value = '';
    t_promedio2.value = ''; t_varianza2.value = ''; t_cantidad2.value = '';
    t_asumeVarianzasIguales.value = true;
    t_t0.value = null; t_ta.value = null; t_veredicto.value = ''; t_mostrarResultados.value = false;
};

const calcularT = async () => {
    if (!t_formularioValido.value) return;
    t_cargando.value = true;
    try {
        let data: any = { modo: t_modoSeleccionado.value, confiabilidad: parseFloat(t_confiabilidad.value) };
        if (t_esDoble.value) {
            data = { ...data, promedio1: parseFloat(t_promedio1.value), varianza1: parseFloat(t_varianza1.value), cantidad1: parseInt(t_cantidad1.value), promedio2: parseFloat(t_promedio2.value), varianza2: parseFloat(t_varianza2.value), cantidad2: parseInt(t_cantidad2.value), boolEsVarianzaUnica: t_asumeVarianzasIguales.value ? true : null };
        } else {
            data = { ...data, promedio: parseFloat(t_promedio.value), u0: parseFloat(t_u0.value), varianza: parseFloat(t_varianza.value), cantidad: parseInt(t_cantidad.value) };
        }
        const response = await axios.post('/pruebahipotesistabla23', data);
        t_t0.value = response.data.t0; t_ta.value = response.data.ta; t_veredicto.value = response.data.veredicto; t_mostrarResultados.value = true;
    } catch (error) { console.error(error); alert('Error en cálculo T'); } finally { t_cargando.value = false; }
};
</script>

<template>
  <Head title="Pruebas de Hipótesis" />

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

    <header class="w-full max-w-6xl mx-auto text-center mb-8 px-4">
      <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-2">
        Pruebas de Hipótesis
      </h1>
      <p class="text-base sm:text-lg text-gray-500 dark:text-gray-400">
        Selecciona la prueba estadística adecuada para tus datos.
      </p>
    </header>

    <main class="w-full max-w-5xl mx-auto space-y-6 flex-grow">
        
        <div class="bg-white/90 dark:bg-[#0b0b0b]/90 backdrop-blur rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm transition-all duration-300"
             :class="activeAccordion === 'z-test' ? 'ring-2 ring-indigo-500 shadow-xl' : 'hover:border-indigo-300'">
            
            <button @click="toggleAccordion('z-test')" class="w-full flex items-center justify-between p-6 cursor-pointer focus:outline-none bg-gradient-to-r from-indigo-50/50 to-transparent dark:from-indigo-900/10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <FlaskConical class="w-6 h-6" />
                    </div>
                    <div class="text-left">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Prueba Z (Normal)</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Varianza Poblacional Conocida (Tabla 2.2)</p>
                    </div>
                </div>
                <div class="text-indigo-500">
                    <ChevronUp v-if="activeAccordion === 'z-test'" class="w-6 h-6" />
                    <ChevronDown v-else class="w-6 h-6" />
                </div>
            </button>

            <div v-show="activeAccordion === 'z-test'" class="border-t border-gray-100 dark:border-gray-800 p-6 animate-in slide-in-from-top-2">
                
                <div class="overflow-x-auto mb-8 rounded-lg border border-indigo-100 dark:border-gray-800 bg-indigo-50/30 dark:bg-black/20">
                    <div class="bg-indigo-100/50 dark:bg-gray-800/50 px-4 py-2 border-b border-indigo-100 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-gray-300">Tabla 2.2: Fórmulas de Referencia</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-indigo-100 dark:border-gray-800 text-left">
                                <th class="py-2 px-4 font-semibold text-gray-600 dark:text-gray-400">Hipótesis</th>
                                <th class="py-2 px-4 font-semibold text-center text-gray-600 dark:text-gray-400">Estadístico Z₀</th>
                                <th class="py-2 px-4 font-semibold text-center text-gray-600 dark:text-gray-400">Criterio de Rechazo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-100 dark:divide-gray-800">
                            <tr>
                                <td class="py-2 px-4"><div class="text-xs">H₀: μ = μ₀ <br> H₁: μ ≠ μ₀</div></td>
                                <td rowspan="3" class="py-2 px-4 text-center border-l border-r border-indigo-100 dark:border-gray-800">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="text-xs font-bold bg-white dark:bg-gray-800 px-2 rounded mb-1 border">1 Muestra</span>
                                        <div class="font-mono text-sm text-indigo-600 dark:text-indigo-400 font-bold">(x̄ - μ₀) / (σ / √n)</div>
                                    </div>
                                </td>
                                <td class="py-2 px-4 text-center font-mono text-xs text-red-500">|Z₀| > Z(α/2)</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4"><div class="text-xs">H₀: μ ≥ μ₀ <br> H₁: μ < μ₀</div></td>
                                <td class="py-2 px-4 text-center font-mono text-xs text-red-500">Z₀ < -Zα</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4"><div class="text-xs">H₀: μ ≤ μ₀ <br> H₁: μ > μ₀</div></td>
                                <td class="py-2 px-4 text-center font-mono text-xs text-red-500">Z₀ > Zα</td>
                            </tr>
                            <tr class="bg-indigo-50/50 dark:bg-white/5">
                                <td class="py-2 px-4"><div class="text-xs">H₀: μ₁ = μ₂ <br> H₁: μ₁ ≠ μ₂</div></td>
                                <td rowspan="3" class="py-2 px-4 text-center border-l border-r border-indigo-100 dark:border-gray-800">
                                    <div class="flex flex-col items-center justify-center">
                                        <span class="text-xs font-bold bg-white dark:bg-gray-800 px-2 rounded mb-1 border">2 Muestras</span>
                                        <div class="font-mono text-sm text-indigo-600 dark:text-indigo-400 font-bold">(x̄₁ - x̄₂) / √(σ₁²/n₁ + σ₂²/n₂)</div>
                                    </div>
                                </td>
                                <td class="py-2 px-4 text-center font-mono text-xs text-red-500">|Z₀| > Z(α/2)</td>
                            </tr>
                            <tr class="bg-indigo-50/50 dark:bg-white/5">
                                <td class="py-2 px-4"><div class="text-xs">H₀: μ₁ ≥ μ₂ <br> H₁: μ₁ < μ₂</div></td>
                                <td class="py-2 px-4 text-center font-mono text-xs text-red-500">Z₀ < -Zα</td>
                            </tr>
                            <tr class="bg-indigo-50/50 dark:bg-white/5">
                                <td class="py-2 px-4"><div class="text-xs">H₀: μ₁ ≤ μ₂ <br> H₁: μ₁ > μ₂</div></td>
                                <td class="py-2 px-4 text-center font-mono text-xs text-red-500">Z₀ > Zα</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="z_modoSeleccionado === null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                     <button v-for="modo in z_modos" :key="modo.id" @click="z_seleccionarModo(modo.id)"
                        class="p-4 rounded-lg bg-gray-50 dark:bg-[#151515] hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border border-gray-200 dark:border-gray-700 hover:border-indigo-400 transition-all text-left">
                        <div class="flex justify-between items-center mb-2">
                             <span class="font-bold text-indigo-600 dark:text-indigo-400">Caso {{ modo.id + 1 }}</span>
                             <span class="text-[10px] uppercase font-bold bg-white dark:bg-black px-2 py-1 rounded border border-gray-200 dark:border-gray-800">{{ modo.tipo === 'simple' ? '1 Muestra' : '2 Muestras' }}</span>
                        </div>
                        <p class="font-mono text-xs text-gray-800 dark:text-gray-200 mb-1">{{ modo.titulo }}</p>
                        <p class="text-xs text-gray-500">{{ modo.descripcion }}</p>
                     </button>
                </div>

                <div v-else class="space-y-6">
                    <div class="flex justify-between items-center bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg">
                        <div>
                             <h3 class="font-bold text-indigo-700 dark:text-indigo-300">{{ z_modoActual?.titulo }}</h3>
                             <p class="text-xs text-indigo-600/70 dark:text-indigo-400/70">Prueba Z - {{ z_modoActual?.descripcion }}</p>
                        </div>
                        <button @click="z_modoSeleccionado = null" class="text-xs font-bold text-indigo-600 hover:underline">Cambiar Caso</button>
                    </div>

                    <form @submit.prevent="calcularZ" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div v-if="!z_esDoble" class="grid grid-cols-2 gap-3">
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Media (x̄)</label> <input v-model="z_promedio" type="number" step="any" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Hipotética (μ₀)</label> <input v-model="z_u0" type="number" step="any" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Desviación (σ)</label> <input v-model="z_desviacion" type="number" step="any" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Muestra (n)</label> <input v-model="z_cantidad" type="number" min="1" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                            </div>
                            <div v-else class="space-y-4">
                                <div class="p-3 border rounded bg-gray-50 dark:bg-[#151515] dark:border-gray-700">
                                    <p class="text-xs font-bold mb-2">Muestra 1</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input v-model="z_promedio1" placeholder="x̄₁" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="z_desviacion1" placeholder="σ₁" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="z_cantidad1" placeholder="n₁" type="number" min="1" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                    </div>
                                </div>
                                <div class="p-3 border rounded bg-gray-50 dark:bg-[#151515] dark:border-gray-700">
                                    <p class="text-xs font-bold mb-2">Muestra 2</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input v-model="z_promedio2" placeholder="x̄₂" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="z_desviacion2" placeholder="σ₂" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="z_cantidad2" placeholder="n₂" type="number" min="1" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase text-gray-500 font-bold">Confianza (1-α)</label>
                                <input v-model="z_confiabilidad" type="number" step="0.01" max="1" placeholder="0.95" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700 mt-1" />
                            </div>
                            <Button type="submit" :disabled="!z_formularioValido || z_cargando" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white">
                                {{ z_cargando ? 'Calculando...' : 'Calcular Z' }}
                            </Button>
                        </div>

                        <div class="bg-indigo-50/50 dark:bg-indigo-900/10 rounded-xl p-6 border border-indigo-100 dark:border-indigo-900/30 flex flex-col justify-center items-center text-center">
                            <div v-if="!z_mostrarResultados" class="text-indigo-300 dark:text-indigo-800">
                                <FlaskConical class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">Resultados aquí</p>
                            </div>
                            <div v-else class="w-full space-y-4">
                                <div :class="`p-4 rounded-lg border ${z_veredicto.includes('rechaza') ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800'}`">
                                    <p class="font-bold text-lg">{{ z_veredicto }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white dark:bg-black p-3 rounded border dark:border-gray-700">
                                        <p class="text-xs text-gray-500 uppercase">Z Calculado</p>
                                        <p class="text-xl font-mono font-bold">{{ z_z0?.toFixed(4) }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-black p-3 rounded border dark:border-gray-700">
                                        <p class="text-xs text-gray-500 uppercase">Z Crítico</p>
                                        <p class="text-xl font-mono font-bold">{{ z_za?.toFixed(4) }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="z_limpiarFormulario" class="text-xs underline text-gray-500">Limpiar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white/90 dark:bg-[#0b0b0b]/90 backdrop-blur rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm transition-all duration-300"
             :class="activeAccordion === 't-student' ? 'ring-2 ring-teal-500 shadow-xl' : 'hover:border-teal-300'">
            
            <button @click="toggleAccordion('t-student')" class="w-full flex items-center justify-between p-6 cursor-pointer focus:outline-none bg-gradient-to-r from-teal-50/50 to-transparent dark:from-teal-900/10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                        <Microscope class="w-6 h-6" />
                    </div>
                    <div class="text-left">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Prueba t-Student</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Varianza Desconocida (Tabla 2.3)</p>
                    </div>
                </div>
                <div class="text-teal-500">
                    <ChevronUp v-if="activeAccordion === 't-student'" class="w-6 h-6" />
                    <ChevronDown v-else class="w-6 h-6" />
                </div>
            </button>

            <div v-show="activeAccordion === 't-student'" class="border-t border-gray-100 dark:border-gray-800 p-6 animate-in slide-in-from-top-2">
                
                <div class="overflow-x-auto mb-8 rounded-lg border border-teal-100 dark:border-gray-800 bg-teal-50/30 dark:bg-black/20">
                    <div class="bg-teal-100/50 dark:bg-gray-800/50 px-4 py-2 border-b border-teal-100 dark:border-gray-700">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-teal-700 dark:text-gray-300">Tabla 2.3: Fórmulas de Referencia</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-teal-100 dark:border-gray-800 text-left bg-teal-50/50 dark:bg-white/5">
                                <th class="py-2 px-4 font-semibold text-gray-600 dark:text-gray-400">Caso</th>
                                <th class="py-2 px-4 font-semibold text-center text-gray-600 dark:text-gray-400">Estadístico t₀</th>
                                <th class="py-2 px-4 font-semibold text-center text-gray-600 dark:text-gray-400">Grados Libertad (v)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-teal-100 dark:divide-gray-800">
                            <tr>
                                <td class="py-2 px-4 font-medium">1 Muestra</td>
                                <td class="py-2 px-4 text-center font-mono text-teal-600 dark:text-teal-400 font-bold">(x̄ - μ₀) / (S / √n)</td>
                                <td class="py-2 px-4 text-center font-mono text-xs">n - 1</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 font-medium">2 Muestras (Var. Iguales)</td>
                                <td class="py-2 px-4 text-center font-mono text-teal-600 dark:text-teal-400 font-bold">(x̄₁ - x̄₂) / (Sp · √...)</td>
                                <td class="py-2 px-4 text-center font-mono text-xs">n₁ + n₂ - 2</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 font-medium">2 Muestras (Var. Diferentes)</td>
                                <td class="py-2 px-4 text-center font-mono text-teal-600 dark:text-teal-400 font-bold">(x̄₁ - x̄₂) / √(S₁²/n₁ + S₂²/n₂)</td>
                                <td class="py-2 px-4 text-center font-mono text-xs">Aprox. Welch</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="t_modoSeleccionado === null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                     <button v-for="modo in t_modos" :key="modo.id" @click="t_seleccionarModo(modo.id)"
                        class="p-4 rounded-lg bg-gray-50 dark:bg-[#151515] hover:bg-teal-50 dark:hover:bg-teal-900/20 border border-gray-200 dark:border-gray-700 hover:border-teal-400 transition-all text-left">
                        <div class="flex justify-between items-center mb-2">
                             <span class="font-bold text-teal-600 dark:text-teal-400">Caso {{ modo.id + 1 }}</span>
                             <span class="text-[10px] uppercase font-bold bg-white dark:bg-black px-2 py-1 rounded border border-gray-200 dark:border-gray-800">{{ modo.tipo === 'simple' ? '1 Muestra' : '2 Muestras' }}</span>
                        </div>
                        <p class="font-mono text-xs text-gray-800 dark:text-gray-200 mb-1">{{ modo.titulo }}</p>
                        <p class="text-xs text-gray-500">{{ modo.descripcion }}</p>
                     </button>
                </div>

                <div v-else class="space-y-6">
                    <div class="flex justify-between items-center bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">
                        <div>
                             <h3 class="font-bold text-teal-700 dark:text-teal-300">{{ t_modoActual?.titulo }}</h3>
                             <p class="text-xs text-teal-600/70 dark:text-teal-400/70">Prueba t - {{ t_modoActual?.descripcion }}</p>
                        </div>
                        <button @click="t_modoSeleccionado = null" class="text-xs font-bold text-teal-600 hover:underline">Cambiar Caso</button>
                    </div>

                    <form @submit.prevent="calcularT" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div v-if="!t_esDoble" class="grid grid-cols-2 gap-3">
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Media (x̄)</label> <input v-model="t_promedio" type="number" step="any" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Hipotética (μ₀)</label> <input v-model="t_u0" type="number" step="any" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Varianza (S²)</label> <input v-model="t_varianza" type="number" step="any" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                                <div class="col-span-1"> <label class="text-[10px] uppercase text-gray-500 font-bold">Muestra (n)</label> <input v-model="t_cantidad" type="number" min="1" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700" /> </div>
                            </div>
                            <div v-else class="space-y-4">
                                <label class="flex items-center gap-2 p-3 bg-teal-50 dark:bg-teal-900/10 rounded border border-teal-100 dark:border-teal-800">
                                    <input type="checkbox" v-model="t_asumeVarianzasIguales" class="text-teal-600 rounded" />
                                    <span class="text-xs font-bold">Asumir Varianzas Iguales</span>
                                </label>

                                <div class="p-3 border rounded bg-gray-50 dark:bg-[#151515] dark:border-gray-700">
                                    <p class="text-xs font-bold mb-2">Muestra 1</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input v-model="t_promedio1" placeholder="x̄₁" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="t_varianza1" placeholder="S₁²" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="t_cantidad1" placeholder="n₁" type="number" min="1" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                    </div>
                                </div>
                                <div class="p-3 border rounded bg-gray-50 dark:bg-[#151515] dark:border-gray-700">
                                    <p class="text-xs font-bold mb-2">Muestra 2</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input v-model="t_promedio2" placeholder="x̄₂" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="t_varianza2" placeholder="S₂²" type="number" step="any" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                        <input v-model="t_cantidad2" placeholder="n₂" type="number" min="1" required class="w-full px-2 py-1 text-sm rounded border dark:bg-black dark:border-gray-600" />
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase text-gray-500 font-bold">Confianza (1-α)</label>
                                <input v-model="t_confiabilidad" type="number" step="0.01" max="1" placeholder="0.95" required class="w-full px-3 py-2 rounded border dark:bg-[#151515] dark:border-gray-700 mt-1" />
                            </div>
                            <Button type="submit" :disabled="!t_formularioValido || t_cargando" class="w-full bg-teal-600 hover:bg-teal-700 text-white">
                                {{ t_cargando ? 'Calculando...' : 'Calcular t-Student' }}
                            </Button>
                        </div>

                        <div class="bg-teal-50/50 dark:bg-teal-900/10 rounded-xl p-6 border border-teal-100 dark:border-teal-900/30 flex flex-col justify-center items-center text-center">
                            <div v-if="!t_mostrarResultados" class="text-teal-300 dark:text-teal-800">
                                <Microscope class="w-12 h-12 mx-auto mb-2 opacity-50" />
                                <p class="text-sm">Resultados aquí</p>
                            </div>
                            <div v-else class="w-full space-y-4">
                                <div :class="`p-4 rounded-lg border ${t_veredicto.includes('rechaza') ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800'}`">
                                    <p class="font-bold text-lg">{{ t_veredicto }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-white dark:bg-black p-3 rounded border dark:border-gray-700">
                                        <p class="text-xs text-gray-500 uppercase">t Calculado</p>
                                        <p class="text-xl font-mono font-bold">{{ t_t0?.toFixed(4) }}</p>
                                    </div>
                                    <div class="bg-white dark:bg-black p-3 rounded border dark:border-gray-700">
                                        <p class="text-xs text-gray-500 uppercase">t Crítico</p>
                                        <p class="text-xl font-mono font-bold">{{ t_ta?.toFixed(4) }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="t_limpiarFormulario" class="text-xs underline text-gray-500">Limpiar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <FooterComp class="mt-12" />
  </div>
</template>