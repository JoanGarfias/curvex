<?php

namespace App\ValueObjects;

class VariableData {
    /** @var float[] */
    public array $points = [];

    public function __construct(array $points)
    {
        $this->points = $points;
    }

    public function countPoints(): int
    {
        return count($this->points);
    }

    public function getVariableAt(int $index): float
    {
        return $this->points[$index];
    }

    public function __clone()
    {
        // Como tu array tiene números (floats), PHP ya hace una copia por valor automáticamente.
        // Sin embargo, forzarlo explícitamente asegura que $this->points 
        // sea un array nuevo en memoria, desligado del objeto original.
        
        $this->points = $this->points; 
        
        // NOTA: Si $points tuviera OBJETOS dentro, aquí tendrías que recorrerlos y clonarlos uno a uno.
    }
}

// Mantener compatibilidad con código antiguo
class independentVariable extends VariableData {}