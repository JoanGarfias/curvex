<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;
use Illuminate\Support\Facades\Log;

class RegresionPotentialModel extends RegresionSolver {

    protected bool $transformDataRequired = true;

    public function transformData() : void {
    
    // 1. Transformar $this->data
    foreach($this->data as $key => $variableData) {
        // Usamos array_map para aplicar log10 a todo el array de una vez
        // y reasignamos el resultado a la propiedad del objeto.
        $variableData->points = array_map('log10', $variableData->points);
    }

    // 2. Transformar $this->dependent_data
    // Si dependent_data es un array simple de números:
    foreach($this->dependent_data as $key => $y) {
        $this->dependent_data[$key] = log10($y);
    }

    
}

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    public function calculateYModel(array $solutions, array $ind_term): float {
        $a = $solutions[0];
        $b = $solutions[1];

        Log::info($ind_term);

        return $a * ($ind_term[0] ** $b);
    }

    public function getName(): string {
        return "Potencial";
    }
}