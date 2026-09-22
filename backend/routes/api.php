<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Rutas Públicas de Autenticación
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Rutas Protegidas (Requieren Token Sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Perfil y Sesión
        Route::prefix('auth')->group(function () {
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // Solicitudes CRUD
        Route::apiResource('solicitudes', SolicitudController::class);

        // Subrecursos: Evidencias, Seguimiento y Comentarios
        Route::prefix('solicitudes/{solicitud}')->group(function () {
            Route::post('/evidencias', [SolicitudController::class, 'storeEvidencia']);
            Route::get('/seguimiento', [SolicitudController::class, 'seguimiento']);
            Route::get('/comentarios', [SolicitudController::class, 'comentarios']);
            Route::post('/comentarios', [SolicitudController::class, 'storeComentario']);
        });
    });
});
