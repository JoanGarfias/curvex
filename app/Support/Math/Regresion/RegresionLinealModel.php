<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;
use Illuminate\Support\Facades\Log;

class RegresionLinealModel extends RegresionSolver {

    public function calculateYModel(array $solutions, array $ind_term): float {
        $y_model = $this->solutions[0];
        
        for($i=0; $i < $this->countVariables(); $i++){
            $y_model += $solutions[$i+1] * $ind_term[$i];
        }
        Log::info($y_model);

        return $y_model;
    }

    public function calculateXValue(): float {
    }

    public function getName(): string {
        return "Lineal";
    }
}