<?php

namespace App\Support\Math\Regresion;

use App\Support\Math\RegresionSolver;
use Exception;
use Illuminate\Support\Facades\Log;

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
         * Resolver ax² + bx + c = 0
         * donde: solutions[2]x² + solutions[1]x + (solutions[0] - y) = 0
         */
        $y = $this->dependent_data[0];
        $a = $this->solutions[2];  // coeficiente de x²
        $b = $this->solutions[1];  // coeficiente de x
        $c = $this->solutions[0] - $y;  // término constante

        Log::info("Calculando valor de X para Y = $y usando modelo cuadrático");
        Log::debug("Ecuación: 0 = {$a}x^2 + {$b}x + ({$c})");

        if(2.0 * $a == 0.0){
            throw new Exception("Se ha generado una división por cero, el coeficiente de x² no puede ser 0");
        }

        $discriminant = pow($b, 2) - (4.0 * $a * $c);

        if($discriminant < 0.0){
            throw new Exception("Error: El discriminante para encontrar la solución de X ha sido calculado como cero");
            return [];
        }

        Log::debug("Discriminante: $discriminant");

        $sqrt = sqrt($discriminant);

        Log::debug("Raíz cuadrada del discriminante: $sqrt");

        $x1 = ((-1.0 * $b) + $sqrt) / (2.0 * $a);
        $x2 = ((-1.0 * $b) - $sqrt) / (2.0 * $a);

        Log::info("Valores calculados de X: x1 = $x1, x2 = $x2");

        return [$x1, $x2];
    }

    public function getName(): string {
        return "Cuadrático";
    }
}