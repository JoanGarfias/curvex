<?php

use Illuminate\Support\Facades\Log;

describe('Endpoint de Regresión', function () {
    
    beforeEach(function () {
        // Mock del facade Log
        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();
    });

    it('calcula correctamente R² a través del endpoint', function () {
        // Datos de una línea perfecta: y = 2x + 1
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,3;2,5;3,7;4,9;5,11',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['R2']
            ]);

        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('maneja datos con correlación fuerte', function () {
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,2.1;2,4.0;3,5.9;4,8.1;5,10.0',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200);
        
        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThan(0.9)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('acepta valores decimales en las coordenadas', function () {
        $response = $this->postJson('/calc-regresion', [
            'values' => '1.5,3.2;2.3,4.8;3.1,6.5;4.7,9.1;5.9,11.3',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200);
        
        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThanOrEqual(0.0)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('maneja correctamente el método por defecto', function () {
        // Sin especificar método, debe usar 'lineal' por defecto
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,2;2,4;3,6;4,8;5,10',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Cálculo de regresión realizado con éxito.'
            ]);

        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('valida que el campo values sea requerido', function () {
        $response = $this->postJson('/calc-regresion', [
            'method' => 'lineal'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['values']);
    });

    it('calcula correctamente con valores negativos', function () {
        // Datos con pendiente negativa
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,9;2,8;3,7;4,6;5,5',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200);
        
        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('maneja correctamente múltiples puntos de datos', function () {
        // Dataset más grande
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,2.5;2,3.1;3,3.9;4,5.2;5,6.1;6,6.8;7,7.9;8,9.0;9,9.8;10,11.2',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200);
        
        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThan(0.95)
            ->and($r2)->toBeLessThanOrEqual(1.0);
    });

    it('maneja el caso de dos puntos (mínimo requerido)', function () {
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,2;2,4',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200);
        
        $r2 = $response->json('data.R2');
        // Con 2 puntos, R² siempre es 1
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });

    it('responde con el formato JSON correcto', function () {
        $response = $this->postJson('/calc-regresion', [
            'values' => '1,2;2,4;3,6',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'R2'
                ]
            ]);

        expect($response->json('message'))->toBe('Cálculo de regresión realizado con éxito.');
    });

    it('maneja valores grandes correctamente', function () {
        $response = $this->postJson('/calc-regresion', [
            'values' => '1000,2000;2000,4000;3000,6000;4000,8000;5000,10000',
            'method' => 'lineal'
        ]);

        $response->assertStatus(200);
        
        $r2 = $response->json('data.R2');
        expect($r2)->toBeGreaterThanOrEqual(0.999);
    });
});
