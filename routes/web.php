<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FrecuencyTableController;
use App\Http\Controllers\CorreccionStatisticsController;


Route::post('/calculate-statistics', [StatisticsController::class, 'calculate']);
Route::post('/calculate-frequency', [FrecuencyTableController::class, 'calculateFrequency']);
Route::post('/correct-frequency', [CorreccionStatisticsController::class, 'corregir']);

// Home page: render the Welcome page only
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/correccionvarianza', function () {
    return Inertia::render('Correccion');
})->name('correcion de varianza');

// Dashboard and auth-related routes removed intentionally.
