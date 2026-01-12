<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;

class RegresionExponentialModel extends RegresionSolver {

    public function transformData() : void {
        foreach($this->data as $row_data){
            foreach($row_data as $x){
                $x = log10($x);
            }
        }

        foreach($this->dependent_data as $y){
            $y = log10($y);
        }

    }

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    public function calculateYModel(array $solutions, array $ind_term): float {
        $a = $solutions[0];
        $b = $solutions[1];

        return $a * exp($b * $ind_term[0]);
    }

    public function getName(): string {
        return "Exponencial";
    }
}