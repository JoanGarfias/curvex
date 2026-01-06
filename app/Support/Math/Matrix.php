<?php

namespace App\Math;

class Matrix 
{
    /**
     * @param float[][] $data
     */
    public function __construct(private array $data) {}

    public static function multiply(Matrix $a, Matrix $b): Matrix 
    {
        // Lógica de cálculo...
    }
}