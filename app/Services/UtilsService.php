<?php

namespace App\Services;


class UtilsService{
    
    public static function factorial(int $n){
        if($n <= 1){
            return 1;
        }
        return $n * self::factorial($n - 1);
    }
}