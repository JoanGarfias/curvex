<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;

class RegresionCuadraticModel extends RegresionSolver {

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    public function calculateYModel(array $solutions, array $ind_term): float {
        $x = $ind_term[0];
        return $solutions[0] + ($solutions[1] * $x) + ($solutions[2] * pow($x, 2));
    }

    public function getName(): string {
        return "Cuadrático";
    }
}