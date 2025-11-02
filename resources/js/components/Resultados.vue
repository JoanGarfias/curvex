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

import { ref, toRef } from 'vue';
import type { Resultado } from '@/types/Resultado';

interface Props{
  resultado: Resultado;
}

const props = defineProps<Props>();
const resultado = toRef(props, 'resultado');

const emit = defineEmits<{
  close: []
}>();

const open = ref(true);

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
      <div class="grid gap-4 py-4">
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="promedio">Promedio</Label>
          <Input id="promedio" type="text" :value="resultado.mean" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="valmin">Valor Mínimo</Label>
          <Input id="valmin" type="text" :value="resultado.min" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="valmax">Valor Máximo</Label>
          <Input id="valmax" type="text" :value="resultado.max" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="rango">Rango</Label>
          <Input id="rango" type="text" :value="resultado.range" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="varianza">Varianza</Label>
          <Input id="varianza" type="text" :value="resultado.variance" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="desviacionEstandar">Desviación Estándar</Label>
          <Input id="desviacionEstandar" type="text" :value="resultado.standardDeviation" readonly />
        </div>
        <div class="grid grid-cols-2 items-center gap-4">
          <Label for="curtosis">Curtosis</Label>
          <Input id="curtosis" type="text" :value="resultado.kurtosis" readonly />
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