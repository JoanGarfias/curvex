<?php

namespace App\ValueObjects;

class independentVariable {
    /** @var float[] */
    private array $points = [];

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