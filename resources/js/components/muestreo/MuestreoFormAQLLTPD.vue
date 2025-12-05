<template>
  <div class="space-y-5">
    <!-- AQL -->
    <div id="aql-input">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
        AQL (Nivel de Calidad Aceptable)
        <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
      </label>
      <input
        :value="modelValue.AQT"
        @input="$emit('update:modelValue', { ...modelValue, AQT: ($event.target as HTMLInputElement).value })"
        type="number"
        step="0.01"
        min="0"
        max="1"
        :class="[
          'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
          'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
          errors.AQT ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
        ]"
        placeholder="Ej: 0.02"
        @focus="$emit('clearError', 'AQT')"
      />
      <p v-if="errors.AQT" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.AQT }}</p>
    </div>

    <!-- LTPD -->
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
        LTPD (Tolerancia del Lote)
        <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
      </label>
      <input
        :value="modelValue.LTPD"
        @input="$emit('update:modelValue', { ...modelValue, LTPD: ($event.target as HTMLInputElement).value })"
        type="number"
        step="0.01"
        min="0"
        max="1"
        :class="[
          'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
          'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
          errors.LTPD ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
        ]"
        placeholder="Ej: 0.10"
        @focus="$emit('clearError', 'LTPD')"
      />
      <p v-if="errors.LTPD" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.LTPD }}</p>
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
