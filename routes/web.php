<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FrecuencyTableController;
use App\Http\Controllers\CorreccionStatisticsController;
use App\Http\Controllers\MuestroAceptacionController;
use App\Http\Controllers\PruebaHipotesisVarianzaController;
use App\Http\Controllers\PruebaHipotesisNoVarianzaController;
use App\Http\Controllers\ProporcionController;

/* RUTAS PARA VISTAS */

Route::get('/', function () {return Inertia::render('MainMenu');})->name('home');
Route::get('/estadistica-descriptiva', function () {return Inertia::render('EstadisticaDescriptiva'); })->name('calculadora');
Route::get('/muestreo-aceptacion', function () {return Inertia::render('MuestreoAceptacion');})->name('muestreo');
Route::get('/correccion-varianza', function () {return Inertia::render('Correccion');})->name('correcion de varianza');
// Una sola ruta para todo
Route::get('/pruebas-hipotesis', function () {return Inertia::render('HipotesisGeneral'); 
})->name('hipotesis');


/* RUTAS PARA CALCULOS */

Route::post('/calculate-statistics', [StatisticsController::class, 'calculate']);
Route::post('/calculate-frequency', [FrecuencyTableController::class, 'calculateFrequency']);
Route::post('/correct-frequency', [CorreccionStatisticsController::class, 'corregir']);
Route::post('/calc-muestroaceptacion', [MuestroAceptacionController::class, 'calcular']);
Route::post('/calc-muestroaceptacion2', [MuestroAceptacionController::class, 'calcular2']);
Route::post('/pruebahipotesistabla22', [PruebaHipotesisVarianzaController::class, 'decidir']);
Route::post('/pruebahipotesistabla23', [PruebaHipotesisNoVarianzaController::class, 'decidir']);

// Prueba de proporciones (normal approx con corrección de continuidad)
Route::post('/proportion-test', [ProporcionController::class, 'test']);

/* RUTAS PARA TESTING */

Route::post('/test-normdist', [StatisticsController::class, 'normdist']);