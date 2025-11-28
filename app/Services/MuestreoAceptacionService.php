<?php

namespace App\Services;

use App\Services\UtilsService as Utils;

class MuestreoAceptacionService {
    public function binomDistAcum(int $n, int $k, int $p) : float{
        $binomAcum = 0.00;
        for($i = 0; $i <= $k; $i++){
            $binomAcum += 1.0 * $this->binomDist($n, $i) * pow($p, $i) * pow(1.0-$p, $n-$i);
        }
        return $binomAcum;
    }

    public function binomDist($n, $i){
        return (1.0 * Utils::factorial($n)) / (Utils::factorial($i) * Utils::factorial($n - $i));
    }

}