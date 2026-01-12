<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;
use Exception;
use Illuminate\Support\Facades\Log;

class RegresionLinealModel extends RegresionSolver {

    public function transformData() : void {}

    public function calculateYModel(array $solutions, array $ind_term): float {
        $y_model = $this->solutions[0];
        
        for($i=0; $i < $this->countVariables(); $i++){
            $y_model += $solutions[$i+1] * $ind_term[$i];
        }
        Log::info($y_model);

        return $y_model;
    }

    public function calculateXValue(): float {
        /**
         * (y - a) / b = x
         */
        $y = $this->dependent_data[0];
        $a = $this->solutions[0];
        $b = $this->solutions[1];
        
        if($b == 0.0){
            throw new Exception("El cálculo del valor de X ha generado una divisón por cero, la solución 2 no puede ser 0");
        }

        return ($y - $a) / $b;
    }

    public function getName(): string {
        return "Lineal";
    }
}