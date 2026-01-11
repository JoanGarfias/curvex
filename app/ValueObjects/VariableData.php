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
}

// Mantener compatibilidad con código antiguo
class independentVariable extends VariableData {}