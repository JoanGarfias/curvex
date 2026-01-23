<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { Button } from '@/components/ui/button';
import { 
    ArrowLeft, Calculator, AlertCircle, RefreshCcw, 
    BarChart4, Trophy, Target
} from "lucide-vue-next";
import axios from 'axios';

type ChartType = 'ajuste' | 'curva' | 'residuos' | 'histograma';

// --- ESTADO ---
const inputX = ref('');
const inputY = ref('');
const loading = ref(false);
const errorMsg = ref('');
const showResults = ref(false);

const selectedMethod = ref('best'); 
const actualMethod = ref('lineal'); // Método real usado por el backend
const numVars = ref(0);
const activeChart = ref<ChartType>('ajuste');

// Estado para la Calculadora Final
const calcMode = ref<'calcY' | 'calcX'>('calcY'); 
const calcInputs = ref<Record<string, string>>({}); 
const calcResult = ref<number | null>(null);
const calcResult2 = ref<number | null>(null);

// --- OPCIONES DE MÉTODO ---
const availableMethods = computed(() => {
    const methods = [
        { id: 'best', name: 'Automático' },
        { id: 'lineal', name: 'Lineal / Multilineal' },
        { id: 'exponential', name: 'Exponencial' },
        { id: 'potential', name: 'Potencial' },
        { id: 'cuadratic', name: 'Cuadrática' },
    ];
    if (numVars.value > 1) return methods.filter(m => m.id != 'cuadratic');
    return methods;
});

const availableCharts = computed<ChartType[]>(() => {
    if (numVars.value > 1) {
        return ['ajuste', 'residuos', 'histograma'];
    }
    return ['ajuste', 'curva', 'residuos', 'histograma'];
});

// Resultados
const results = ref({
    r2: 0,
    equation: '',
    coefficients: [] as number[],
    prediction: [] as number[],
    sse: 0,
    sst: 0,
});


// --- PARSEO ---
const parseData = () => {
    try {
        errorMsg.value = '';
        const rowsX = inputX.value.trim().split('\n');
        const parsedX = rowsX.map(row => row.trim().split(/[\s,;\t]+/).filter(v => v !== '').map(Number)).filter(row => row.length > 0);
        const parsedY = inputY.value.trim().split(/[\s,;\n]+/).filter(v => v !== '').map(Number);

        if (parsedX.length === 0 || parsedY.length === 0) { numVars.value = 0; return; }
        if (parsedX.length !== parsedY.length) throw new Error(`Filas desiguales: X(${parsedX.length}) vs Y(${parsedY.length})`);
        
        const cols = parsedX[0].length;
        if (parsedX.some(row => row.length !== cols)) throw new Error("Matriz X no uniforme");

        numVars.value = cols;

    } catch (e: any) {
        errorMsg.value = e.message;
        numVars.value = 0;
    }
};


watch([inputX, inputY], () => { if(inputX.value && inputY.value) parseData(); });

// --- GRÁFICAS ---

const chartData = computed(() => {
    if (!showResults.value || inputY.value === '') return null;
    const yReal = inputY.value.trim().split(/[\s,;\n]+/).filter(v => v !== '').map(Number);
    const yPred = results.value.prediction;
    if (yReal.length < 2) return null;
    console.log(results.value.prediction);
    const dataPoints = yReal.map((val, i) => ({ real: val, pred: yPred[i] ?? val }));
    const allVals = [...dataPoints.map(p => p.real), ...dataPoints.map(p => p.pred)];
    const minVal = Math.min(...allVals);
    const maxVal = Math.max(...allVals);
    
    const width = 400; const height = 250; const padding = 30;
    const scale = (val: number) => padding + ((val - minVal) / (maxVal - minVal || 1)) * (width - 2 * padding);
    const scaleYInv = (val: number) => height - (padding + ((val - minVal) / (maxVal - minVal || 1)) * (height - 2 * padding));

    return {
        points: dataPoints.map(p => ({ cx: scale(p.pred), cy: scaleYInv(p.real), ...p })),
        line: { x1: scale(minVal), y1: scaleYInv(minVal), x2: scale(maxVal), y2: scaleYInv(maxVal) }
    };
});


const curveData = computed(() => {
    if (!showResults.value || !inputX.value) return null;
    
    try {
        // Parsear X e Y
        const rowsX = inputX.value.trim().split('\n').map(r => 
            r.trim().split(/[\s,;\t]+/).map(Number)
        );
        const xValues = rowsX.map(row => row[0]);
        const yValues = inputY.value.trim().split(/[\s,;\n]+/).map(Number);
        
        if (xValues.length === 0 || yValues.length === 0) return null;
        
        const minX = Math.min(...xValues);
        const maxX = Math.max(...xValues);
        
        // Generar 100 puntos para curva suave
        const numPoints = 100;
        const step = (maxX - minX) / (numPoints - 1);
        
        const curvePoints = [];
        for (let i = 0; i < numPoints; i++) {
            const xVal = minX + step * i;
            let yVal;
            
            // Calcular Y según el método real usado
            switch(actualMethod.value) {
                case 'cuadratic':
                    yVal = results.value.coefficients[0] + 
                        results.value.coefficients[1] * xVal + 
                        results.value.coefficients[2] * Math.pow(xVal, 2);
                    break;
                case 'exponential':
                    yVal = results.value.coefficients[0] * 
                        Math.exp(results.value.coefficients[1] * xVal);
                    break;
                case 'potential':
                    yVal = results.value.coefficients[0] * 
                        Math.pow(xVal, results.value.coefficients[1]);
                    break;
                case 'lineal':
                default:
                    yVal = results.value.coefficients[0] + 
                        results.value.coefficients[1] * xVal;
                    break;
            }
            
            curvePoints.push({ x: xVal, y: yVal });
        }
        
        // IMPORTANTE: Incluir los datos reales Y en el cálculo de escala
        const allYValues = [...curvePoints.map(p => p.y), ...yValues];
        const allXValues = [...curvePoints.map(p => p.x), ...xValues];
        
        const minXScale = Math.min(...allXValues);
        const maxXScale = Math.max(...allXValues);
        const minYScale = Math.min(...allYValues);
        const maxYScale = Math.max(...allYValues);
        
        const width = 400;
        const height = 250;
        const padding = 40;
        
        // Funciones de escala corregidas
        const scaleX = (xCoord: number): number => {
            return padding + ((xCoord - minXScale) / (maxXScale - minXScale || 1)) * (width - 2 * padding);
        };
        
        const scaleY = (yCoord: number): number => {
            return height - padding - ((yCoord - minYScale) / (maxYScale - minYScale || 1)) * (height - 2 * padding);
        };
        
         const result = {
            points: curvePoints.map(p => ({
                x: scaleX(p.x),
                y: scaleY(p.y)
            })),
            dataPoints: xValues.map((xVal, i) => ({
                x: scaleX(xVal),
                y: scaleY(yValues[i])
            })),
            minX: minXScale,
            maxX: maxXScale,
            minY: minYScale,
            maxY: maxYScale
        };

        console.log('=== CURVE DATA DEBUG ===');
        console.log('Coeficientes:', results.value.coefficients);
        console.log('Rango X:', minXScale, 'a', maxXScale);
        console.log('Rango Y:', minYScale, 'a', maxYScale);
        console.log('Primer punto curva (raw):', curvePoints[0]);
        console.log('Último punto curva (raw):', curvePoints[curvePoints.length - 1]);
        console.log('Primer punto escalado:', result.points[0]);
        console.log('Último punto escalado:', result.points[result.points.length - 1]);
        console.log('DataPoints:', result.dataPoints);
        console.log('========================');
        return result;

    } catch (error) {
        console.error('Error en curveData:', error);
        return null;
    }
});

const residuals = computed(() => {
    if (!showResults.value) return [];
    const yReal = inputY.value.trim().split(/[\s,;\n]+/).map(Number);
    return yReal.map((y, i) => y - (results.value.prediction[i] ?? y));
});

const histogram = computed(() => {
    if (residuals.value.length === 0) return [];
    const bins = 5;
    const max = Math.max(...residuals.value.map(r => Math.abs(r))) || 1;
    const step = max / bins;
    return Array.from({ length: bins }, (_, i) => ({
        label: `${(i * step).toFixed(1)} – ${((i + 1) * step).toFixed(1)}`,
        count: residuals.value.filter(r => Math.abs(r) >= i * step && Math.abs(r) < (i + 1) * step).length
    }));
});

// --- LÓGICA DE LA CALCULADORA FINAL ---
const realizarPrediccion = async () => {
    calcResult.value = null;
    calcResult2.value = null;
    const coeffs = results.value.coefficients;
    if (coeffs.length === 0) return;

    console.log('=== PREDICCIÓN DEBUG ===');
    console.log('Mode:', calcMode.value);
    console.log('Num Vars:', numVars.value);
    console.log('Inputs:', calcInputs.value);
    console.log('Method:', actualMethod.value);
    console.log('Coeffs:', coeffs);

    try {
        if (calcMode.value === 'calcY') {
            // Siempre usar el backend para calcular Y
            const payload = {
                variable_input: "x",
                value: parseFloat(calcInputs.value['x0']),
                method: actualMethod.value,
                solutions: coeffs
            };

            console.log('Llamando al backend con payload:', payload);
            const response = await axios.post('/calc-regresion-value', payload);
            const data = response.data.data;
            console.log('Respuesta del backend:', data);
            calcResult.value = data.y;
        } 
        else if (calcMode.value === 'calcX' && numVars.value === 1) {
            // Calcular X dado Y (solo para una variable)
            const payload = {
                variable_input: "y",
                value: parseFloat(calcInputs.value['y']),
                method: actualMethod.value,
                solutions: coeffs
            };

            console.log('Llamando al backend con payload:', payload);
            const response = await axios.post('/calc-regresion-value', payload);
            const data = response.data.data;
            console.log('Respuesta del backend:', data);
            
            // Para cuadrática pueden haber dos soluciones
            if(actualMethod.value === 'cuadratic' && Array.isArray(data.x)){
                calcResult.value = data.x[0];
                calcResult2.value = data.x[1];
            } else {
                calcResult.value = data.x;
            }
        }
    } catch (e: any) {
        console.error('Error en predicción:', e);
        console.error('Detalles del error:', e.response?.data);
        errorMsg.value = e.response?.data?.message || "Error al calcular predicción";
        calcResult.value = null;
        calcResult2.value = null;
    }
};

// Función para normalizar el método del backend al formato interno
const normalizeMethod = (backendMethod: string): string => {
    const methodMap: Record<string, string> = {
        'Lineal': 'lineal',
        'lineal': 'lineal',
        'Exponencial': 'exponential',
        'exponential': 'exponential',
        'Potencial': 'potential',
        'potential': 'potential',
        'Cuadrático': 'cuadratic',
        'cuadratic': 'cuadratic',
    };
    return methodMap[backendMethod] || backendMethod.toLowerCase();
};

// Función para generar la ecuación formateada
const generateEquation = (method: string, coeffs: number[]): string => {
    if (!coeffs || coeffs.length === 0) return 'Modelo no disponible';
    
    const format = (num: number) => num.toFixed(4);
    
    switch(method) {
        case 'lineal':
            if (numVars.value === 1) {
                // Lineal simple: y = a + bx
                return `y = ${format(coeffs[0])} + ${format(coeffs[1])}x`;
            } else {
                // Multilineal: y = a + b₁x₁ + b₂x₂ + ...
                let eq = `y = ${format(coeffs[0])}`;
                for (let i = 1; i < coeffs.length; i++) {
                    eq += ` + ${format(coeffs[i])}x${i}`;
                }
                return eq;
            }
        
        case 'cuadratic':
            // y = a + bx + cx²
            return `y = ${format(coeffs[0])} + ${format(coeffs[1])}x + ${format(coeffs[2])}x²`;
        
        case 'exponential':
            
            if (numVars.value === 1) {
                // y = a·e^(bx)
                return `y = ${format(coeffs[0])}·e^(${format(coeffs[1])}x)`;
            } else {
                // Multilineal: y = a · e^(b₁x₁) · e^(b₂x₂) · e^( ...
                let eq = `y = ${format(coeffs[0])}`;
                for (let i = 1; i < coeffs.length; i++) {
                    eq += ` · e^(${format(coeffs[i])}x${i})`;
                }
                return eq;
            }
            
        
        case 'potential':
            if (numVars.value === 1) {
                // y = a·x^b
                return `y = ${format(coeffs[0])}·x^${format(coeffs[1])}`;
            } else {
                // Multilineal: y = a · x₁^b₁ · x₂^b₂ · ...
                let eq = `y = ${format(coeffs[0])}`;
                for (let i = 1; i < coeffs.length; i++) {
                    eq += ` · x${i}^${format(coeffs[i])}`;
                }
                return eq;
            }
            
        default:
            return `Modelo (R²=${(results.value.r2*100).toFixed(2)}%)`;
    }
};

// --- CALCULAR MODELO ---
const calcular = async () => {
    parseData();
    if(errorMsg.value || numVars.value === 0) return;
    loading.value = true; showResults.value = false;

    try {
        const rowsX = inputX.value.trim().split('\n').map(r => r.trim().split(/[\s,;\t]+/).map(Number));
        const independentArray: string[] = [];
        for (let col = 0; col < numVars.value; col++) independentArray.push(rowsX.map(row => row[col]).join(','));

        const payload = {
            dependent: inputY.value.trim().split(/[\s,;\n]+/).join(','),
            independent: independentArray,
            method: selectedMethod.value
        };

        let data = null;

        if(selectedMethod.value == "best"){
            // 4. Petición Axios
            const response = await axios.post('/calc-best-regresion', payload);
            data = response.data.data;
            actualMethod.value = normalizeMethod(data.method); // Normalizar el método del backend
        }else{
            // 4. Petición Axios
            const response = await axios.post('/calc-regresion', payload);
            data = response.data.data;
            actualMethod.value = selectedMethod.value; // Usar el método seleccionado
        }

        // Generar ecuación formateada usando el método real aplicado
        const equation = generateEquation(actualMethod.value, data.solutions ?? []);

        results.value = {
            r2: data.R2 ?? 0,
            equation: equation,  // ← Usar la ecuación generada
            coefficients: data.solutions ?? [],
            prediction: data.predictions ?? [],
            sse: data.SSE ?? 0,
            sst: data.SST ?? 0
        };
        
        showResults.value = true;
        calcResult.value = null; 
        calcInputs.value = {};

    } catch (e: any) {
        console.error(e);
        errorMsg.value = e.response?.data?.message || "Error al calcular.";
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
                        <option v-for="method in availableMethods" :key="method.id" :value="method.id">{{ method.name }}</option>
                    </select>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2 space-y-2">
    <div class="flex justify-between items-end">
        <label class="text-xs font-bold uppercase text-gray-500">Matriz X</label>
        <span v-if="numVars > 0" class="text-[10px] bg-purple-100 text-purple-700 px-2 rounded">{{ numVars }} Vars</span>
    </div>
    <textarea 
        v-model="inputX" 
        rows="10" 
        class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 p-3 text-xs font-mono"
        placeholder="2, 3, 3
2, 5, 6
4, 7, 8"
    ></textarea>
</div>

<div class="space-y-2">
    <label class="text-xs font-bold uppercase text-gray-500 block text-center">Y</label>
    <textarea 
        v-model="inputY" 
        rows="10" 
        class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 p-3 text-xs font-mono text-center"
        placeholder="10
15
20"
    ></textarea>
</div>
                </div>
                <div v-if="errorMsg" class="mt-4 p-3 bg-red-50 text-red-600 text-xs rounded-lg flex items-center gap-2"><AlertCircle class="w-4 h-4" /> {{ errorMsg }}</div>
                <div class="flex gap-3 mt-6">
                    <Button @click="calcular" :disabled="loading || !inputX" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white">{{ loading ? 'Calculando...' : 'Calcular Regresión' }}</Button>
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
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold uppercase text-purple-600">Modelo Resultante</p>
                            <span v-if="selectedMethod === 'best'" class="px-3 py-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs font-bold rounded-full shadow-sm">
                                {{ actualMethod === 'lineal' ? 'Lineal' : actualMethod === 'exponential' ? 'Exponencial' : actualMethod === 'potential' ? 'Potencial' : actualMethod === 'cuadratic' ? 'Cuadrático' : actualMethod }}
                            </span>
                        </div>
                        <p class="text-xl font-mono font-bold whitespace-nowrap overflow-x-auto pb-2">{{ results.equation }}</p>
                        <p v-if="selectedMethod === 'best'" class="text-xs text-gray-500 dark:text-gray-400 mt-2 italic">
                            Modelo óptimo seleccionado automáticamente según R²
                        </p>
                        <div class="grid grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                            <div>
                                <p class="text-xs uppercase text-gray-500 font-bold">R² (Determinación)</p>
                                <p class="text-3xl font-bold text-green-500">{{ (results.r2 * 100).toFixed(8) }}%</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase text-gray-500 font-bold">Coef. Correlación (r)</p>
                                <p class="text-xl font-mono text-gray-700 dark:text-gray-300">{{ Math.sqrt(Math.abs(results.r2)).toFixed(10) }}</p>
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

                <div class="flex gap-2 mb-4">
                    <button v-for="chart in availableCharts" :key="chart" @click="activeChart = chart" class="px-3 py-1 text-xs rounded-lg border transition capitalize" :class="activeChart === chart ? 'bg-purple-600 text-white border-purple-600' : 'border-gray-300 dark:border-gray-700 text-gray-400 hover:text-white'">{{ chart }}</button>
                </div>

                <div v-if="activeChart === 'ajuste' && chartData" class="bg-white dark:bg-[#0b0b0b] p-6 rounded-2xl border dark:border-gray-800">
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

                <div v-if="activeChart === 'curva' && curveData" class="bg-white dark:bg-[#0b0b0b] p-6 rounded-2xl border dark:border-gray-800">
    <h3 class="font-bold mb-4">Curva del Modelo</h3>
    <div class="w-full aspect-video bg-gray-50 dark:bg-[#151515] rounded-lg relative overflow-hidden">
        <svg viewBox="0 0 400 250" class="w-full h-full">
            <!-- Ejes -->
            <line x1="40" y1="210" x2="360" y2="210" stroke="currentColor" class="text-gray-300" stroke-width="2" />
            <line x1="40" y1="210" x2="40" y2="40" stroke="currentColor" class="text-gray-300" stroke-width="2" />
            
            <!-- Curva del modelo -->
            <polyline 
                :points="curveData.points.map(p => `${p.x},${p.y}`).join(' ')" 
                fill="none" 
                stroke="#9333ea" 
                stroke-width="2" 
            />
            
            <!-- Puntos de datos reales -->
            <circle 
                v-for="(p, i) in curveData.dataPoints" 
                :key="i" 
                :cx="p.x" 
                :cy="p.y" 
                r="5" 
                class="fill-red-500 stroke-red-700"
                stroke-width="2"
            >
                <title>Punto {{ i + 1 }}</title>
            </circle>
            
            <!-- Etiquetas -->
            <text x="200" y="235" text-anchor="middle" class="text-xs fill-gray-500">X</text>
            <text x="20" y="125" text-anchor="middle" class="text-xs fill-gray-500" transform="rotate(-90, 20, 125)">Y</text>
        </svg>
    </div>
    <p class="text-xs text-gray-500 text-center mt-2">La curva muestra el modelo {{ actualMethod }} ajustado.</p>
</div>

                <div v-if="activeChart === 'residuos'" class="bg-white dark:bg-[#0b0b0b] p-6 rounded-2xl border dark:border-gray-800">
                    <h3 class="font-bold mb-4">Residuos</h3>
                    <div class="w-full aspect-video bg-gray-50 dark:bg-[#151515] rounded-lg">
                        <svg viewBox="0 0 400 250" class="w-full h-full p-4">
                            <line x1="0" y1="125" x2="400" y2="125" class="stroke-gray-400" stroke-dasharray="4" />
                            <circle v-for="(r, i) in residuals" :key="i" :cx="i * 30 + 30" :cy="125 - r*10" r="4" class="fill-purple-600" />
                        </svg>
                    </div>
                </div>

                <div v-if="activeChart === 'histograma'" class="bg-white dark:bg-[#0b0b0b] p-6 rounded-2xl border dark:border-gray-800">
                    <h3 class="font-bold mb-4">Distribución del Error</h3>
                    <div class="flex items-end gap-2 h-40">
                        <div v-for="(bin, i) in histogram" :key="i" class="flex-1 bg-purple-500 rounded-t" :style="{ height: `${bin.count * 20}px` }" :title="bin.label"></div>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#0b0b0b] border dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <Target class="w-5 h-5 text-purple-600" />
                        <h3 class="font-bold text-lg">Predicción con el Modelo</h3>
                    </div>

                    <div class="flex gap-6 mb-6">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors" :class="calcMode === 'calcY' ? 'border-purple-600' : 'border-gray-300'">
                                <div v-if="calcMode === 'calcY'" class="w-2.5 h-2.5 bg-purple-600 rounded-full"></div>
                            </div>
                            <input type="radio" v-model="calcMode" value="calcY" class="hidden" />
                            <span :class="calcMode === 'calcY' ? 'text-purple-600 font-bold' : 'text-gray-500'">Calcular Y</span>
                        </label>
                        <label v-if="numVars === 1" class="flex items-center gap-2 cursor-pointer group">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors" :class="calcMode === 'calcX' ? 'border-purple-600' : 'border-gray-300'">
                                <div v-if="calcMode === 'calcX'" class="w-2.5 h-2.5 bg-purple-600 rounded-full"></div>
                            </div>
                            <input type="radio" v-model="calcMode" value="calcX" class="hidden" />
                            <span :class="calcMode === 'calcX' ? 'text-purple-600 font-bold' : 'text-gray-500'">Calcular X</span>
                        </label>
                    </div>

                    <div class="flex items-end gap-4">
                        <div v-if="calcMode === 'calcY'" class="flex-grow grid gap-4" :class="numVars > 1 ? 'grid-cols-2' : 'grid-cols-1'">
                            <div v-for="i in numVars" :key="i">
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Valor de X{{ numVars > 1 ? i : '' }}</label>
                                <input v-model="calcInputs[`x${i-1}`]" type="number" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 outline-none focus:ring-2 focus:ring-purple-500 font-mono no-arrow" :placeholder="`Ingresa X${numVars > 1 ? i : ''}...`" />
                            </div>
                        </div>
                        <div v-else class="flex-grow">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Valor de Y</label>
                            <input v-model="calcInputs['y']" type="number" class="w-full px-4 py-2 rounded-lg bg-gray-50 dark:bg-[#151515] border dark:border-gray-700 outline-none focus:ring-2 focus:ring-purple-500 font-mono" placeholder="Ingresa Y..." />
                        </div>
                        <Button @click="realizarPrediccion" class="bg-purple-600 hover:bg-purple-700 h-[42px] px-6">Calcular</Button>
                    </div>

                    <div v-if="calcResult !== null" class="mt-6 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-xl flex items-center justify-between animate-in slide-in-from-top-2">
                        <span class="text-sm font-bold text-purple-800 dark:text-purple-300 uppercase tracking-wider">Resultado:</span>
                        <div class="flex items-center gap-2 text-2xl font-mono font-bold text-purple-700 dark:text-purple-200">
                            <span>{{ calcMode === 'calcY' ? 'Y' : 'X' }} =</span>
                            <span>{{ calcResult.toFixed(4) }}</span>
                            <span v-if="calcResult2 !== null">X2 = {{ calcResult2.toFixed(4) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
    <FooterComp class="mt-12" />
  </div>
</template>

