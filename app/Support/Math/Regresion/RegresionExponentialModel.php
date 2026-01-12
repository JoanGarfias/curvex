<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;
use Exception;

class RegresionExponentialModel extends RegresionSolver {

    public function transformData() : void {
    
    // 1. Transformar $this->data
    /*foreach($this->data as $key => $variableData) {
        // Usamos array_map para aplicar log a todo el array de una vez
        // y reasignamos el resultado a la propiedad del objeto.
        $variableData->points = array_map('log', $variableData->points);
    }*/

    // 2. Transformar $this->dependent_data
    // Si dependent_data es un array simple de números:
    foreach($this->dependent_data as $key => $y) {
        $this->dependent_data[$key] = log($y);
    }
}

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    public function calculateYModel(array $solutions, array $ind_term): float {
        $a = $solutions[0];
        $b = $solutions[1];

        return $a * exp($b * $ind_term[0]);
    }

    public function calculateXValue(): float {
        /**
         * (y / a) ^ (1 / b) = x
         */
        
        $y = $this->dependent_data[0];
        $a = $this->solutions[0];
        $b = $this->solutions[1];

        if($a == 0.0){
            throw new Exception("Se ha generado una división por cero, la solución 1 no puede ser 0");
        }

        return pow($y/$a, 1.0/$b);
    }

    public function getName(): string {
        return "Exponencial";
    }
}