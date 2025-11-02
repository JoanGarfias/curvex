<?php

namespace App\Services;

class StatisticService
{
    /**
     * Calculate the average of a list of numbers.
     *
     * @param array $numbers
     * @param int $count
     * @return float
     */
    public function promedio(array $numbers, int $count): float
    {
        if ($count === 0) {
            return 0;
        }
        return array_sum($numbers) / $count;
    }

    /**
     * Get the minimum value from a list of numbers.
     *
     * @param array $numbers
     * @param int $count
     * @return float
     */
    public function valormin(array $numbers, int $count): float
    {
        if ($count === 0) {
            return 0;
        }
        return min($numbers);
    }

    /**
     * Get the maximum value from a list of numbers.
     *
     * @param array $numbers
     * @param int $count
     * @return float
     */
    public function valormax(array $numbers, int $count): float
    {
        if ($count === 0) {
            return 0;
        }
        return max($numbers);
    }

    /**
     * Calcula la Varianza Poblacional (divide entre 'n').
     * Equivalente a tu función obtenerVarianza().
     */
    public function obtenerVarianza(array $lista, int $n, float $promedio): float
    {
        $res = 0.0;
        foreach ($lista as $valor) {
            $res += pow($valor - $promedio, 2);
        }
        
        if ($n === 0) return 0.0; // Evitar división por cero
        
        return $res / $n; // Varianza Poblacional
    }

    /**
     * Calcula la Curtosis Excesiva.
     * Equivalente a tu función obtenerCurtosis().
     */
    public function obtenerCurtosis(array $lista, int $n, float $promedio, float $desviacion): float
    {
        if ($desviacion == 0) {
            // Si la desviación es 0, todos los valores son iguales.
            // La curtosis no está definida (división por cero).
            // Devolvemos 0 o null según la lógica de negocio.
            return 0.0;
        }
        
        $res = 0.0;
        foreach ($lista as $valor) {
            $res += pow($valor - $promedio, 4);
        }
        
        $denominador = ($n * pow($desviacion, 4));

        if ($denominador == 0) return 0.0; // Doble chequeo
        
        // (res / denominador) - 3 es la "Curtosis Excesiva"
        return ($res / $denominador) - 3;
    }

    public function obtenerPercentiles($lista,$cant): array
    {
        
        $n = count($lista);
        sort($lista);
        $percentiles = array();

        $i = 1;
        while($i < $cant){
            $dummy = ($i/$cant) * $n;
            if($dummy % 2 == 0){
                //Log::debug($cant." impar ".$i."  ".$dummy);
                array_push($percentiles, ["".$i => ($lista[$dummy]+$lista[$dummy+1])/2]);
            }else{
                //Log::debug($cant." par ".$i."  ".$dummy);
                array_push($percentiles, ["".$i => $lista[$dummy]]);
            }
            $i+=1;
        }
        
        return $percentiles;
    }
}