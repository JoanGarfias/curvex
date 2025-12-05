<template>
  <div class="space-y-5">
    <!-- 1-alpha -->
    <div id="alpha-input">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
        1 - α (Confianza del Productor)
        <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
      </label>
      <input
        :value="modelValue['1-alpha']"
        @input="$emit('update:modelValue', { ...modelValue, '1-alpha': ($event.target as HTMLInputElement).value })"
        type="number"
        step="0.01"
        min="0"
        max="1"
        :class="[
          'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
          'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
          errors['1-alpha'] ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
        ]"
        placeholder="Ej: 0.95"
        @focus="$emit('clearError', '1-alpha')"
      />
      <p v-if="errors['1-alpha']" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors['1-alpha'] }}</p>
    </div>

    <!-- Beta -->
    <div id="beta-input">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
        β (Riesgo del Consumidor)
        <span class="ml-1 text-gray-400 dark:text-gray-500 text-xs">0 - 1</span>
      </label>
      <input
        :value="modelValue.beta"
        @input="$emit('update:modelValue', { ...modelValue, beta: ($event.target as HTMLInputElement).value })"
        type="number"
        step="0.01"
        min="0"
        max="1"
        :class="[
          'w-full px-4 py-3 rounded-lg border-2 transition-colors outline-none',
          'dark:bg-gray-700 dark:text-white dark:placeholder-gray-400',
          errors.beta ? 'border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20' : 'border-gray-200 hover:border-gray-300 focus:border-black dark:border-gray-600 dark:hover:border-gray-500 dark:focus:border-white'
        ]"
        placeholder="Ej: 0.10"
        @focus="$emit('clearError', 'beta')"
      />
      <p v-if="errors.beta" class="text-red-500 dark:text-red-400 text-xs mt-1">{{ errors.beta }}</p>
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
