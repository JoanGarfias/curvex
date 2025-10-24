<script setup lang="ts">
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Sun, Moon } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const current = computed(() => appearance.value || 'system');

function toggle() {
  // toggle between light and dark; if system go to dark
  const next = current.value === 'dark' ? 'light' : 'dark';
  updateAppearance(next as any);
}
</script>

<template>
  <Button @click="toggle" variant="ghost" size="icon" class="p-2">
    <component :is="current === 'dark' ? Moon : Sun" class="h-5 w-5" />
  </Button>
</template>
