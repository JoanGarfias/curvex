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

describe('Endpoint de Predicción de Regresión', function () {
    
    beforeEach(function () {
        Log::shouldReceive('info')->andReturnNull();
        Log::shouldReceive('error')->andReturnNull();
        Log::shouldReceive('debug')->andReturnNull();
    });

    it('calcula Y dado X para regresión lineal', function () {
        // Para y = 2x + 1, si x = 3, entonces y = 7
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'variable_input' => 'x',
            'value' => 3,
            'solutions' => [1, 2] // intercepto = 1, pendiente = 2
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => ['method', 'x', 'y']
            ]);

        $y = $response->json('data.y');
        expect($y)->toBeGreaterThan(6.9)
            ->and($y)->toBeLessThan(7.1);
    });

    it('calcula X dado Y para regresión lineal', function () {
        // Para y = 2x + 1, si y = 7, entonces x = 3
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'variable_input' => 'y',
            'value' => 7,
            'solutions' => [1, 2]
        ]);

        $response->assertStatus(200);

        $x = $response->json('data.x');
        expect($x)->toBeGreaterThan(2.9)
            ->and($x)->toBeLessThan(3.1);
    });

    it('calcula Y dado X para regresión exponencial', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'exponential',
            'variable_input' => 'x',
            'value' => 2,
            'solutions' => [1, 0.5]
        ]);

        $response->assertStatus(200);
        
        $y = $response->json('data.y');
        expect($y)->toBeNumeric();
    });

    it('calcula Y dado X para regresión potencial', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'potential',
            'variable_input' => 'x',
            'value' => 4,
            'solutions' => [2, 2]
        ]);

        $response->assertStatus(200);
        
        $y = $response->json('data.y');
        expect($y)->toBeNumeric();
    });

    it('valida que method sea requerido', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'variable_input' => 'x',
            'value' => 3,
            'solutions' => [1, 2]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['method']);
    });

    it('valida que variable_input sea requerido', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'value' => 3,
            'solutions' => [1, 2]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['variable_input']);
    });

    it('valida que value sea requerido', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'variable_input' => 'x',
            'solutions' => [1, 2]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['value']);
    });

    it('valida que solutions sea requerido', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'variable_input' => 'x',
            'value' => 3
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['solutions']);
    });

    it('valida que method sea uno de los permitidos', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'invalido',
            'variable_input' => 'x',
            'value' => 3,
            'solutions' => [1, 2]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['method']);
    });

    it('valida que variable_input sea x o y', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'variable_input' => 'z',
            'value' => 3,
            'solutions' => [1, 2]
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['variable_input']);
    });

    it('maneja valores negativos correctamente', function () {
        $response = $this->postJson('/calc-regresion-value', [
            'method' => 'lineal',
            'variable_input' => 'x',
            'value' => -5,
            'solutions' => [10, -2]
        ]);

        $response->assertStatus(200);
        
        $y = $response->json('data.y');
        expect($y)->toBe(-5 * -2 + 10); // 20
    });
});
