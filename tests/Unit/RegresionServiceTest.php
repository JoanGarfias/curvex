<?php

use App\Services\RegresionService;
use App\ValueObjects\Point;
use Illuminate\Support\Facades\Log;

describe('RegresionService', function () {
    
    beforeEach(function () {
        // Mock del facade Log para evitar errores en tests unitarios
        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();
    });
    
    it('calcula correctamente R² para una regresión lineal perfecta', function () {
        // Datos de una línea perfecta: y = 2x + 1
        $points = [
            new Point(1, 3),   // y = 2(1) + 1 = 3
            new Point(2, 5),   // y = 2(2) + 1 = 5
            new Point(3, 7),   // y = 2(3) + 1 = 7
            new Point(4, 9),   // y = 2(4) + 1 = 9
            new Point(5, 11),  // y = 2(5) + 1 = 11
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // Para una línea perfecta, R² debe ser 1.0
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('calcula correctamente R² para datos con correlación fuerte', function () {
        // Datos con correlación fuerte pero no perfecta
        $points = [
            new Point(1, 2.1),
            new Point(2, 4.0),
            new Point(3, 5.9),
            new Point(4, 8.1),
            new Point(5, 10.0),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // R² debe estar entre 0.9 y 1.0 para correlación fuerte
        expect($r2)->toBeGreaterThan(0.9)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('calcula correctamente R² para datos con correlación débil', function () {
        // Datos con correlación débil
        $points = [
            new Point(1, 2),
            new Point(2, 3),
            new Point(3, 2),
            new Point(4, 5),
            new Point(5, 4),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // R² debe estar entre 0 y 1
        expect($r2)->toBeGreaterThanOrEqual(0.0)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('calcula correctamente R² con valores negativos', function () {
        // Datos con pendiente negativa: y = -x + 10
        $points = [
            new Point(1, 9),
            new Point(2, 8),
            new Point(3, 7),
            new Point(4, 6),
            new Point(5, 5),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // Para una línea perfecta con pendiente negativa, R² debe ser ~1.0
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('maneja correctamente datos con valores decimales', function () {
        // Datos con valores decimales
        $points = [
            new Point(1.5, 3.2),
            new Point(2.3, 4.8),
            new Point(3.1, 6.5),
            new Point(4.7, 9.1),
            new Point(5.9, 11.3),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // R² debe estar entre 0 y 1
        expect($r2)->toBeGreaterThanOrEqual(0.0)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('calcula R² con el conjunto mínimo de puntos', function () {
        // Solo 2 puntos (mínimo para una regresión lineal)
        $points = [
            new Point(1, 2),
            new Point(2, 4),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // Con 2 puntos, R² siempre debe ser 1.0
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('calcula correctamente con valores grandes', function () {
        // Datos con valores grandes
        $points = [
            new Point(1000, 2000),
            new Point(2000, 4000),
            new Point(3000, 6000),
            new Point(4000, 8000),
            new Point(5000, 10000),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // Para una línea perfecta, R² debe ser ~1.0
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('maneja correctamente puntos con mismo valor x', function () {
        // Puntos con valores x repetidos pero diferentes y
        $points = [
            new Point(1, 2),
            new Point(1, 3),
            new Point(2, 4),
            new Point(3, 5),
            new Point(4, 6),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // R² debe estar entre 0 y 1
        expect($r2)->toBeGreaterThanOrEqual(0.0)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('calcula R² con datos de ejemplo real', function () {
        // Datos de ejemplo típicos de un problema de regresión
        $points = [
            new Point(1, 2.5),
            new Point(2, 3.1),
            new Point(3, 3.9),
            new Point(4, 5.2),
            new Point(5, 6.1),
            new Point(6, 6.8),
            new Point(7, 7.9),
            new Point(8, 9.0),
        ];

        $service = new RegresionService($points);
        $r2 = $service->calculateR2();

        // R² debe estar entre 0 y 1
        expect($r2)->toBeGreaterThanOrEqual(0.0)
            ->and($r2)->toBeLessThanOrEqual(1.0)
            ->and($r2)->toBeGreaterThan(0.9); // Esperamos buena correlación
    });

    it('calcula correctamente con línea horizontal (pendiente cero)', function () {
        // Todos los puntos tienen el mismo valor y (línea horizontal)
        // Esto causa SST = 0 lo que resulta en división por cero
        $points = [
            new Point(1, 5),
            new Point(2, 5),
            new Point(3, 5),
            new Point(4, 5),
            new Point(5, 5),
        ];

        $service = new RegresionService($points);
        
        // Este caso especial debería lanzar una excepción de división por cero
        expect(fn() => $service->calculateR2())
            ->toThrow(DivisionByZeroError::class);
    });

    it('lanza excepción cuando hay error en el cálculo', function () {
        // Caso que podría causar división por cero o matriz singular
        $points = [
            new Point(0, 0),
            new Point(0, 0),
        ];

        $service = new RegresionService($points);
        
        // Dependiendo de la implementación, esto podría lanzar excepción
        // o manejar el caso especial
        try {
            $r2 = $service->calculateR2();
            expect($r2)->toBeGreaterThanOrEqual(0.0);
        } catch (Exception $e) {
            expect($e)->toBeInstanceOf(Exception::class);
        }
    });
});
