<?php

namespace App\Services;

use App\Support\Math\CrammerSolver;
use App\ValueObjects\Matrix;
use Illuminate\Support\Facades\Log;
use App\ValueObjects\Point;
use App\ValueObjects\Solution2VSystem;
use Exception;

class RegresionService
{
    /** @var Point[] */
    private array $points = [];
    private float $y_avg = 0.0;
    private float $SSE = 0.0;
    private float $SSR = 0.0;
    private float $SST = 0.0;
    private ?Solution2VSystem $a = null;
    private string $method = "lineal";

    public function __construct(array $points, string $method = "lineal")
    {
        $this->points = $points;
        $this->method = $method;
    }

    private function countPoints(): int
    {
        return count($this->points);
    }



    private function calculateSSE(): float {
        $sse = 0.0;

        foreach($this->points as $p){
            //y gorrito
            $y_pri = $this->a->a0 + $this->a->a1 * $p->x;
            $sse += pow($y_pri-$p->y, 2);
        }
        
        return $sse;
    }

    private function calculateSST(): float {
        $sst = 0.0;

        foreach($this->points as $p){
            $sst += pow($p->y - $this->y_avg,   2);
        }
        
        return $sst;
    }
    

    public function calculateR2(): float
    {
        /*Paso 1: Calcular m, la cantidad de datos */
        $m = (float) $this->countPoints();

        /*Paso 2: Calcular las sumatorias (SSE, SSR, SST) */
        $sum_y = array_reduce($this->points, fn(float $s, Point $p) => $s + $p->y, 0.0);
        $sum_x = array_reduce($this->points, fn(float $s, Point $p) => $s + $p->x, 0.0);
        $sum_x2 = array_reduce($this->points, fn(float $s, Point $p) => $s + pow($p->x, 2), 0.0);
        $sum_xy = array_reduce($this->points, fn(float $s, Point $p) => $s + ($p->x * $p->y), 0.0);
        Log::info("Sumatorias calculadas: sum_y = $sum_y, sum_x = $sum_x, sum_x2 = $sum_x2, sum_xy = $sum_xy");

        try{
            $this->y_avg = $sum_y / $m;

            $matriz = new Matrix(
                [
                    [$m, $sum_x, $sum_y],
                    [$sum_x, $sum_x2, $sum_xy],
                ], 2, 3);

            $this->a = CrammerSolver::solveCrammerMatrix2X2($matriz);

            Log::info("Coeficientes calculados: a0 = {$this->a->a0}, a1 = {$this->a->a1}");

            $this->SSE = $this->calculateSSE();
            $this->SST = $this->calculateSST();

            Log::info("SSE = $this->SSE, SST = $this->SST");

            $R2 = 1 - ($this->SSE / $this->SST);
            return $R2;
        } catch (Exception $e) {
            Log::error("Error al calcular los coeficientes de regresión: " . $e->getMessage());
            throw $e;
        }
    }

}