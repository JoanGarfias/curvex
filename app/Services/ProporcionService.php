<?php

namespace App\Services;

class ProporcionService
{
    // Error function approximation (Abramowitz & Stegun)
    private function erf(float $x): float
    {
        $sign = ($x < 0) ? -1.0 : 1.0;
        $x = abs($x);
        $a1 = 0.254829592;
        $a2 = -0.284496736;
        $a3 = 1.421413741;
        $a4 = -1.453152027;
        $a5 = 1.061405429;
        $p = 0.3275911;
        $t = 1.0 / (1.0 + $p * $x);
        $y = 1.0 - (((($a5 * $t + $a4) * $t + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$x * $x);
        return $sign * $y;
    }

    private function normalCdf(float $z): float
    {
        return 0.5 * (1.0 + $this->erf($z / sqrt(2.0)));
    }

    // Numerically invert normal CDF with bisection for critical values
    private function normalQuantile(float $p): float
    {
        if ($p <= 0.0) return -INF;
        if ($p >= 1.0) return INF;
        $low = -10.0; $high = 10.0;
        $i = 0;
        while ($i < 200 && ($high - $low) > 1e-8) {
            $mid = 0.5 * ($low + $high);
            $cdf = $this->normalCdf($mid);
            if ($cdf < $p) $low = $mid; else $high = $mid;
            $i++;
        }
        return 0.5 * ($low + $high);
    }

    /**
     * Ejecuta la prueba de hipótesis para proporciones usando aproximación normal.
     *
     * @param int $x éxitos observados
     * @param int $n tamaño de muestra
     * @param float $p0 proporción bajo H0
     * @param float $alpha nivel de significancia (por defecto 0.05)
     * @param string $tail 'two'|'left'|'right'
     * @param bool $continuity aplicar corrección de continuidad
     * @return array
     */
    public function testProportion(int $x, int $n, float $p0, float $alpha = 0.05, string $tail = 'two', bool $continuity = true): array
    {
        $pHat = $n > 0 ? ($x / $n) : 0.0;

        $np0 = $n * $p0;
        $var = $n * $p0 * (1.0 - $p0);
        $sd = $var > 0 ? sqrt($var) : 0.0;

        // Ajuste por corrección de continuidad
        $adjustedX = $x;
        if ($continuity && $sd > 0) {
            if ($x < $np0) {
                $adjustedX = $x + 0.5;
            } elseif ($x > $np0) {
                $adjustedX = $x - 0.5;
            }
        }

        if ($sd == 0.0) {
            $z = NAN;
        } else {
            $z = ($adjustedX - $np0) / $sd;
        }

        // p-value según cola
        if ($tail === 'two') {
            $pValue = is_nan($z) ? NAN : 2.0 * (1.0 - $this->normalCdf(abs($z)));
            $critical = $this->normalQuantile(1.0 - $alpha / 2.0);
        } elseif ($tail === 'right') {
            $pValue = is_nan($z) ? NAN : 1.0 - $this->normalCdf($z);
            $critical = $this->normalQuantile(1.0 - $alpha);
        } else { // left
            $pValue = is_nan($z) ? NAN : $this->normalCdf($z);
            $critical = $this->normalQuantile($alpha);
        }

        $reject = is_nan($pValue) ? false : ($pValue < $alpha);

        return [
            'x' => $x,
            'n' => $n,
            'p_hat' => $pHat,
            'p0' => $p0,
            'alpha' => $alpha,
            'tail' => $tail,
            'continuity' => (bool) $continuity,
            'adjusted_x' => $adjustedX,
            'np0' => $np0,
            'sd' => $sd,
            'z' => $z,
            'p_value' => $pValue,
            'critical' => $critical,
            'reject' => $reject,
        ];
    }
}
