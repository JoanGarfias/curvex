<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FrecuencyTableController;
use App\Http\Controllers\CorreccionStatisticsController;
use App\Http\Controllers\MuestroAceptacionController;

Route::post('/calculate-statistics', [StatisticsController::class, 'calculate']);
Route::post('/test-normdist', [StatisticsController::class, 'normdist']);
Route::post('/test-muestroaceptacion', [MuestroAceptacionController::class, 'calcular']);
Route::post('/test-muestroaceptacion2', [MuestroAceptacionController::class, 'calcular2']);
Route::post('/calculate-frequency', [FrecuencyTableController::class, 'calculateFrequency']);
Route::post('/correct-frequency', [CorreccionStatisticsController::class, 'corregir']);

Route::get('/', function () {return Inertia::render('MainMenu');})->name('home');

Route::get('/calculadora', function () {return Inertia::render('Welcome'); })->name('calculadora');

Route::get('/muestreo-aceptacion', function () {return Inertia::render('MuestreoAceptacion');})->name('muestreo');

Route::get('/correccionvarianza', function () {return Inertia::render('Correccion');})->name('correcion de varianza');