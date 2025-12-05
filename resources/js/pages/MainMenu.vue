<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import CurvexIcon from '@/icons/CurvexIcon.vue';
import FooterComp from '@/components/FooterComp.vue';
import { BarChart3, CheckCircle2, Calculator, ArrowRight } from "lucide-vue-next";

// Definición de las opciones del menú
const menuOptions = [
  {
    title: "Estadística Descriptiva & Chi²",
    description: "Calcula histogramas, tablas de frecuencia, varianza, deciles, cuartiles, percentiles y pruebas de Chi Cuadrada.",
    icon: BarChart3,
    route: '/estadistica-descriptiva',
    color: "text-gray-900 dark:text-gray-100",
    bg: "bg-gray-100 dark:bg-gray-800",
    hoverBorder: "hover:border-gray-400 dark:hover:border-gray-600"
  },
  {
    title: "Muestreo de Aceptación",
    description: "Evalúa lotes de productos. Determina si se aceptan o rechazan basándose en una muestra y estándares de calidad.",
    icon: CheckCircle2,
    route: '/muestreo-aceptacion',
    color: "text-gray-900 dark:text-gray-100",
    bg: "bg-gray-100 dark:bg-gray-800",
    hoverBorder: "hover:border-gray-400 dark:hover:border-gray-600"
  }
];
</script>

<template>
  <Head title="Curvex - Inicio" />

  <div class="min-h-screen flex flex-col items-center bg-gradient-to-br from-[#f8fafc] to-[#eef2f3] dark:from-[#0f0f0f] dark:to-[#1a1a1a] text-gray-800 dark:text-gray-100 transition-all p-4 sm:p-6">
    
    <nav class="w-full max-w-5xl flex items-center justify-between mb-8 sm:mb-12 px-2 sm:px-4">
      <div class="flex items-center gap-2 sm:gap-3">
        <CurvexIcon class="w-8 h-8 sm:w-10 sm:h-10 text-gray-900 dark:text-gray-100" />
        <span class="text-xl sm:text-2xl font-extrabold tracking-tight">Curvex</span>
      </div>
      <ThemeToggle />
    </nav>

    <header class="text-center mb-10 sm:mb-14 max-w-3xl px-4">
      <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-4">
        Herramientas de <span class="text-gray-900 dark:text-gray-100">Análisis</span>
      </h1>
      <p class="text-base sm:text-xl text-gray-500 dark:text-gray-400">
        Selecciona el módulo de probabilidad y estadística que deseas utilizar hoy.
      </p>
    </header>

    <main class="w-full max-w-5xl px-2 sm:px-4 flex-grow">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
        
        <Link 
          v-for="(option, index) in menuOptions" 
          :key="index" 
          :href="option.route"
          :class="`group relative flex flex-col p-6 sm:p-8 rounded-2xl bg-white/80 dark:bg-[#0b0b0b]/80 backdrop-blur border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 ${option.hoverBorder}`"
        >
          <div :class="`w-14 h-14 rounded-xl flex items-center justify-center mb-6 ${option.bg} ${option.color}`">
            <component :is="option.icon" class="w-7 h-7" stroke-width="2.5" />
          </div>

          <h2 class="text-2xl font-bold mb-3 group-hover:opacity-80 transition-opacity">
            {{ option.title }}
          </h2>
          
          <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6 flex-grow">
            {{ option.description }}
          </p>

          <div :class="`flex items-center text-sm font-semibold mt-auto ${option.color}`">
            <span>Seleccionar Herramienta</span>
            <ArrowRight class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1" />
          </div>
        </Link>

        <Link 
          href="/correccion-varianza"
          class="group md:col-span-2 relative flex flex-row items-center p-6 rounded-2xl bg-gradient-to-r from-gray-100 to-gray-50 dark:from-[#151515] dark:to-[#0f0f0f] border border-gray-200 dark:border-gray-800 hover:border-gray-400 transition-all"
        >
          <div class="p-3 bg-gray-200 dark:bg-gray-800 rounded-lg mr-4">
             <Calculator class="w-6 h-6 text-gray-600 dark:text-gray-300" />
          </div>
          <div>
             <h3 class="font-bold text-gray-800 dark:text-gray-200">¿Necesitas corrección de varianza?</h3>
             <p class="text-sm text-gray-500">Herramienta rápida para corregir datos a partir de una muestra.</p>
          </div>
          <ArrowRight class="w-5 h-5 ml-auto text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200" />
        </Link>

      </div>
    </main>

    <FooterComp class="mt-12" />
  </div>
</template>