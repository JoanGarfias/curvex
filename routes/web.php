<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FrecuencyTableController;
use App\Http\Controllers\CorreccionStatisticsController;
use App\Http\Controllers\MuestroAceptacionController;

/* RUTAS PARA VISTAS */

Route::get('/', function () {return Inertia::render('MainMenu');})->name('home');
Route::get('/estadistica-descriptiva', function () {return Inertia::render('EstadisticaDescriptiva'); })->name('calculadora');
Route::get('/muestreo-aceptacion', function () {return Inertia::render('MuestreoAceptacion');})->name('muestreo');
Route::get('/correccion-varianza', function () {return Inertia::render('Correccion');})->name('correcion de varianza');

/* RUTAS PARA CALCULOS */

Route::post('/calculate-statistics', [StatisticsController::class, 'calculate']);
Route::post('/calculate-frequency', [FrecuencyTableController::class, 'calculateFrequency']);
Route::post('/correct-frequency', [CorreccionStatisticsController::class, 'corregir']);
Route::post('/calc-muestroaceptacion', [MuestroAceptacionController::class, 'calcular']);

/* RUTAS PARA TESTING */

Route::post('/test-normdist', [StatisticsController::class, 'normdist']);
Route::post('/test-muestroaceptacion2', [MuestroAceptacionController::class, 'calcular2']);