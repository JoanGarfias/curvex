<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft, TrendingUp, Calculator, AlertCircle, Info, RefreshCcw } from "lucide-vue-next";
import axios from 'axios';

// --- ESTADO ---
const inputX = ref('');
const inputY = ref('');
const loading = ref(false);
const errorMsg = ref('');
const showResults = ref(false);

// Resultados del Backend
const serverR2 = ref<number | null>(null);

// Resultados Calculados (Frontend para visualización inmediata)
const localModel = ref({ a0: 0, a1: 0, r2: 0, sse: 0, sst: 0 });
const points = ref<{x: number, y: number}[]>([]);

// --- UTILIDADES ---
const parseInput = (text: string) => {
    return text.trim().split(/[\s,;\n]+/).filter(v => v !== '').map(Number);
};

const limpiar = () => {
    inputX.value = '';
    inputY.value = '';
    showResults.value = false;
    errorMsg.value = '';
    serverR2.value = null;
    points.value = [];
};

// --- CÁLCULO DE GRÁFICA (Visualización) ---
const chartData = computed(() => {
    if (points.value.length < 2) return null;
    
    const xs = points.value.map(p => p.x);
    const ys = points.value.map(p => p.y);
    
    const minX = Math.min(...xs);
    const maxX = Math.max(...xs);
    const minY = Math.min(...ys);
    const maxY = Math.max(...ys);
    
    // Margen para la gráfica
    const padding = 20;
    const width = 400;
    const height = 250;

    // Escalas
    const scaleX = (val: number) => padding + ((val - minX) / (maxX - minX || 1)) * (width - 2 * padding);
    const scaleY = (val: number) => height - padding - ((val - minY) / (maxY - minY || 1)) * (height - 2 * padding);

    // Puntos SVG
    const svgPoints = points.value.map(p => ({
        cx: scaleX(p.x),
        cy: scaleY(p.y),
        x: p.x,
        y: p.y
    }));

    // Línea de tendencia (y = a0 + a1*x)
    const x1 = minX;
    const y1 = localModel.value.a0 + localModel.value.a1 * x1;
    const x2 = maxX;
    const y2 = localModel.value.a0 + localModel.value.a1 * x2;

    return {
        width, height,
        line: {
            x1: scaleX(x1), y1: scaleY(y1),
            x2: scaleX(x2), y2: scaleY(y2)
        },
        points: svgPoints
    };
});

// --- LÓGICA PRINCIPAL ---
const calcular = async () => {
    errorMsg.value = '';
    showResults.value = false;
    loading.value = true;

    try {
        const arrX = parseInput(inputX.value);
        const arrY = parseInput(inputY.value);

        // 1. Validaciones
        if (arrX.length !== arrY.length) throw new Error(`Las listas no coinciden: X tiene ${arrX.length} datos, Y tiene ${arrY.length}.`);
        if (arrX.length < 2) throw new Error("Se necesitan al menos 2 pares de datos.");

        // Guardar puntos para gráfica
        points.value = arrX.map((x, i) => ({ x, y: arrY[i] }));

        // 2. Preparar payload para Backend "1,2;3,4"
        const valuesString = points.value.map(p => `${p.x},${p.y}`).join(';');

        // 3. Cálculo Local (Para mostrar A0, A1 y la Gráfica mientras el backend se actualiza)
        // Nota: Esto es un cálculo auxiliar para cumplir con el diseño visual solicitado
        const n = points.value.length;
        const sumX = arrX.reduce((a, b) => a + b, 0);
        const sumY = arrY.reduce((a, b) => a + b, 0);
        const sumXY = points.value.reduce((acc, p) => acc + p.x * p.y, 0);
        const sumX2 = points.value.reduce((acc, p) => acc + p.x * p.x, 0);
        const meanY = sumY / n;

        // Cramer o fórmulas directas para regresión lineal simple
        const denominator = (n * sumX2 - sumX * sumX);
        if (denominator === 0) throw new Error("No se puede calcular regresión (división por cero en pendiente).");
        
        const a1 = (n * sumXY - sumX * sumY) / denominator;
        const a0 = (sumY - a1 * sumX) / n;

        // Calcular SSE y SST locales para visualización
        let sse = 0;
        let sst = 0;
        points.value.forEach(p => {
            const yPred = a0 + a1 * p.x;
            sse += Math.pow(p.y - yPred, 2);
            sst += Math.pow(p.y - meanY, 2);
        });
        const r2Local = 1 - (sse/sst);

        localModel.value = { a0, a1, r2: r2Local, sse, sst };

        // 4. Petición al Backend (Lo que tus compañeros programaron)
        const response = await axios.post('/calc-regresion', {
            values: valuesString,
            method: 'lineal'
        });

        // Obtener R2 del servidor (para validar que el endpoint funciona)
        serverR2.value = response.data.data.R2;

        showResults.value = true;

    } catch (e: any) {
        console.error(e);
        errorMsg.value = e.message || "Error al procesar los datos.";
    } finally {
        loading.value = false;
    }
};
</script>

<template>
  <Head title="Regresión Lineal" />

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
                 <ArrowLeft class="w-4 h-4" /> Volver al Menú
             </Button>
         </Link>
         <ThemeToggle />
      </div>
    </nav>

    <header class="w-full max-w-7xl mx-auto mb-8 px-4 flex items-center gap-3">
        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl text-purple-600 dark:text-purple-400">
            <TrendingUp class="w-8 h-8" />
        </div>
        <div>
            <h1 class="text-3xl font-extrabold leading-tight">Análisis de Regresión</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Ajuste de modelos lineales por Mínimos Cuadrados</p>
        </div>
    </header>

    <main class="w-full max-w-7xl mx-auto px-2 sm:px-4 flex-grow">
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="bg-white/90 dark:bg-[#0b0b0b]/90 backdrop-blur rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm h-fit">
                <div class="flex items-center gap-2 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                    <Calculator class="w-5 h-5 text-purple-500" />
                    <h3 class="text-lg font-bold">Datos de Entrada</h3>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Variable X (Independiente)</label>
                        <textarea 
                            v-model="inputX" 
                            rows="12" 
                            class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-4 text-sm font-mono leading-relaxed resize-none"
                            placeholder="Ej:&#10;1&#10;2&#10;3&#10;4&#10;5"
                        ></textarea>
                        <p class="text-[10px] text-gray-400">Separa los datos con enter o comas.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase text-gray-500 tracking-wider">Variable Y (Dependiente)</label>
                        <textarea 
                            v-model="inputY" 
                            rows="12" 
                            class="w-full rounded-xl bg-gray-50 dark:bg-[#151515] border-gray-200 dark:border-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-4 text-sm font-mono leading-relaxed resize-none"
                            placeholder="Ej:&#10;2.5&#10;3.1&#10;3.9&#10;5.2&#10;6.1"
                        ></textarea>
                        <p class="text-[10px] text-gray-400">Debe tener la misma cantidad que X.</p>
                    </div>
                </div>

                <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-sm rounded-lg flex items-center gap-2">
                    <AlertCircle class="w-4 h-4" /> {{ errorMsg }}
                </div>

                <div class="flex gap-3">
                    <Button @click="calcular" :disabled="loading || !inputX || !inputY" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white h-12 text-base shadow-lg shadow-purple-500/20">
                        {{ loading ? 'Calculando...' : 'Calcular Modelo' }}
                    </Button>
                    <Button @click="limpiar" variant="outline" class="h-12 w-12 p-0 border-gray-200 dark:border-gray-700">
                        <RefreshCcw class="w-5 h-5 text-gray-500" />
                    </Button>
                </div>
            </div>

            <div class="space-y-6">
                
                <div v-if="!showResults" class="h-full min-h-[400px] flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-[#0b0b0b]/30 text-gray-400">
                    <TrendingUp class="w-16 h-16 mb-4 opacity-20" />
                    <p class="text-lg font-medium text-gray-500">Esperando datos...</p>
                    <p class="text-sm">Ingresa los pares (x,y) para ver el modelo.</p>
                </div>

                <div v-else class="animate-in slide-in-from-right-4 duration-500 space-y-6">
                    
                    <div class="bg-white dark:bg-[#0b0b0b] rounded-2xl border border-purple-100 dark:border-purple-900/30 p-6 shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-4 opacity-5">
                            <TrendingUp class="w-32 h-32 text-purple-600" />
                        </div>
                        
                        <h3 class="text-sm font-bold uppercase tracking-widest text-purple-600 dark:text-purple-400 mb-4">Modelo Lineal</h3>
                        
                        <div class="text-center py-4">
                            <p class="text-4xl sm:text-5xl font-extrabold text-gray-800 dark:text-gray-100 tracking-tight">
                                y = {{ localModel.a0.toFixed(4) }} + {{ localModel.a1.toFixed(4) }}x
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 uppercase">Intercepto (a₀)</p>
                                <p class="text-xl font-mono font-bold text-gray-700 dark:text-gray-200">{{ localModel.a0.toFixed(4) }}</p>
                            </div>
                            <div class="text-center border-l border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-500 uppercase">Pendiente (a₁)</p>
                                <p class="text-xl font-mono font-bold text-gray-700 dark:text-gray-200">{{ localModel.a1.toFixed(4) }}</p>
                            </div>
                            <div class="text-center border-l border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-500 uppercase">R² (Backend)</p>
                                <p class="text-xl font-mono font-bold text-purple-600 dark:text-purple-400">{{ serverR2?.toFixed(4) ?? localModel.r2.toFixed(4) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-[#0b0b0b] p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-gray-400">SST</span>
                                <Info class="w-4 h-4 text-gray-300" />
                            </div>
                            <p class="text-2xl font-mono font-bold text-gray-800 dark:text-gray-100">{{ localModel.sst.toFixed(4) }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">Suma Total de Cuadrados</p>
                        </div>
                        <div class="bg-white dark:bg-[#0b0b0b] p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex flex-col justify-between">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-xs font-bold text-gray-400">SSE</span>
                                <Info class="w-4 h-4 text-gray-300" />
                            </div>
                            <p class="text-2xl font-mono font-bold text-gray-800 dark:text-gray-100">{{ localModel.sse.toFixed(4) }}</p>
                            <p class="text-[10px] text-gray-400 mt-1">Suma Error de Cuadrados</p>
                        </div>
                    </div>

                    <div v-if="chartData" class="bg-white dark:bg-[#0b0b0b] rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                        <h3 class="text-sm font-bold uppercase tracking-widest text-gray-500 mb-4">Gráfica de Tendencia</h3>
                        <div class="w-full aspect-video bg-gray-50 dark:bg-[#151515] rounded-lg relative overflow-hidden flex items-center justify-center border border-gray-100 dark:border-gray-800">
                            <svg :viewBox="`0 0 ${chartData.width} ${chartData.height}`" class="w-full h-full p-2">
                                <line :x1="20" :y1="chartData.height - 20" :x2="chartData.width" :y2="chartData.height - 20" stroke="currentColor" class="text-gray-300" stroke-width="1" />
                                <line :x1="20" :y1="chartData.height - 20" :x2="20" :y2="0" stroke="currentColor" class="text-gray-300" stroke-width="1" />

                                <line 
                                    :x1="chartData.line.x1" 
                                    :y1="chartData.line.y1" 
                                    :x2="chartData.line.x2" 
                                    :y2="chartData.line.y2" 
                                    stroke="currentColor" 
                                    class="text-purple-500" 
                                    stroke-width="2" 
                                />

                                <circle 
                                    v-for="(p, i) in chartData.points" 
                                    :key="i" 
                                    :cx="p.cx" 
                                    :cy="p.cy" 
                                    r="4" 
                                    class="fill-white stroke-purple-700 dark:fill-black dark:stroke-purple-400" 
                                    stroke-width="2"
                                >
                                    <title>({{ p.x }}, {{ p.y }})</title>
                                </circle>
                            </svg>
                        </div>
                        <p class="text-center text-xs text-gray-400 mt-2">La línea representa el modelo lineal ajustado.</p>
                    </div>

                </div>
            </div>

        </div>

    </main>

    <FooterComp class="mt-12" />
  </div>
</template>