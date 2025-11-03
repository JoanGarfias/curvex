<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;

class StatisticService
{
    /**
     * Devuelve la suma de los números.
     *
     * @param array $numbers
     * @param int|null $count  (opcional) no usado pero mantenido por compatibilidad
     * @return float
     */
    public function suma(array $numbers, int $count = null): float
    {
        if (empty($numbers)) {
            return 0.0;
        }
        return array_sum($numbers);
    }
    
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
    public function obtenerVarianza(array $lista, int $n, float $promedio, int $modo): float
    {
        $res = 0.0;
        foreach ($lista as $valor) {
            $res += pow($valor - $promedio, 2);
        }
        
        if ($n === 0) return 0.0; // Evitar división por cero
        
        if($modo == 1){
            Log::info('Varianza muestral');
            return $res / ($n - 1); // Varianza muestral
        }else{
            Log::info('Varianza Poblacional');
            return $res / $n; // Varianza Poblacional
        }
    }

        /**
     * Calcula la desviación estándar muestral (como =DESVEST.M en Excel)
     */
    public function obtenerDesviacionEstandar(array $lista, int $n, float $promedio): float
    {
        if ($n <= 1) return 0.0;
        $suma = 0.0;
        foreach ($lista as $valor) {
            $suma += pow($valor - $promedio, 2);
        }
        // Excel usa "n - 1" para la desviación muestral
        return sqrt($suma / ($n - 1));
    }

    /**
     * Calcula el rango (MAX - MIN)
     */
    public function obtenerRango(float $maximo, float $minimo): float
    {
        return $maximo - $minimo;
    }

    /**
     * Calcula los límites superior e inferior
     * Superior = (Promedio + Rango) / 2
     * Inferior = (Promedio - Rango) / 2
     */
    public function obtenerLimites(float $promedio, float $rango): array
    {
        $limiteSuperior = ($promedio + $rango) / 2;
        $limiteInferior = ($promedio - $rango) / 2;
        return [
            'superior' => $limiteSuperior,
            'inferior' => $limiteInferior
        ];
    }

    /**
     * Calcula el número de intervalos
     * Fórmula Excel: =REDONDEAR(1 + 3.3 * LOG10(n), 0)
     */
    public function obtenerNumeroIntervalos(int $n): int
    {
        if ($n <= 0) return 0;
        return (int) round(1 + 3.3 * log10($n));
    }

    /**
     * Calcula el ancho de clase
     * Fórmula Excel: =REDONDEAR(Rango / Número de Intervalos, 3)
     */
    public function obtenerAnchoClase(float $rango, int $numIntervalos): float
    {
        if ($numIntervalos === 0) return 0.0;
        return round($rango / $numIntervalos, 3);
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

    public function obtenerPercentiles($lista, $cant): array
    {
        $n = count($lista);
        
        if ($n === 0) {
            return [];
        }
        
        sort($lista);
        $percentiles = array();

        for ($i = 1; $i < $cant; $i++) {
            $position = ($i / $cant) * ($n - 1); // Usar n-1 para índices basados en 0
            $lower = floor($position);
            $upper = ceil($position);
            
            // Verificar que los índices estén dentro de los límites
            if ($lower >= 0 && $upper < $n) {
                if ($lower == $upper) {
                    // Posición exacta
                    $percentiles[] = [$i => $lista[$lower]];
                } else {
                    // Interpolación lineal entre dos valores
                    $fraction = $position - $lower;
                    $value = $lista[$lower] + ($lista[$upper] - $lista[$lower]) * $fraction;
                    $percentiles[] = [$i => $value];
                }
            }
        }
        
        return $percentiles;
    }
}