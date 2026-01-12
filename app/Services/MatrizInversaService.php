<?php

namespace App\Services;

use Exception;
use InvalidArgumentException;

class MatrizInversaService
{
    /**
     * Calcula la inversa. Lanza excepciones si hay error.
     * Retorna array puro, NO una respuesta JSON.
     */
    public function calcular(array $matriz): array
    {
        if (!$this->esCuadrada($matriz)) {
            throw new InvalidArgumentException('La matriz debe ser cuadrada.');
        }

        return $this->aplicarGaussJordan($matriz);
    }

    private function esCuadrada(array $matriz): bool
    {
        $n = count($matriz);
        foreach ($matriz as $fila) {
            if (!is_array($fila) || count($fila) !== $n) {
                return false;
            }
        }
        return true;
    }

    private function aplicarGaussJordan(array $matriz): array
    {
        $n = count($matriz);
        // Crear matriz identidad
        $identidad = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $identidad[$i][$j] = ($i === $j) ? 1.0 : 0.0;
                $matriz[$i][$j] = (float) $matriz[$i][$j]; // Asegurar float
            }
        }

        // Algoritmo
        for ($i = 0; $i < $n; $i++) {
            $pivot = $matriz[$i][$i];

            // 1. Pivoteo (Buscar mejor pivote para estabilidad numérica)
            if (abs($pivot) < 1e-10) { 
                // Buscamos una fila debajo para intercambiar
                $intercambiado = false;
                for ($k = $i + 1; $k < $n; $k++) {
                    if (abs($matriz[$k][$i]) > 1e-10) {
                        // Intercambio de filas en Matriz y en Identidad
                        $temp = $matriz[$i];
                        $matriz[$i] = $matriz[$k];
                        $matriz[$k] = $temp;

                        $tempI = $identidad[$i];
                        $identidad[$i] = $identidad[$k];
                        $identidad[$k] = $tempI;
                        
                        $pivot = $matriz[$i][$i]; // Actualizamos pivote
                        $intercambiado = true;
                        break;
                    }
                }

                if (!$intercambiado) {
                    throw new Exception("La matriz es singular (no tiene inversa).");
                }
            }

            // 2. Normalizar la fila del pivote (hacer que el pivote sea 1)
            for ($j = 0; $j < $n; $j++) {
                $matriz[$i][$j] /= $pivot;
                $identidad[$i][$j] /= $pivot;
            }

            // 3. Hacer ceros en las otras filas
            for ($k = 0; $k < $n; $k++) {
                if ($k != $i) {
                    $factor = $matriz[$k][$i];
                    for ($j = 0; $j < $n; $j++) {
                        $matriz[$k][$j] -= $factor * $matriz[$i][$j];
                        $identidad[$k][$j] -= $factor * $identidad[$i][$j];
                    }
                }
            }
        }

        return $identidad;
    }
}