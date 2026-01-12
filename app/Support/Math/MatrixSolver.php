<?php

namespace App\Support\Math;

use App\ValueObjects\Matrix;
use Exception; // 1. Importar Exception

class MatrixSolver
{
    // Eliminamos el constructor innecesario

    public static function multiply(Matrix $a, Matrix $b): Matrix 
    {
        // Validación de dimensiones: Columnas de A == Filas de B
        if ($a->cols != $b->rows) {
            throw new Exception("Dimensiones incompatibles: A({$a->rows}x{$a->cols}) y B({$b->rows}x{$b->cols})");
        }

        $resultData = [];

        // 2. Necesitamos 3 bucles anidados
        // i: recorre filas de A
        for ($i = 0; $i < $a->rows; $i++) {
            
            // j: recorre columnas de B
            for ($j = 0; $j < $b->cols; $j++) {
                
                $sum = 0;
                
                // k: recorre las columnas de A (que son las filas de B)
                // Aquí ocurre el producto punto
                for ($k = 0; $k < $a->cols; $k++) {
                    // C[i][j] += A[i][k] * B[k][j]
                    $sum += $a->data[$i][$k] * $b->data[$k][$j];
                }

                $resultData[$i][$j] = $sum;
            }
        }

        // Retornamos una nueva matriz con las dimensiones correctas
        // Filas de A x Columnas de B
        return new Matrix($resultData, $a->rows, $b->cols);
    }
}