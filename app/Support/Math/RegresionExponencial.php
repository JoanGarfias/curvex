<?php

use App\Services\RegresionOperations;

class RegresionExponencial implements RegresionOperations {
    /** @var Point[] */
    private array $points = [];
    private float $y_avg = 0.0;
    private float $SSE = 0.0;
    private float $SST = 0.0;
    private string $method = "lineal";
    public function calculateSSE(){}
    public function calculateSST(){}
    public function calculateR2(){}
}