<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\FrecuencyTableController;


Route::post('/calculate-statistics', [StatisticsController::class, 'calculate']);
Route::post('/calculate-frequency', [FrecuencyTableController::class, 'calculateFrequency']);

// Home page: render the Welcome page only
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard and auth-related routes removed intentionally.
