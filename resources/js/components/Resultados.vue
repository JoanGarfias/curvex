<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Button } from "@/components/ui/button"

import { ref, toRef, computed } from 'vue';
import type { Resultado } from '@/types/Resultado';

interface Props{
  resultado: Resultado;
}

const props = defineProps<Props>();
const resultado = toRef(props, 'resultado');

const emit = defineEmits<{
  close: []
}>();

const open = ref<boolean>(true);
const decimales = ref<number>(8);

// Función para truncar (cortar) sin redondear
const truncate = (num: number, decimals: number): string => {
  const multiplier = Math.pow(10, decimals);
  const truncated = Math.trunc(num * multiplier) / multiplier;
  return truncated.toFixed(decimals);
};

// Computed properties para formatear los valores reactivamente (truncados, no redondeados)
const promedio = computed(() => truncate(resultado.value.mean, Number(decimales.value)));
const minimo = computed(() => truncate(resultado.value.min, Number(decimales.value)));
const maximo = computed(() => truncate(resultado.value.max, Number(decimales.value)));
const rango = computed(() => truncate(resultado.value.range, Number(decimales.value)));
const varianza = computed(() => truncate(resultado.value.variance, Number(decimales.value)));
const desviacionEstandar = computed(() => truncate(resultado.value.standardDeviation, Number(decimales.value)));
const curtosis = computed(() => truncate(resultado.value.kurtosis, Number(decimales.value)));

function handleClose() {
  open.value = false;
  emit('close');
}

</script>

<template>
  <Dialog v-model:open="open" @update:open="(val) => !val && handleClose()">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Resultados</DialogTitle>
        <DialogDescription>
          Aquí están los resultados de su análisis.
        </DialogDescription>
      </DialogHeader>

      <div class="flex flex-row align-center justify-between">
        <Label for="decimales">Número de decimales</Label>
        <Input v-model.number="decimales" class="w-16" id="decimales" type="number" label="Número de decimales" min="1" max="10" />
      </div>

      <div class="grid gap-4 py-4">
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="promedio">Promedio</Label>
          <Input id="promedio" type="text" :value="promedio" :defaultValue="promedio" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="valmin">Valor Mínimo</Label>
          <Input id="valmin" type="text" :value="minimo" :defaultValue="minimo" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="valmax">Valor Máximo</Label>
          <Input id="valmax" type="text" :value="maximo" :defaultValue="maximo" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="rango">Rango</Label>
          <Input id="rango" type="text" :value="rango" :defaultValue="rango" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="varianza">Varianza</Label>
          <Input id="varianza" type="text" :value="varianza" :defaultValue="varianza" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="desviacionEstandar">Desviación Estándar</Label>
          <Input id="desviacionEstandar" type="text" :value="desviacionEstandar" :defaultValue="desviacionEstandar" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="curtosis">Curtosis</Label>
          <Input id="curtosis" type="text" :value="curtosis" :defaultValue="curtosis" readonly />
        </div>
      </div>
      <DialogFooter>
        <Button type="submit">
          Descargar resultados
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

</template>