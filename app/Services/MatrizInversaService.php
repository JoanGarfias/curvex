<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MatrizInversaService{
    public function inversa(Request $request)
    {
        $matriz = $request->input('matriz');

        if (!$this->esCuadrada($matriz)) {
            return response()->json([
                'error' => 'La matriz no es cuadrada'
            ], 400);
        }

        $resultado = $this->inversaMatriz($matriz);

        if (is_string($resultado)) {
            return response()->json([
                'error' => $resultado
            ], 400);
        }

        return response()->json([
            'inversa' => $resultado
        ]);
    }

    private function esCuadrada($matriz)
    {
        $n = count($matriz);
        foreach ($matriz as $fila) {
            if (count($fila) !== $n) {
                return false;
            }
        }
        return true;
    }

    private function inversaMatriz($matriz)
    {
        $n = count($matriz);

        // Matriz identidad
        $I = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $I[$i][$j] = ($i === $j) ? 1 : 0;
            }
        }

        // Convertir a float
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $matriz[$i][$j] = (float) $matriz[$i][$j];
            }
        }

        // Gauss-Jordan con pivoting
        for ($i = 0; $i < $n; $i++) {

            if ($matriz[$i][$i] == 0) {
                for ($k = $i + 1; $k < $n; $k++) {
                    if ($matriz[$k][$i] != 0) {
                        [$matriz[$i], $matriz[$k]] = [$matriz[$k], $matriz[$i]];
                        [$I[$i], $I[$k]] = [$I[$k], $I[$i]];
                        break;
                    }
                }
            }

            if ($matriz[$i][$i] == 0) {
                return "La matriz no tiene inversa";
            }

            $div = $matriz[$i][$i];
            for ($j = 0; $j < $n; $j++) {
                $matriz[$i][$j] /= $div;
                $I[$i][$j] /= $div;
            }

            for ($k = 0; $k < $n; $k++) {
                if ($k != $i) {
                    $factor = $matriz[$k][$i];
                    for ($j = 0; $j < $n; $j++) {
                        $matriz[$k][$j] -= $factor * $matriz[$i][$j];
                        $I[$k][$j] -= $factor * $I[$i][$j];
                    }
                }
            }
        }

        return $I;
    }
}