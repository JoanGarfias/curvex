<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use Point;

class RegresionService
{
    /** @var Point[] */
    private array $points = [];
    private float $y_avg = 0.0;
    private float $SSE = 0.0;
    private float $SSR = 0.0;
    private float $SST = 0.0;
    private float $a1 = 0.0;
    private float $a2 = 0.0;
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


    private function calculate_SSE(): void {
        //Calcular SSE
    }

    private function calculate_SST(): void {
        //Calcular SST
    }
    

    private function calculate_R2(): float
    {
        /*Paso 1: Calcular m, la cantidad de datos */
        $m = (float) $this->countPoints();

        /*Paso 2: Calcular las sumatorias (SSE, SSR, SST) */
        $sum_y = array_reduce($this->points, fn(float $s, Point $p) => $s + $p->y, 0.0);
        $sum_x = array_reduce($this->points, fn(float $s, Point $p) => $s + $p->x, 0.0);
        $sum_x2 = array_reduce($this->points, fn(float $s, Point $p) => $s + pow($p->x, 2), 0.0);
        $sum_xy = array_reduce($this->points, fn(float $s, Point $p) => $s + ($p->x * $p->y), 0.0);
        
        $this->y_avg = $sum_y / $m;

        $R2 = 0.0;
        return $R2;
    }

}