<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FrequencyService
{
    /**
     * Genera la tabla de frecuencias completa.
     * 
     * @param array $lista Array de números
     * @return array Tabla de frecuencias con info de intervalos
     */
    public function generateFrequencyTable(array $lista, float $promedio): array
    {
        $n = count($lista);
        
        if ($n === 0) {
            return [
                'info_intervalos' => [],
                'tabla_frecuencias' => []
            ];
        }

        $minimo = min($lista);
        $maximo = max($lista);
        $rango = $maximo - $minimo;
        
        // Validar que haya un rango (evita división por cero si todos los números son iguales)
        if ($rango < 0.0001) {
            return [
                'info_intervalos' => [
                    'numero_intervalos' => 0,
                    'ancho_intervalo' => 0,
                    'mensaje' => 'Los valores tienen muy poca variabilidad para generar tabla de frecuencias'
                ],
                'tabla_frecuencias' => []
            ];
        }

        // Regla de Sturges: numIntervalos = 1 + 3.3 * log10(n)
        $numIntervalos = (int) round(1 + 3.3 * log10($n));
        $ancho = $rango / $numIntervalos;

        // Preparar lista con flags
        $listaConFlags = $this->prepararListaConFlags($lista);

        // Calcular límites de cada intervalo
        $limites = [];
        for ($i = 0; $i < $numIntervalos; $i++) {
            $lim_inf = $minimo + ($i * $ancho);
            $lim_sup = $lim_inf + $ancho;
            $limites[] = [$lim_inf, $lim_sup];
        }

        // Obtener frecuencias
        $frecuencias = $this->obtenerFrecuencias($listaConFlags, $limites, $n, $numIntervalos);

        // Construir tabla de resultados
        $tablaResultados = $this->construirTablaResultados($limites, $frecuencias, $numIntervalos, $n, $promedio);

        return [
            'info_intervalos' => [
                'numero_intervalos' => $numIntervalos,
                'ancho_intervalo' => round($ancho, 3),
            ],
            'tabla_frecuencias' => $tablaResultados
        ];
    }

    /**
     * Prepara la lista con flags para marcar elementos ya contados.
     * Equivalente a prepararMatriz() de Java.
     * 
     * @param array $lista
     * @return array
     */
    private function prepararListaConFlags(array $lista): array
    {
        $listaConFlags = [];
        foreach ($lista as $valor) {
            $listaConFlags[] = ['value' => $valor, 'counted' => false];
        }
        return $listaConFlags;
    }

    /**
     * Calcula las frecuencias para cada intervalo.
     * Equivalente a obtenerFrecuencias() de Java.
     * 
     * @param array $listaConFlags Array con valores y flags (pasado por referencia)
     * @param array $limites Array de límites [inferior, superior] para cada intervalo
     * @param int $n Número total de elementos
     * @param int $numIntervalos Número de intervalos
     * @return array Frecuencias para cada intervalo
     */
    private function obtenerFrecuencias(array &$listaConFlags, array $limites, int $n, int $numIntervalos): array
    {
        $frecuencias = array_fill(0, $numIntervalos, 0);

        for ($i = 0; $i < $numIntervalos; $i++) {
            $elems = 0;
            $lim_inf = $limites[$i][0];
            $lim_sup = $limites[$i][1];

            for ($j = 0; $j < $n; $j++) {
                if (round($listaConFlags[$j]['value'], 8) >= round($lim_inf, 8) &&
                    round($listaConFlags[$j]['value'], 8) <= round($lim_sup, 8) &&
                    !$listaConFlags[$j]['counted'])
                {
                    $elems += 1;
                    $listaConFlags[$j]['counted'] = true;
                }
            }
            $frecuencias[$i] = $elems;
        }

        return $frecuencias;
    }

    /**
     * Construye la tabla de resultados con todas las columnas.
     * 
     * @param array $limites
     * @param array $frecuencias
     * @param int $numIntervalos
     * @param int $n Total de elementos
     * @return array
     */
    private function construirTablaResultados(array $limites, array $frecuencias, int $numIntervalos, int $n, float $promedio): array
    {
        // Usar la implementación local de la CDF normal (desviación por defecto = 2)

        $tablaResultados = [];
        $frecAcumulada = 0;
        
        $prec_limites = 3; // Precisión para redondear límites
        $prec_pct = 2;     // Precisión para redondear porcentajes

        for ($i = 0; $i < $numIntervalos; $i++) {
            $frec = $frecuencias[$i];
            $frecAcumulada += $frec;

            $lim_inf = $limites[$i][0];
            $lim_sup = $limites[$i][1];
            $marca_clase = ($lim_inf + $lim_sup) / 2;
            
            $frec_rel_pct = ($n > 0) ? ($frec / $n) * 100 : 0;
            $frec_rel_acum_pct = ($n > 0) ? ($frecAcumulada / $n) * 100 : 0;

            $prob_li = self::normDistAcumulado($lim_inf, $promedio, 2);
            $prob_sup = self::normDistAcumulado($lim_sup, $promedio, 2);
            $prob_total = $prob_sup - $prob_li;
            $esp = $prob_total * $n;
            
            // Evitar división por cero en chisinsuma
            $chisinsuma = 0;
            if ($esp > 0.0001) {
                $chisinsuma = ((((int) $frec) - $esp)**2) / $esp;
            }

            $tablaResultados[] = [
                'clase' => "Clase " . ($i + 1),
                'limite_inferior' => round($lim_inf, $prec_limites),
                'limite_superior' => round($lim_sup, $prec_limites),
                'marca_de_clase' => round($marca_clase, $prec_limites),
                'frecuencia_absoluta' => (int) $frec,
                'frecuencia_abs_acumulada' => (int) $frecAcumulada,
                'frecuencia_relativa_pct' => round($frec_rel_pct, $prec_pct),
                'frecuencia_rel_acumulada_pct' => round($frec_rel_acum_pct, $prec_pct),
                'prob_li' => $prob_li,
                'prob_ls' => $prob_sup,
                'prob_total' => $prob_total,
                'esperado' => $esp,
                'chisinsuma' => $chisinsuma,
            ];
        }

        return $tablaResultados;
    }


    public function generateChiData(array $tablaFrecuencias, int $promedio, $desviacionEstandar, $cantidadDatos, $numeroDeClases){
        //alpha es el nivel de significancia, este lo da el usuario.
        //Recorro el array de tablas de frecuencias para obtener limite inferior, superior, frecuencia absoluta, etc
        
        // Validar desviación estándar para evitar división por cero
        if ($desviacionEstandar < 0.0000001 || empty($tablaFrecuencias)) {
            return [
                "chicua" => null,
                "chicua_statistic" => null,
                "statistic" => null,
                "grados_libertad" => 0,
                "degrees_of_freedom" => 0,
                "grados_libertad_raw" => 0,
                "p_value" => null,
                "critical_0_95" => null,
                "critical_0_99" => null,
                "chi_inverso" => null,
                "chi_inverse" => null,
                "mensaje" => "No se puede calcular Chi-cuadrado: desviación estándar demasiado pequeña o sin datos"
            ];
        }
        
        //Chi cuadrada
        $sumaChiCua = 0.0;
        
        Log::info("Cálculo de Chi-cuadrado iniciado.");

        foreach($tablaFrecuencias as $claseData){
            //Calcular la probabilidad de que X esté entre los límites
            $normDistLimiteInferior = self::normDistAcumulado(
                $claseData['limite_inferior'],
                $promedio,
                round($desviacionEstandar,8)
            );

            $normDistLimiteSuperior = self::normDistAcumulado(
                $claseData['limite_superior'],
                $promedio,
                round($desviacionEstandar,8)
            );

            Log::info("Limite Inferior: " . $claseData['limite_inferior'] . " - NormDist: " . $normDistLimiteInferior);
            Log::info("Limite Superior: " . $claseData['limite_superior'] . " - NormDist: " . $normDistLimiteSuperior);
            
            $probabilidadLimites = $normDistLimiteSuperior - $normDistLimiteInferior;

            //SumaChi = (Oi-Ei)² / Ei 
            //Ei = ProbabilidadLimites * cantidad de datos
            //Oi = Frecuencia absoluta
            $Ei = $probabilidadLimites * $cantidadDatos;
            $Oi = $claseData["frecuencia_absoluta"];
            
            // Evitar división por cero: solo agregar al chi-cuadrado si Ei es mayor que un umbral mínimo
            if ($Ei > 0.0001) {
                Log::info("Oi: " . $Oi . " - Ei: " . $Ei);
                $sumaChiCua += (pow(1.0 * ($Oi-$Ei), 2) ) / $Ei;
            }
        }

        // Grados de libertad: usar número de clases (ajustado según expectativa del usuario)
        $gradosDeLibertad = max(1, (int) $numeroDeClases);

        // Calcular p-valor y valores críticos (chi inversa para p acumulada 0.95 y 0.99)
        $pValue = null;
        $critical95 = null;
        $critical99 = null;
        try {
            $pValue = 1.0 - $this->chiCdf($sumaChiCua, $gradosDeLibertad);
            $critical95 = $this->chiInverse(0.95, $gradosDeLibertad);
            $critical99 = $this->chiInverse(0.99, $gradosDeLibertad);
        } catch (\Throwable $e) {
            // Si falla el cálculo numérico, dejamos valores en null
        }

        return [
            "chicua" => $sumaChiCua,
            "chicua_statistic" => $sumaChiCua,
            "statistic" => $sumaChiCua,
            "grados_libertad" => $gradosDeLibertad,
            "degrees_of_freedom" => $gradosDeLibertad,
            "grados_libertad_raw" => $gradosDeLibertad,
            "p_value" => $pValue,
            "critical_0_95" => $critical95,
            "critical_0_99" => $critical99,
            // Compatibilidad con front-end: chi inverso (valor crítico 0.95)
            "chi_inverso" => $critical95,
            "chi_inverse" => $critical95,
        ];

    }


    public static function normDistAcumulado(float $x, float $promedio, float $desviacionEstandar){
        //Esta función asume que el parámetro de acumulado = VERDADERO y utiliza la integral
        //Se -infinito hasta x

        if($desviacionEstandar <= 0.0000001){
            return NAN;
        }

        $z = ($x - $promedio) / $desviacionEstandar;
        
        $phiZ = 0.5 * (1.0 + self::erf($z / sqrt(2.0)));

        return $phiZ;

    }


    /**
     * Implementa la función de Error (erf) para PHP.
     * Se basa en una aproximación polinómica de alta precisión
     * para evaluar la integral Gaussiana.
     * @param float $y El valor para el cual calcular la erf.
     * @return float El valor de erf(y).
     */
    public static function erf($y) {
        // Constantes de la aproximación de Abramowitz & Stegun (7.1.26)
        $a1 = 0.254829592;
        $a2 = -0.284496736;
        $a3 = 1.421413741;
        $a4 = -1.453152027;
        $a5 = 1.061405429;
        $p = 0.3275911;

        // Manejar el signo: erf(-y) = -erf(y)
        $sign = 1.0;
        if ($y < 0) {
            $sign = -1.0;
            $y = -$y;
        }

        // Cálculo del factor t
        $t = 1.0 / (1.0 + $p * $y);

        // Evaluación del polinomio
        $erf = 1.0 - (((($a5 * $t + $a4) * $t + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$y * $y);

        return $sign * $erf;
    }

    /**
     * CDF de la chi-cuadrada (regularizada) usando gamma incompleta.
     * @param float $x
     * @param int $df
     * @return float
     */
    private function chiCdf(float $x, int $df): float
    {
        return $this->gammainc($x / 2.0, $df / 2.0);
    }

    /**
     * Inversa (cuantil) de la chi-cuadrada para probabilidad acumulada p.
     * Busca x tal que CDF(x) = p.
     *
     * @param float $p (0..1)
     * @param int $df
     * @return float|null
     */
    public function chiInverse(float $p, int $df): ?float
    {
        if ($p <= 0.0 || $p >= 1.0) return null;

        $low = 0.0;
        $high = max(1.0, $df * 10.0);
        $iter = 0;
        while ($this->chiCdf($high, $df) < $p && $iter < 200) {
            $high *= 2.0;
            $iter++;
        }

        $tol = 1e-8;
        $i = 0;
        while (($high - $low) > $tol && $i < 200) {
            $mid = ($low + $high) / 2.0;
            $cdf = $this->chiCdf($mid, $df);
            if ($cdf < $p) {
                $low = $mid;
            } else {
                $high = $mid;
            }
            $i++;
        }

        return ($low + $high) / 2.0;
    }

    /**
     * Gamma incompleta regularizada (aproximación simple).
     */
    private function gammainc(float $x, float $a): float
    {
        if ($x < 0 || $a <= 0) return NAN;
        $gl = $this->gammaLower($x, $a);
        $g = $this->gammaFunc($a);
        
        // Evitar división por cero
        if (abs($g) < 1e-10) return NAN;
        
        return $gl / $g;
    }

    private function gammaLower(float $x, float $a): float
    {
        // Evitar división por cero
        if (abs($a) < 1e-10) return NAN;
        
        $sum = 1.0 / $a;
        $term = $sum;
        for ($n = 1; $n < 100; $n++) {
            $divisor = $a + $n;
            if (abs($divisor) < 1e-10) break; // Evitar división por cero
            $term *= $x / $divisor;
            $sum += $term;
        }
        return pow($x, $a) * exp(-$x) * $sum;
    }

    private function gammaFunc(float $z): float
    {
        $p = [
            676.5203681218851,
            -1259.1392167224028,
            771.32342877765313,
            -176.61502916214059,
            12.507343278686905,
            -0.13857109526572012,
            9.9843695780195716e-6,
            1.5056327351493116e-7
        ];
        $g = 7;
        if ($z < 0.5) {
            $sinVal = sin(M_PI * $z);
            $gammaVal = $this->gammaFunc(1 - $z);
            $denominator = $sinVal * $gammaVal;
            
            // Evitar división por cero
            if (abs($denominator) < 1e-10) return NAN;
            
            return M_PI / $denominator;
        }
        $z -= 1;
        $x = 0.99999999999980993;
        for ($i = 0; $i < count($p); $i++) {
            $x += $p[$i] / ($z + $i + 1);
        }
        $t = $z + $g + 0.5;
        return sqrt(2 * M_PI) * pow($t, $z + 0.5) * exp(-$t) * $x;
    }

}
