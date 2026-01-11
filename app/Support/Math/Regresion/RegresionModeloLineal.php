<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;

class RegresionModeloLineal extends RegresionSolver {

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    public function calculateYModel(array $solutions, array $ind_term): float {
        $y_model = $this->solutions[0];

        for($i=1; $i < $this->countVariables(); $i++){
            $y_model += $solutions[$i] + $ind_term[$i];
        }

        return $y_model;
    }

    public function getName(): string {
        return "Lineal";
    }
}