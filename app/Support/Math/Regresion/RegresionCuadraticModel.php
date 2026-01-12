<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;
use Exception;

class RegresionCuadraticModel extends RegresionSolver {

    public function transformData() : void {}

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    public function calculateYModel(array $solutions, array $ind_term): float {
        $x = $ind_term[0];
        return $solutions[0] + ($solutions[1] * $x) + ($solutions[2] * pow($x, 2));
    }

    //retorna un array de flotantes
    public function calculateXValue() : array {
        /**
         * (y / a) ^ (1 / b) = x
         */
        $y = $this->dependent_data[0];
        $a = $this->solutions[0] - $y;
        $b = $this->solutions[1];
        $c = $this->solutions[2];

        if(2.0 * $a == 0.0){
            throw new Exception("Se ha generado una división por cero, la solución 1 no puede ser 0");
        }

        $discriminant = pow($b, 2) - (4.0 * $a * $c);

        if($discriminant < 0.0){
            throw new Exception("Error: El discriminante para encontrar la solución de X ha sido calculado como cero");
            return [];
        }

        $sqrt = sqrt($discriminant);

        $x1 = (-$b + $sqrt) / (2.0 * $a);
        $x2 = (-$b - $sqrt) / (2.0 * $a);

        return [$x1, $x2];
    }

    public function getName(): string {
        return "Cuadrático";
    }
}