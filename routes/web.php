<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\RecursoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Web - Campus Connect (Panel Administrativo)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard Administrativo con Métricas y Monitoreo de Tickets
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Gestión CRUD de Recursos Institucionales (Infraestructura y Equipamiento)
Route::resource('recursos', RecursoController::class);
