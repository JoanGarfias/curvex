<?php

namespace App\Math;
use App\ValueObjects\Matrix;
use App\ValueObjects\Solution2VSystem;

class CrammerSolver {
    public static function solveCrammerMatrix2X2(Matrix $m) : Solution2VSystem{
        $terms = $m->getConstantTerms();

        //Calcular delta general
        $delta_matrix = new Matrix(
        [
            [ $m[0][0], $m[0][1]  ],
            [ $m[1][0], $m[1][1]  ],
        ], 2, 3);
        $delta_general = $delta_matrix->calculateDeterminant();


        //Calcular delta X
        $delta_x_matrix = new Matrix(
        [
            [ $terms[0], $m[0][1]  ],
            [ $terms[1], $m[1][1]  ],  
        ], 2, 2);
        $delta_x = $delta_x_matrix->calculateDeterminant();


        //Calcular delta Y
        $delta_y_matrix = new Matrix(
        [
            [ $m[0][0], $terms[0]  ],
            [ $m[1][0], $terms[1]  ],  
        ], 2, 2);
        $delta_y = $delta_y_matrix->calculateDeterminant();


        //Calcular soluciones del sistema
        $x = $delta_x / $delta_general;
        $y = $delta_y / $delta_general;


        //Retornar resultado con tipado
        $solution = new Solution2VSystem($x, $y);

        return $solution;
    }
}