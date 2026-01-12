<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { Button } from '@/components/ui/button';
import { 
    ArrowLeft, TrendingUp, Calculator, AlertCircle, RefreshCcw, 
    Table, BarChart4, Sigma, Trophy, Ban
} from "lucide-vue-next";
import axios from 'axios';

// --- ESTADO ---
const inputX = ref('');
const inputY = ref('');
const loading = ref(false);
const errorMsg = ref('');
const showResults = ref(false);

const selectedMethod = ref('lineal'); // Por defecto Lineal
const numVars = ref(0);

// --- OPCIONES DE MÉTODO (DINÁMICAS) ---
const availableMethods = computed(() => {
    const methods = [
        { id: 'lineal', name: 'Lineal / Multilineal' },
        { id: 'exponencial', name: 'Exponencial' },
        { id: 'potencial', name: 'Potencial' },
        { id: 'cuadratico', name: 'Cuadrática' },
    ];

    // Si hay múltiples variables, filtramos solo Lineal (o Automático si el back lo soporta)
    if (numVars.value > 1) {
        return methods.filter(m => m.id === 'lineal');
    }
    return methods;
});

// Resultados
const results = ref({
    r2: 0,
    equation: '',
    coefficients: [] as number[],
    prediction: [] as number[],
    sse: 0,
    sst: 0
});

// --- PARSEO INTELIGENTE ---
const parseData = () => {
    try {
        errorMsg.value = '';
        const rowsX = inputX.value.trim().split('\n');
        const parsedX = rowsX.map(row => 
            row.trim().split(/[\s,;\t]+/).filter(v => v !== '').map(Number)
        ).filter(row => row.length > 0);

        const parsedY = inputY.value.trim().split(/[\s,;\n]+/).filter(v => v !== '').map(Number);

        if (parsedX.length === 0 || parsedY.length === 0) {
            numVars.value = 0;
            return;
        }

        if (parsedX.length !== parsedY.length) throw new Error(`Filas desiguales: X(${parsedX.length}) vs Y(${parsedY.length})`);
        
        const cols = parsedX[0].length;
        if (parsedX.some(row => row.length !== cols)) throw new Error("Matriz X no uniforme");

        numVars.value = cols;
        
        // AUTO-CORRECCIÓN: Si el usuario tenía Exponencial pero pega 3 columnas, volver a Lineal
        if (cols > 1 && selectedMethod.value !== 'lineal') {
            selectedMethod.value = 'lineal';
        }

    } catch (e: any) {
        errorMsg.value = e.message;
        numVars.value = 0;
    }
};

watch([inputX, inputY], () => { if(inputX.value && inputY.value) parseData(); });

// --- GRÁFICA ---
const chartData = computed(() => {
    if (!showResults.value || inputY.value === '') return null;
    // Parseamos Y otra vez para asegurar reactividad
    const yReal = inputY.value.trim().split(/[\s,;\n]+/).filter(v => v !== '').map(Number);
    const yPred = results.value.prediction;

    if (yReal.length < 2) return null;

    const dataPoints = yReal.map((val, i) => ({
        real: val,
        pred: yPred[i] ?? val // Si no hay pred, usar real (fallback)
    }));

    const allVals = [...dataPoints.map(p => p.real), ...dataPoints.map(p => p.pred)];
    const minVal = Math.min(...allVals);
    const maxVal = Math.max(...allVals);
    
    const width = 400; const height = 250; const padding = 30;
    const scale = (val: number) => padding + ((val - minVal) / (maxVal - minVal || 1)) * (width - 2 * padding);
    const scaleYInv = (val: number) => height - (padding + ((val - minVal) / (maxVal - minVal || 1)) * (height - 2 * padding));

    return {
        points: dataPoints.map(p => ({
            cx: scale(p.pred), cy: scaleYInv(p.real), ...p
        })),
        line: {
            x1: scale(minVal), y1: scaleYInv(minVal),
            x2: scale(maxVal), y2: scaleYInv(maxVal)
        }
    };
});

// --- CALCULAR ---
const calcular = async () => {
    parseData();
    if(errorMsg.value || numVars.value === 0) return;
    
    loading.value = true;
    showResults.value = false;

    try {
        // Preparamos matriz X transpuesta (columnas) para el backend
        // Parseamos de nuevo para asegurar datos frescos
        const rowsX = inputX.value.trim().split('\n').map(r => r.trim().split(/[\s,;\t]+/).map(Number));
        const independentArray: string[] = [];
        
        for (let col = 0; col < numVars.value; col++) {
            independentArray.push(rowsX.map(row => row[col]).join(','));
        }

        const payload = {
            dependent: inputY.value.trim().split(/[\s,;\n]+/).join(','),
            independent: independentArray,
            method: selectedMethod.value
        };

        const response = await axios.post('/calc-regresion', payload);
        const data = response.data.data;

        results.value = {
            r2: data.R2 ?? 0,
            // Si el back no manda ecuación formateada, ponemos un placeholder
            equation: data.equation ?? `Modelo Ajustado (R²=${(data.R2*100).toFixed(2)}%)`,
            coefficients: data.coefficients ?? [],
            prediction: data.predictions ?? [],
            sse: data.SSE ?? 0,
            sst: data.SST ?? 0
        };
        showResults.value = true;

    } catch (e: any) {
        console.error(e);
        errorMsg.value = e.response?.data?.message || "Error al calcular.";
        if(e.response?.data?.error) errorMsg.value += ` (${e.response.data.error})`;
    } finally {
        loading.value = false;
    }
};

const limpiar = () => { inputX.value = ''; inputY.value = ''; showResults.value = false; };
</script>

<template>
  <Head title="Regresión Avanzada" />
  <div class="min-h-screen flex flex-col bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 p-4 sm:p-6">
    
    <nav class="w-full max-w-7xl mx-auto flex justify-between mb-8 px-2">
      <Link href="/" class="flex items-center gap-2 font-bold text-xl">
        <CurvexIcon class="w-8 h-8 text-purple-600" /> Curvex
      </Link>
      <div class="flex gap-4">
         <Link href="/"><Button variant="ghost"><ArrowLeft class="w-4 h-4 mr-2"/> Volver</Button></Link>
         <ThemeToggle />
      </div>
    </nav>

    <main class="w-full max-w-7xl mx-auto px-2 flex-grow grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white/90 dark:bg-[#0b0b0b]/90 backdrop-blur rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <Calculator class="w-5 h-5 text-purple-500" />
                    <h3 class="font-bold">Datos de Entrada</h3>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Método</label>
                    <select v-model="selectedMethod" class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 outline-none text-sm">
                        <option v-for="method in availableMethods" :key="method.id" :value="method.id">
                            {{ method.name }}
                        </option>
                    </select>
                    <p v-if="numVars > 1" class="text-[10px] text-orange-500 mt-1 flex items-center gap-1">
                        <Ban class="w-3 h-3"/> Métodos no lineales deshabilitados para múltiple variable.
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2 space-y-2">
                        <div class="flex justify-between items-end">
                            <label class="text-xs font-bold uppercase text-gray-500">Matriz X</label>
                            <span v-if="numVars > 0" class="text-[10px] bg-purple-100 text-purple-700 px-2 rounded">{{ numVars }} Vars</span>
                        </div>
                        <textarea v-model="inputX" rows="10" class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 p-3 text-xs font-mono" placeholder="Pega columnas X..."></textarea>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-bold uppercase text-gray-500 block text-center">Y</label>
                        <textarea v-model="inputY" rows="10" class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 p-3 text-xs font-mono text-center" placeholder="Pega Y..."></textarea>
                    </div>
                </div>

                <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 text-red-600 text-xs rounded-lg flex items-center gap-2">
                    <AlertCircle class="w-4 h-4" /> {{ errorMsg }}
                </div>

                <div class="flex gap-3 mt-6">
                    <Button @click="calcular" :disabled="loading || !inputX" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white">
                        {{ loading ? 'Calculando...' : 'Calcular Regresión' }}
                    </Button>
                    <Button @click="limpiar" variant="outline" class="w-12 px-0"><RefreshCcw class="w-4 h-4" /></Button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7 space-y-6">
            <div v-if="!showResults" class="h-full min-h-[300px] flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl text-gray-400">
                <BarChart4 class="w-12 h-12 mb-2 opacity-20" />
                <p>Esperando datos...</p>
            </div>

            <div v-else class="space-y-6 animate-in fade-in">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-1 shadow-lg">
                    <div class="bg-white dark:bg-[#0b0b0b] rounded-xl p-6 relative overflow-hidden">
                        <Trophy class="absolute top-0 right-0 p-4 w-32 h-32 text-purple-600 opacity-10" />
                        <p class="text-xs font-bold uppercase text-purple-600 mb-2">Modelo Resultante</p>
                        <p class="text-xl font-mono font-bold whitespace-nowrap overflow-x-auto pb-2">{{ results.equation }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-bold">R² (Determinación)</p>
                                <p class="text-3xl font-bold text-green-500">{{ (results.r2 * 100).toFixed(4) }}%</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase text-gray-500 font-bold">Coef. Correlación (r)</p>
                                <p class="text-xl font-mono text-gray-700 dark:text-gray-300">{{ Math.sqrt(Math.abs(results.r2)).toFixed(4) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-[#0b0b0b] p-4 rounded-xl border dark:border-gray-800">
                        <p class="text-xs font-bold text-gray-400 uppercase">SSE (Error)</p>
                        <p class="text-xl font-mono">{{ results.sse.toFixed(4) }}</p>
                    </div>
                    <div class="bg-white dark:bg-[#0b0b0b] p-4 rounded-xl border dark:border-gray-800">
                        <p class="text-xs font-bold text-gray-400 uppercase">SST (Total)</p>
                        <p class="text-xl font-mono">{{ results.sst.toFixed(4) }}</p>
                    </div>
                </div>

                <div v-if="chartData" class="bg-white dark:bg-[#0b0b0b] p-6 rounded-2xl border dark:border-gray-800">
                    <h3 class="font-bold mb-4">Ajuste (Real vs Predicho)</h3>
                    <div class="w-full aspect-video bg-gray-50 dark:bg-[#151515] rounded-lg relative overflow-hidden">
                        <svg :viewBox="`0 0 400 250`" class="w-full h-full p-4">
                            <line x1="30" y1="220" x2="370" y2="220" stroke="currentColor" class="text-gray-300" />
                            <line x1="30" y1="220" x2="30" y2="30" stroke="currentColor" class="text-gray-300" />
                            <line :x1="chartData.line.x1" :y1="chartData.line.y1" :x2="chartData.line.x2" :y2="chartData.line.y2" stroke="#9333ea" stroke-width="1" stroke-dasharray="4" />
                            <circle v-for="(p, i) in chartData.points" :key="i" :cx="p.cx" :cy="p.cy" r="4" class="fill-white stroke-purple-600 hover:fill-purple-600 transition-colors">
                                <title>Real: {{ p.real.toFixed(2) }} / Pred: {{ p.pred.toFixed(2) }}</title>
                            </circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <FooterComp class="mt-12" />
  </div>
</template>