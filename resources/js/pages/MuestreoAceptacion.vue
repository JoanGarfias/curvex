<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft, Calculator, CheckCircle2, XCircle, Info, RefreshCcw } from 'lucide-vue-next';

// --- ESTADO Y VARIABLES ---

// Entradas del usuario
const n_lote = ref<number | null>(null);     // N (Tamaño del lote)
const n_muestra = ref<number | null>(null);  // n (Tamaño de la muestra)
const c_aceptacion = ref<number | null>(null); // c (Número de aceptación)
const d_defectos = ref<number | null>(null);   // d (Defectos encontrados)

const loading = ref(false);
const mostrarResultados = ref(false);

// Objeto para guardar los resultados (Tu compañero llenará esto con datos reales del backend)
const resultado = ref({
    decision: 'aceptar', // 'aceptar' | 'rechazar'
    probabilidadAceptacion: 0, // Pa
    tasaDefectos: 0 // p
});

// --- LÓGICA (Aquí trabaja tu compañero) ---

function calcularMuestreo() {
    // Validaciones básicas
    if (n_muestra.value === null || c_aceptacion.value === null || d_defectos.value === null) {
        alert("Por favor llena todos los campos necesarios.");
        return;
    }

    loading.value = true;

    // ---------------------------------------------------------
    // TODO: AQUÍ TU COMPAÑERO CONECTA CON LARAVEL O PONE SU FÓRMULA
    // Ejemplo de lógica simulada (Frontend):
    // Si defectos <= número de aceptación -> ACEPTAR
    // ---------------------------------------------------------
    
    setTimeout(() => { // Simulamos un pequeño delay de carga
        
        // 1. Determinar decisión
        if (d_defectos.value! <= c_aceptacion.value!) {
            resultado.value.decision = 'aceptar';
        } else {
            resultado.value.decision = 'rechazar';
        }

        // 2. Calcular métricas (Simuladas)
        // Ejemplo: Tasa de defectos = Defectos / Muestra
        resultado.value.tasaDefectos = (d_defectos.value! / n_muestra.value!);
        
        // Ejemplo: Probabilidad dummy (aquí iría la Hipergeométrica o Poisson)
        resultado.value.probabilidadAceptacion = Math.random() * (1 - 0.8) + 0.8; 

        mostrarResultados.value = true;
        loading.value = false;

    }, 600);
}

function limpiar() {
    mostrarResultados.value = false;
    n_lote.value = null;
    n_muestra.value = null;
    c_aceptacion.value = null;
    d_defectos.value = null;
}
</script>

<template>
  <Head title="Muestreo de Aceptación" />

  <div class="min-h-screen flex flex-col items-center bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-4 sm:p-6">
    
    <nav class="w-full max-w-5xl flex items-center justify-between mb-6 sm:mb-8 px-2 sm:px-4">
      <div class="flex items-center gap-2 sm:gap-3">
        <Link href="/" class="flex items-center gap-2 group">
            <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10 text-sky-500 group-hover:rotate-12 transition-transform" />
            <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
        </Link>
      </div>
      <ThemeToggle />
    </nav>

    <header class="w-full max-w-4xl flex flex-col md:flex-row items-start md:items-center justify-between mb-8 px-2 gap-4">
        <div>
            <h1 class="text-3xl sm:text-4xl font-bold text-sky-500">Muestreo de Aceptación</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Evalúa la calidad de un lote basándote en una muestra aleatoria.
            </p>
        </div>
        <Link href="/">
            <Button variant="outline" class="gap-2 border-gray-300 dark:border-gray-700">
                <ArrowLeft class="w-4 h-4" /> Volver
            </Button>
        </Link>
    </header>

    <main class="w-full max-w-4xl grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <Calculator class="w-5 h-5 text-sky-500" /> Parámetros
                </h2>
                
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamaño Lote (N)</label>
                        <input v-model="n_lote" type="number" placeholder="Ej. 1000" 
                            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Tamaño Muestra (n)</label>
                        <input v-model="n_muestra" type="number" placeholder="Ej. 80" 
                            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none" />
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Número Aceptación (c)</label>
                        <div class="relative">
                            <input v-model="c_aceptacion" type="number" placeholder="Ej. 2" 
                                class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-transparent px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none" />
                            <div class="absolute right-2 top-2 text-gray-400">
                                <Info class="w-4 h-4 cursor-help" />
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400">Máximo de defectos permitidos para aceptar.</p>
                    </div>

                     <div class="w-full h-px bg-gray-200 dark:bg-gray-800 my-2"></div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-sky-600 dark:text-sky-400 uppercase tracking-wider">Defectos Encontrados (d)</label>
                        <input v-model="d_defectos" type="number" placeholder="Ej. 1" 
                            class="w-full rounded-md border border-sky-200 dark:border-sky-900 bg-sky-50/50 dark:bg-sky-900/10 px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 outline-none font-bold text-sky-700 dark:text-sky-300" />
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <Button @click="calcularMuestreo" :disabled="loading" class="flex-1 bg-sky-500 hover:bg-sky-600 text-white">
                        {{ loading ? 'Calculando...' : 'Evaluar Lote' }}
                    </Button>
                    <Button @click="limpiar" variant="outline" size="icon">
                        <RefreshCcw class="w-4 h-4" />
                    </Button>
                </div>
            </div>
        </div>

        <div class="md:col-span-2">
            
            <div v-if="!mostrarResultados" class="h-full min-h-[300px] flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/20 text-center p-8">
                <div class="w-16 h-16 bg-gray-200 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                    <Calculator class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-600 dark:text-gray-300">Esperando datos</h3>
                <p class="text-sm text-gray-400 max-w-sm mt-2">
                    Ingresa los parámetros del plan de muestreo y los hallazgos para determinar si el lote se acepta o rechaza.
                </p>
            </div>

            <div v-else class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
                
                <div v-if="resultado.decision === 'aceptar'" 
                     class="relative overflow-hidden bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-md p-6">
                    <div class="flex items-start gap-4">
                        <CheckCircle2 class="h-10 w-10 text-emerald-500" />
                        <div>
                            <h2 class="text-2xl font-bold text-emerald-800 dark:text-emerald-100">Lote Aceptado</h2>
                            <p class="text-emerald-700 dark:text-emerald-300 mt-1">
                                La cantidad de defectos ({{ d_defectos }}) es menor o igual al límite de aceptación ({{ c_aceptacion }}).
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else 
                     class="relative overflow-hidden bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-200 dark:border-rose-800 shadow-md p-6">
                    <div class="flex items-start gap-4">
                        <XCircle class="h-10 w-10 text-rose-500" />
                        <div>
                            <h2 class="text-2xl font-bold text-rose-800 dark:text-rose-100">Lote Rechazado</h2>
                            <p class="text-rose-700 dark:text-rose-300 mt-1">
                                La cantidad de defectos ({{ d_defectos }}) excede el límite de aceptación ({{ c_aceptacion }}).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-[#0b0b0b]/60 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Tasa de Defectos (p)</span>
                        <div class="text-2xl font-mono mt-1">{{ (resultado.tasaDefectos * 100).toFixed(2) }}%</div>
                    </div>
                    <div class="bg-white dark:bg-[#0b0b0b]/60 p-4 rounded-lg border border-gray-200 dark:border-gray-800">
                        <span class="text-xs text-gray-500 uppercase font-semibold">Probabilidad (Pa)</span>
                        <div class="text-2xl font-mono mt-1">{{ (resultado.probabilidadAceptacion * 100).toFixed(2) }}%</div>
                        <span class="text-[10px] text-gray-400">Valor simulado (Conectar fórmula)</span>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <FooterComp class="mt-12" />
  </div>
</template>