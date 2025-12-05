<template>
  <div class="space-y-6">
    <!-- Skeleton: Resultados numéricos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
      <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/3 mb-4"></div>
      
      <div class="grid grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-gray-200 dark:border-gray-600">
          <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-2/3 mx-auto mb-2"></div>
          <div class="h-10 bg-gray-200 dark:bg-gray-600 rounded w-16 mx-auto mb-2"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-600 rounded w-1/2 mx-auto"></div>
        </div>
      </div>

      <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
        <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-1/4 mb-2"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-600 rounded w-full"></div>
      </div>
    </div>

    <!-- Skeleton: Gráfica -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
      <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
      
      <div class="w-full h-80 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
        <svg class="w-16 h-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
        </svg>
      </div>

      <div class="mt-4 flex items-center justify-center gap-6">
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-24"></div>
      </div>
    </div>

    <!-- Skeleton: Tabla -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
      <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-2/3 mb-4"></div>
      
      <div class="space-y-3">
        <div class="flex gap-4">
          <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded flex-1"></div>
          <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded flex-1"></div>
          <div class="h-10 bg-gray-100 dark:bg-gray-700 rounded flex-1"></div>
        </div>
        <div v-for="i in 5" :key="i" class="h-8 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
      </div>
    </div>

    <!-- Skeleton: Grid 2 columnas -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
        <div class="space-y-3">
          <div v-for="i in 4" :key="i" class="h-10 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
        </div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 animate-pulse">
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-4"></div>
        <div class="space-y-3">
          <div v-for="i in 3" :key="i" class="h-6 bg-gray-50 dark:bg-gray-700/50 rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>
