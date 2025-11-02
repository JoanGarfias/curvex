<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { useAppearance } from '@/composables/useAppearance';
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

const mode = ref<'file' | 'text'>('file');
const csvFile = ref<File | null>(null);
const text = ref('');
const loading = ref(false);

// Appearance (light/dark/system) from composable
const { appearance } = useAppearance();

const isDark = computed(() => {
    // explicit preference
    if (appearance.value === 'dark') return true;
    if (appearance.value === 'light') return false;

    // system: check current document class or media query
    if (typeof document !== 'undefined') {
        if (document.documentElement.classList.contains('dark')) return true;
    }

    if (typeof window !== 'undefined') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    return false;
});

const hasInput = computed(() => {
    return mode.value === 'file' ? !!csvFile.value : text.value.trim().length > 0;
});

// (file changes are handled by the drag/drop input handler)

function analyze() {
    if (!hasInput.value) return;

    loading.value = true;
    uploadProgress.value = 0;

    const interval = setInterval(() => {
        uploadProgress.value = Math.min(100, uploadProgress.value + Math.floor(Math.random() * 15) + 5);
        if (uploadProgress.value >= 100) {
            clearInterval(interval);
            setTimeout(() => router.visit('/resultados'), 300);
        }
    }, 250);
}

function openCsvPicker() {
    csvInputRef.value?.click();
}

// Drag & drop state and refs (CSV-specific)
const csvInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const uploadProgress = ref(0);

const csvFileName = computed(() => (csvFile.value ? csvFile.value.name : ''));

function handleCsvDragOver(e: DragEvent) {
    e.preventDefault();
    isDragging.value = true;
}

function handleCsvDragLeave(e: DragEvent) {
    e.preventDefault();
    isDragging.value = false;
}

function handleCsvDrop(e: DragEvent) {
    e.preventDefault();
    isDragging.value = false;
    const f = e.dataTransfer?.files && e.dataTransfer.files[0];
    if (f) {
        csvFile.value = f;
    }
}

function handleCsvFileChange(e: Event) {
    const target = e.target as HTMLInputElement;
    const f = target.files && target.files[0];
    csvFile.value = f ?? null;
}
</script>

<template>
    <Head title="Curvex" />
    <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-6">
        <!-- Navbar -->
        <nav class="w-full max-w-5xl flex items-center justify-between mb-8 px-4">
            <div class="flex items-center gap-3">
                <CurvexIcon />
                <span class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-gray-100">Curvex</span>
            </div>
            <ThemeToggle />
        </nav>

        <!-- Hero: page title (more prominent) and description subtitle -->
        <section class="text-center mb-8 pt-8 px-4">
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-tight text-gray-900 dark:text-gray-100">Curvex</h1>
            <h2 class="mt-4 text-2xl sm:text-3xl font-semibold text-gray-700 dark:text-gray-200 max-w-3xl mx-auto">Analiza tus datos, calcula estadísticas y visualiza resultados de forma sencilla</h2>
            <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">Arrastra y suelta tus archivos CSV o escribe tu matriz de datos directamente para comenzar.</p>
        </section>

        <!-- Controls card -->
        <div class="w-full max-w-4xl bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur rounded-lg shadow-md p-6 mx-auto">
            <h2 class="text-2xl mb-4">Importar datos</h2>

            <div class="grid gap-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de entrada</label>
                <Select v-model="mode" class="w-full sm:w-64">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Selecciona el modo de entrada" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Modos</SelectLabel>
                      <SelectItem value="file">Archivo CSV</SelectItem>
                      <SelectItem value="text">Texto</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>

                <div v-if="mode === 'file'" class="mt-2 w-full">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Seleccionar archivo CSV</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Arrastra tu archivo <strong class="font-medium">.csv</strong> aquí o haz clic para seleccionarlo.</p>

                                        <!-- Zona Drag & Drop -->
                                        <div
                                            class="flex flex-col items-center justify-center border-2 border-dashed rounded-lg py-10 cursor-pointer transition-colors w-full"
                                            :class="isDragging ? 'border-[#9a9563] bg-yellow-50/30 dark:bg-yellow-900/10' : 'border-gray-300 hover:border-[#9a9563] bg-transparent'"
                                            @dragover.prevent="handleCsvDragOver"
                                            @dragleave.prevent="handleCsvDragLeave"
                                            @drop.prevent="handleCsvDrop"
                                            @click="openCsvPicker"
                                        >
                                            <UploadFile />

                                            <span v-if="!csvFileName" class="text-gray-600 dark:text-gray-300">Suelta tu archivo aquí</span>
                                            <span v-else class="text-green-600 dark:text-green-400 font-medium">{{ csvFileName }}</span>

                                            <input ref="csvInputRef" id="csv-file" type="file" accept=".csv,text/csv"
                                                class="hidden" @change="handleCsvFileChange" />
                                        </div>

                                        <!-- Botón Analizar -->
                                        <Button @click="analyze" :disabled="loading || !csvFile" class="mt-4 w-full" :variant="isDark ? 'default' : 'default'">
                                            {{ loading ? 'Procesando...' : 'Subir y Analizar' }}
                                        </Button>

                                        <!-- Barra de progreso -->
                                        <div v-if="loading" class="mt-3 w-full">
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded h-3 overflow-hidden">
                                                <div class="h-full bg-primary transition-all" :style="{ width: uploadProgress + '%' }"></div>
                                            </div>
                                            <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-1">Procesando archivo... {{ uploadProgress }}%</p>
                                        </div>
                                </div>

                <div v-else class="mt-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pega o escribe tus datos
                        <TooltipProvider>
                            <Tooltip>
                            <TooltipTrigger as-child>
                                <Info class="inline-block ml-1 h-4 w-4 text-gray-400 cursor-pointer" />
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Escribe tus números separados por espacios</p>
                            </TooltipContent>
                            </Tooltip>
                        </TooltipProvider>
                    </label>
                    <Textarea v-model="text" rows="8" class="w-full rounded-md border px-3 py-2 bg-transparent placeholder:text-gray-500 dark:placeholder:text-gray-400 text-gray-800 dark:text-gray-100"></textarea>
                </div>

                <div v-if="mode !== 'file'" class="flex items-center justify-between mt-4">
                    <!-- Botón Analizar -->
                    <Button @click="analyze" v-show="!loading" class="mt-4 w-full" :variant="isDark ? 'default' : 'default'">
                        {{ loading ? 'Procesando...' : 'Subir y Analizar' }}
                    </Button>

                    <!-- Barra de progreso -->
                    <div v-if="loading" class="mt-3 w-full">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded h-3 overflow-hidden">
                            <div class="h-full bg-primary transition-all" :style="{ width: uploadProgress + '%' }"></div>
                        </div>
                        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-1">Procesando datos... {{ uploadProgress }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <FooterComp />
    </div>
</template>