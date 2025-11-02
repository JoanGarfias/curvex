<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { useAppearance } from '@/composables/useAppearance';

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
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11.2756 6.74162C7.23776 6.79162 6.88122 6.79996 6.50684 6.94996C5.53525 7.34162 4.78651 8.04162 4.48344 8.84996C4.33191 9.26662 4.323 9.74996 4.323 17.625V25.9583L4.52801 26.4333C4.81325 27.1 5.41046 27.6833 6.13247 28.0166L6.72968 28.2916H16.1782H25.6266L26.1614 28.0916C26.8924 27.8166 27.7035 27.0583 27.9977 26.375L28.2116 25.875L28.2383 17.8833C28.2561 12.6416 28.2294 9.73329 28.167 9.42496C27.9263 8.21662 26.8745 7.14162 25.6266 6.84996C25.1631 6.74162 16.9893 6.68329 11.2756 6.74162ZM25.796 7.99996C26.2684 8.22496 26.5536 8.50829 26.8567 9.03329L27.0528 9.37496L27.0795 16.5583C27.0974 22.7666 27.0795 23.75 26.9726 23.75C26.7497 23.75 26.0456 23.4333 25.5107 23.0916C24.4768 22.425 23.3893 21.2 22.4177 19.5916C22.0255 18.9416 20.0556 15.075 20.0556 14.95C20.0556 14.9333 20.3141 14.9166 20.635 14.9166H21.2144V13V11.0833H20.4567H19.699V12.7166V14.3583L19.2623 13.725C18.674 12.8916 17.8717 12.15 17.1854 11.8166C16.6684 11.5666 16.5525 11.5416 15.8216 11.5416C15.0907 11.5416 14.957 11.5666 14.4311 11.8083C13.1208 12.4166 12.3096 13.475 10.8745 16.5C8.98484 20.4916 7.81715 22.1416 6.16812 23.175L5.48177 23.6L5.45503 16.625L5.4372 9.64163L5.66896 9.17496C5.93637 8.61662 6.1503 8.39162 6.68512 8.09996L7.08623 7.87496L12.8355 7.83329C15.9999 7.80829 20.1091 7.78329 21.972 7.79162H25.3592L25.796 7.99996ZM16.5971 12.7833C17.5598 13.2083 18.3174 14.2 19.6188 16.7166C21.9185 21.175 22.079 21.4333 23.1665 22.6416C24.0489 23.6166 25.3414 24.4333 26.5982 24.8L27.0974 24.95V25.325C27.0885 26.0166 26.5893 26.7166 25.8584 27.0583C25.5642 27.2 25.1542 27.2083 20.8846 27.2333L16.2227 27.2583L16.2049 20.575C16.1782 14.3166 16.1692 13.8833 16.0266 13.8C15.9107 13.7333 15.8216 13.7333 15.7146 13.8C15.5631 13.8833 15.5542 14.3166 15.5275 20.575L15.5096 27.25H11.2756C7.9152 27.25 6.97927 27.225 6.74751 27.1416C5.97202 26.8333 5.4372 26.0666 5.4372 25.25V24.7916L6.11464 24.4583C7.05057 24 8.12913 23.1666 8.77982 22.4C9.64445 21.375 10.3486 20.1916 11.5787 17.6833C12.8623 15.0583 13.3882 14.1833 14.0656 13.4916C14.9124 12.6333 15.7325 12.4 16.5971 12.7833Z" fill="#1EAAA7"/>
                <path d="M22.0611 9.26666C22.0343 9.32499 22.0254 10.6167 22.0343 12.125L22.0611 14.875L22.7474 14.9C23.4071 14.925 23.4427 14.9167 23.4873 14.7333C23.514 14.625 23.5229 13.3417 23.514 11.875L23.4873 9.20832L22.792 9.18332C22.2928 9.16666 22.0878 9.19166 22.0611 9.26666Z" fill="#1EAAA7"/>
                <path d="M24.2003 12.3333C24.1647 12.4333 24.1647 13.0333 24.2003 13.675L24.2716 14.8417L24.9936 14.8167L25.7156 14.7917V13.5V12.2083L24.9847 12.1833C24.3162 12.1583 24.2538 12.175 24.2003 12.3333Z" fill="#1EAAA7"/>
                </svg>
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
                <select v-model="mode" class="w-full sm:w-64 rounded-md border px-3 py-2 bg-transparent text-gray-700 dark:text-gray-200 border-gray-300 dark:border-gray-700">
                    <option value="file">Archivo CSV</option>
                    <option value="text">Texto</option>
                </select>

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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-500 dark:text-gray-300 mb-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 15a4 4 0 014-4h10a4 4 0 010 8H7a4 4 0 01-4-4zM12 12V3m0 0l3.293 3.293M12 3L8.707 6.293" />
                                            </svg>

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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pega o escribe tus datos</label>
                    <textarea v-model="text" rows="8" class="w-full rounded-md border px-3 py-2 bg-transparent placeholder:text-gray-500 dark:placeholder:text-gray-400 text-gray-800 dark:text-gray-100"></textarea>
                </div>

                <div v-if="mode !== 'file'" class="flex items-center justify-between mt-4">
                    <Button :disabled="loading || !hasInput" :variant="isDark ? 'outline' : 'default'" @click="analyze">
                        <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A2 2 0 009 10.07v3.86a2 2 0 002.555 1.884l3.197-1.066A2 2 0 0016 12.414V12a2 2 0 00-1.248-1.832z"/></svg>
                        <span v-if="!loading">Analizar</span>
                        <span v-else class="inline-flex items-center gap-2">Procesando <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg></span>
                    </Button>

                    <!-- Progress / spinner when loading -->
                    <div class="w-2/3">
                        <div v-if="loading" class="w-full bg-gray-200 dark:bg-gray-700 rounded h-3 overflow-hidden">
                            <div class="h-full w-full bg-primary/60 animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-10 mb-6 text-sm text-gray-500 dark:text-gray-400 text-center">
            Desarrollado con ❤️ por Jere, Juan, Joan, Gera y Karlis · Laravel + Vue 3 + TailwindCSS · 2025
        </footer>
    </div>
</template>