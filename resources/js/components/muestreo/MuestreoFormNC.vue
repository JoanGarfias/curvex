<template>
  <div class="space-y-5">
    <!-- n -->
    <div id="n-input">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
        n (Tamaño de Muestra)
        <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">≥ 2</span>
      </label>
      <input
        :value="modelValue.n"
        @input="$emit('update:modelValue', { ...modelValue, n: ($event.target as HTMLInputElement).value })"
        type="number"
        min="2"
        step="1"
        :class="[
          'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
          'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
          errors.n ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
        ]"
        placeholder="Ej: 50"
        @focus="$emit('clearError', 'n')"
      />
      <p v-if="errors.n" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.n }}</p>
    </div>

    <!-- c -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
        c (Criterio de Aceptación)
        <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 7</span>
      </label>
      <input
        :value="modelValue.c"
        @input="$emit('update:modelValue', { ...modelValue, c: ($event.target as HTMLInputElement).value })"
        type="number"
        min="0"
        max="7"
        step="1"
        :class="[
          'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
          'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
          errors.c ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
        ]"
        placeholder="Ej: 2"
        @focus="$emit('clearError', 'c')"
      />
      <p v-if="errors.c" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.c }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { FormData, FormErrors } from '@/types/muestreo';

defineProps<{
  modelValue: FormData;
  errors: FormErrors;
}>();

defineEmits<{
  'update:modelValue': [value: FormData];
  'clearError': [field: string];
}>();
</script>
