<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StatisticsController;

Route::post('/calculate-statistics', [StatisticsController::class, 'calculate']);

// Home page: render the Welcome page only
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Temporary results route used by the client-side demo redirect
Route::get('/resultados', function () {
    return Inertia::render('Resultados');
})->name('resultados');

// Dashboard and auth-related routes removed intentionally.
