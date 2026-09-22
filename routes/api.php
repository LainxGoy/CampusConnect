<?php

use App\Http\Controllers\Api\SolicitudApiController;
use App\Models\Recurso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API - Campus Connect (v1)
|--------------------------------------------------------------------------
| Endpoints para consumo de la aplicación móvil y servicios externos.
*/

Route::prefix('v1')->group(function () {
    // Catálogo rápido de recursos para selectores móviles
    Route::get('/recursos', function () {
        return response()->json([
            'success' => true,
            'data' => Recurso::select('id', 'codigo', 'nombre', 'tipo', 'categoria', 'ubicacion', 'estado')
                ->where('estado', '!=', 'fuera_servicio')
                ->orderBy('nombre')
                ->get(),
        ]);
    });

    // Gestión de Solicitudes y Trazabilidad (Móvil)
    Route::prefix('solicitudes')->group(function () {
        // Reporte consolidado móvil (debe ir antes de {id})
        Route::get('/reportes/resumen', [SolicitudApiController::class, 'reporteResumen'])->name('api.solicitudes.reportes');

        // CRUD de solicitudes
        Route::get('/', [SolicitudApiController::class, 'index'])->name('api.solicitudes.index');
        Route::post('/', [SolicitudApiController::class, 'store'])->name('api.solicitudes.store');
        Route::get('/{id}', [SolicitudApiController::class, 'seguimiento'])->name('api.solicitudes.show');
        Route::put('/{id}', [SolicitudApiController::class, 'update'])->name('api.solicitudes.update');
        Route::patch('/{id}', [SolicitudApiController::class, 'update']);
        Route::delete('/{id}', [SolicitudApiController::class, 'destroy'])->name('api.solicitudes.destroy');

        // Trazabilidad, Evidencias, Estado y Comentarios
        Route::get('/{id}/seguimiento', [SolicitudApiController::class, 'seguimiento'])->name('api.solicitudes.seguimiento');
        Route::patch('/{id}/estado', [SolicitudApiController::class, 'cambiarEstado'])->name('api.solicitudes.estado');
        Route::post('/{id}/evidencias', [SolicitudApiController::class, 'subirEvidencia'])->name('api.solicitudes.evidencias');
        Route::post('/{id}/comentarios', [SolicitudApiController::class, 'agregarComentario'])->name('api.solicitudes.comentarios');
    });
});
