<?php

namespace App\ValueObjects;

use InvalidArgumentException;

class Matrix{
    public array $data;
    public int $rows;
    public int $cols;

    public function __construct(array $data, int $rows, int $cols, bool $isCuadratic = false){
        if($isCuadratic == true){
            if($rows != count($data) || $rows <= 0 || $cols <= 0){
                throw new InvalidArgumentException("No coinciden las filas con los datos");
            }
        }else{
            if($rows != count($data) || $rows <= 0 || $cols <= 0){
                throw new InvalidArgumentException("No coinciden las filas con los datos");
            }
        }
        
        $this->data = $data;
        $this->rows = $rows;
        $this->cols = $cols;
    }

    public function getConstantTerms(): array {
        $terms = [];

        foreach($this->data as $row){
            $terms[] = $row[count($row) - 1];
        }

        return $terms;
    }

    public function calculateDeterminant() : float {
        return ($this->data[0][0] * $this->data[1][1]) - ($this->data[0][1] * $this->data[1][0]);
    }

}