<?php

namespace App\Services;

use App\Services\UtilsService as Utils;

class MuestreoAceptacionService {
    /**
     * Calcula la distribución binomial acumulativa.
     * Equivalente a BINOM.DIST(k, n, p, TRUE) en Excel.
     * 
     * @param int $n Número de ensayos
     * @param int $k Número de éxitos (calcula P(X ≤ k))
     * @param float $p Probabilidad de éxito en cada ensayo
     * @return float Probabilidad acumulada
     */
    public function binomDistAcum(int $n, int $k, float $p) : float{
        $binomAcum = 0.0;
        
        // Suma de probabilidades desde i=0 hasta i=k
        for($i = 0; $i <= $k; $i++){
            $coefBinomial = $this->coeficienteBinomial($n, $i);
            $probabilidad = $coefBinomial * pow($p, $i) * pow(1.0 - $p, $n - $i);
            $binomAcum += $probabilidad;
        }
        
        return $binomAcum;
    }

    /**
     * Calcula la distribución binomial puntual.
     * Equivalente a BINOM.DIST(k, n, p, FALSE) en Excel.
     * 
     * @param int $n Número de ensayos
     * @param int $k Número de éxitos
     * @param float $p Probabilidad de éxito
     * @return float Probabilidad puntual P(X = k)
     */
    public function binomDist(int $n, int $k, float $p) : float{
        $coefBinomial = $this->coeficienteBinomial($n, $k);
        return $coefBinomial * pow($p, $k) * pow(1.0 - $p, $n - $k);
    }

    /**
     * Calcula el coeficiente binomial C(n, k) = n! / (k! * (n-k)!)
     * Optimizado para evitar desbordamiento con factoriales grandes.
     * 
     * @param int $n Total de elementos
     * @param int $k Elementos a seleccionar
     * @return float Coeficiente binomial
     */
    private function coeficienteBinomial(int $n, int $k) : float{
        // Optimización: C(n,k) = C(n, n-k), usar el menor
        if ($k > $n - $k) {
            $k = $n - $k;
        }
        
        // Casos base
        if ($k == 0 || $k == $n) {
            return 1.0;
        }
        
        // Cálculo iterativo para evitar desbordamiento
        // C(n,k) = (n * (n-1) * ... * (n-k+1)) / (k * (k-1) * ... * 1)
        $resultado = 1.0;
        for ($i = 0; $i < $k; $i++) {
            $resultado *= ($n - $i);
            $resultado /= ($i + 1);
        }
        
        return $resultado;
    }

}