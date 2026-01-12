<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { Button } from '@/components/ui/button';
import { 
    ArrowLeft, TrendingUp, Calculator, AlertCircle, RefreshCcw, 
    Table, BarChart4, Sigma, CheckCircle2 
} from "lucide-vue-next";
import axios from 'axios';

// --- ESTADO ---
const inputX = ref('');
const inputY = ref('');
const loading = ref(false);
const errorMsg = ref('');
const showResults = ref(false);

// Configuración del método
const selectedMethod = ref('lineal');
const availableMethods = ref([
    { id: 'lineal', name: 'Lineal / Multilineal' },
    { id: 'exponencial', name: 'Exponencial' },
    { id: 'potencial', name: 'Potencial' },
    { id: 'cuadratica', name: 'Cuadrática' },
]);

// Datos procesados
const matrixX = ref<number[][]>([]); // Matriz de filas (para visualización)
const vectorY = ref<number[]>([]);   // Vector Y
const numVars = ref(0); // Cantidad de columnas (Variables independientes)

// Resultados
const results = ref({
    r2: 0,
    equation: '',
    coefficients: [] as number[],
    prediction: [] as number[],
    sse: 0,
    sst: 0
});

// --- LÓGICA DE PARSEO ---
const parseData = () => {
    try {
        errorMsg.value = '';
        
        // 1. Limpieza y Parseo de X (Soporta Excel: espacios, tabs, comas)
        const rowsX = inputX.value.trim().split('\n');
        const parsedX = rowsX.map(row => 
            row.trim().split(/[\s,;\t]+/).filter(v => v !== '').map(Number)
        ).filter(row => row.length > 0);

        // 2. Limpieza y Parseo de Y
        const parsedY = inputY.value.trim().split(/[\s,;\n]+/).filter(v => v !== '').map(Number);

        if (parsedX.length === 0 || parsedY.length === 0) return;

        // Validaciones según el MD
        if (parsedX.length !== parsedY.length) {
            throw new Error(`Cantidad de datos desigual: X tiene ${parsedX.length} filas, Y tiene ${parsedY.length}.`);
        }

        const cols = parsedX[0].length;
        if (parsedX.some(row => row.length !== cols)) {
            throw new Error("La matriz X no es uniforme. Asegúrate que todas las filas tengan la misma cantidad de columnas.");
        }

        matrixX.value = parsedX;
        vectorY.value = parsedY;
        numVars.value = cols;

    } catch (e: any) {
        errorMsg.value = e.message;
        matrixX.value = [];
        numVars.value = 0;
    }
};

watch([inputX, inputY], () => {
    if (inputX.value && inputY.value) parseData();
});

// --- GRÁFICA ---
const chartData = computed(() => {
    if (vectorY.value.length < 2 || !showResults.value) return null;
    
    // Si no tenemos predicciones del backend, usamos Y vs Y (identidad)
    // Eje X: Predicción (o Y real si no hay predicción), Eje Y: Y Real
    const dataPoints = vectorY.value.map((yReal, i) => ({
        x: results.value.prediction[i] ?? yReal, 
        y: yReal
    }));

    const allVals = [...dataPoints.map(p => p.x), ...dataPoints.map(p => p.y)];
    const minVal = Math.min(...allVals);
    const maxVal = Math.max(...allVals);
    
    const width = 400; const height = 250; const padding = 30;

    // Escalas
    const scale = (val: number) => padding + ((val - minVal) / (maxVal - minVal || 1)) * (width - 2 * padding);
    const scaleYInv = (val: number) => height - (padding + ((val - minVal) / (maxVal - minVal || 1)) * (height - 2 * padding));

    const svgPoints = dataPoints.map(p => ({
        cx: scale(p.x), 
        cy: scaleYInv(p.y),
        real: p.y, pred: p.x
    }));

    return { width, height, points: svgPoints, minVal, maxVal, scale, scaleYInv };
});

// --- ENVIAR AL BACKEND ---
const calcular = async () => {
    parseData();
    if(errorMsg.value || matrixX.value.length === 0) return;
    
    loading.value = true;
    showResults.value = false;

    try {
        // --- PREPARACIÓN DEL PAYLOAD SEGÚN EL MD ---
        
        // 1. Transponer Matriz X: Convertir filas a Columnas (Arrays de strings)
        // El backend espera: independent: ["1,2,3", "4,5,6"]
        const independentArray: string[] = [];
        for (let col = 0; col < numVars.value; col++) {
            const columnValues = matrixX.value.map(row => row[col]);
            independentArray.push(columnValues.join(','));
        }

        // 2. Convertir Y a string: "2,4,6"
        const dependentString = vectorY.value.join(',');

        // 3. Payload Final
        const payload = {
            dependent: dependentString,
            independent: independentArray,
            method: selectedMethod.value
        };

        // 4. Petición Axios
        const response = await axios.post('/calc-regresion', payload);
        const data = response.data.data;

        // 5. Mapeo de Resultados
        results.value = {
            r2: data.R2 ?? 0,
            equation: data.equation ?? `Modelo Calculado`, 
            // Si el backend no envía coeficientes, dejamos vacío por ahora
            coefficients: data.coefficients ?? [],
            // Si el backend no envía predicciones, usamos los datos originales para no romper la gráfica
            prediction: data.predictions ?? [],
            sse: data.SSE ?? 0,
            sst: data.SST ?? 0
        };

        showResults.value = true;

    } catch (e: any) {
        console.error(e);
        // Manejo de errores específicos del backend (422)
        if (e.response && e.response.data && e.response.data.message) {
             errorMsg.value = e.response.data.message;
             if(e.response.data.errors) {
                 // Si hay detalles de validación, mostrarlos
                 errorMsg.value += ' ' + JSON.stringify(e.response.data.errors);
             }
        } else {
            errorMsg.value = "Error de conexión o cálculo.";
        }
    } finally {
        loading.value = false;
    }
};

const limpiar = () => {
    inputX.value = ''; inputY.value = ''; 
    matrixX.value = []; vectorY.value = [];
    showResults.value = false; errorMsg.value = '';
};
</script>

<template>
  <Head title="Regresión Multivariable" />

  <div class="min-h-screen flex flex-col bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-4 sm:p-6">
    
    <nav class="w-full max-w-7xl mx-auto flex items-center justify-between mb-8 px-2 sm:px-4">
      <div class="flex items-center gap-4">
        <Link href="/" class="flex items-center gap-2 group">
          <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10 text-purple-600 group-hover:rotate-12 transition-transform" />
          <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
        </Link>
      </div>
      <div class="flex items-center gap-4">
         <Link href="/">
             <Button variant="ghost" class="gap-2 text-sm text-gray-500 hover:text-purple-600">
                 <ArrowLeft class="w-4 h-4" /> Volver
             </Button>
         </Link>
         <ThemeToggle />
      </div>
    </nav>

    <header class="w-full max-w-7xl mx-auto mb-8 px-4 flex flex-col md:flex-row md:items-center gap-4">
        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl text-purple-600 dark:text-purple-400 w-fit">
            <TrendingUp class="w-8 h-8" />
        </div>
        <div>
            <h1 class="text-3xl font-extrabold leading-tight">Análisis de Regresión</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Ajuste de modelos Lineales y Multivariables
            </p>
        </div>
    </header>

    <main class="w-full max-w-7xl mx-auto px-2 sm:px-4 flex-grow">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-5 space-y-6">
                
                <div class="bg-white/90 dark:bg-[#0b0b0b]/90 backdrop-blur rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <Calculator class="w-5 h-5 text-purple-500" />
                        <h3 class="font-bold text-gray-700 dark:text-gray-200">Datos de Entrada</h3>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">Método</label>
                        <select v-model="selectedMethod" class="w-full px-3 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-purple-500 outline-none text-sm transition-all">
                            <option v-for="method in availableMethods" :key="method.id" :value="method.id">
                                {{ method.name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2 space-y-2">
                            <div class="flex justify-between items-end">
                                <label class="block text-xs font-bold uppercase text-gray-500">Variables X</label>
                                <span class="text-[10px] text-purple-500 font-mono bg-purple-50 dark:bg-purple-900/20 px-2 rounded" v-if="numVars > 0">{{ numVars }} Vars Detectadas</span>
                            </div>
                            <textarea 
                                v-model="inputX" 
                                rows="12" 
                                class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-3 text-xs font-mono leading-relaxed resize-none whitespace-pre"
                                placeholder="Pegar columnas de Excel (X1, X2...)"
                            ></textarea>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase text-gray-500 text-center">Y</label>
                            <textarea 
                                v-model="inputY" 
                                rows="12" 
                                class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-3 text-xs font-mono leading-relaxed resize-none text-center"
                                placeholder="Pegar col Y"
                            ></textarea>
                        </div>
                    </div>

                    <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs rounded-lg flex items-start gap-2 break-all">
                        <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" /> <span>{{ errorMsg }}</span>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <Button @click="calcular" :disabled="loading || matrixX.length === 0" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white shadow-lg shadow-purple-500/20">
                            {{ loading ? 'Procesando...' : 'Calcular Regresión' }}
                        </Button>
                        <Button @click="limpiar" variant="outline" class="w-12 px-0"><RefreshCcw class="w-4 h-4" /></Button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-7 space-y-6">
                
                <div v-if="!showResults" class="h-full min-h-[400px] flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-[#0b0b0b]/30 text-gray-400">
                    <BarChart4 class="w-16 h-16 mb-4 opacity-20" />
                    <p class="text-lg font-medium text-gray-500">Esperando cálculo...</p>
                    <p class="text-sm max-w-xs text-center mt-2 opacity-70">Ingresa las columnas de X y el vector Y para ajustar el modelo.</p>
                </div>

                <div v-else class="animate-in slide-in-from-right-4 duration-500 space-y-6">
                    
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-1 shadow-lg">
                        <div class="bg-white dark:bg-[#0b0b0b] rounded-xl p-6 h-full relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-5">
                                <Sigma class="w-40 h-40 text-purple-600" />
                            </div>
                            
                            <span class="inline-block px-3 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs font-bold uppercase tracking-wider mb-2">
                                {{ selectedMethod.charAt(0).toUpperCase() + selectedMethod.slice(1) }}
                            </span>
                            
                            <div class="py-4 overflow-x-auto scrollbar-hide">
                                <p class="text-xl sm:text-2xl font-mono font-bold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                    {{ results.equation }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-2 pt-4 border-t border-gray-100 dark:border-gray-800">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold">Coeficiente R²</p>
                                    <p class="text-3xl font-bold text-green-500 leading-none">{{ (results.r2 * 100).toFixed(4) }}%</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 uppercase font-bold">Correlación r</p>
                                    <p class="text-xl font-mono text-gray-700 dark:text-gray-300">{{ Math.sqrt(results.r2).toFixed(4) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="chartData" class="bg-white dark:bg-[#0b0b0b] rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-bold text-gray-700 dark:text-gray-200">Gráfica de Ajuste</h3>
                            <span class="text-xs text-gray-400">Y Real vs Y Predicha</span>
                        </div>
                        
                        <div class="w-full aspect-video bg-gray-50 dark:bg-[#151515] rounded-lg relative overflow-hidden border border-gray-100 dark:border-gray-800">
                            <svg :viewBox="`0 0 ${chartData.width} ${chartData.height}`" class="w-full h-full p-4">
                                <line :x1="30" :y1="chartData.height-30" :x2="chartData.width" :y2="chartData.height-30" stroke="currentColor" class="text-gray-300" />
                                <line :x1="30" :y1="chartData.height-30" :x2="30" :y2="0" stroke="currentColor" class="text-gray-300" />

                                <line 
                                    :x1="chartData.scale(chartData.minVal)" :y1="chartData.scaleYInv(chartData.minVal)" 
                                    :x2="chartData.scale(chartData.maxVal)" :y2="chartData.scaleYInv(chartData.maxVal)" 
                                    stroke="#9333ea" 
                                    stroke-width="1" 
                                    stroke-dasharray="5,5" 
                                />

                                <circle 
                                    v-for="(p, i) in chartData.points" 
                                    :key="i" 
                                    :cx="p.cx" :cy="p.cy" r="4" 
                                    class="fill-white dark:fill-black stroke-gray-600 hover:stroke-purple-500 transition-colors cursor-crosshair" 
                                    stroke-width="2"
                                >
                                    <title>Real: {{ p.real.toFixed(2) }} | Pred: {{ p.pred.toFixed(2) }}</title>
                                </circle>
                            </svg>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </main>

    <FooterComp class="mt-12" />
  </div>
</template>