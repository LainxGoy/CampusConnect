<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Recurso;
use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Muestra el panel administrativo con consolidación de métricas operativas.
     */
    public function index(): View
    {
        // 1. Tarjetas de contadores clave
        $totalSolicitudes = Solicitud::count();
        $pendientes = Solicitud::whereIn('estado', ['pendiente', 'en_revision'])->count();
        $enProceso = Solicitud::where('estado', 'en_proceso')->count();
        $resueltas = Solicitud::where('estado', 'resuelto')->count();

        // 2. Recursos y estado físico
        $totalRecursos = Recurso::count();
        $recursosMantenimiento = Recurso::where('estado', 'mantenimiento')->count();
        $recursosOperativos = Recurso::where('estado', 'operativo')->count();

        // 3. Distribución de solicitudes por tipo
        $porTipo = Solicitud::select('tipo', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        // 4. Distribución por prioridad
        $porPrioridad = Solicitud::select('prioridad', DB::raw('count(*) as total'))
            ->groupBy('prioridad')
            ->pluck('total', 'prioridad')
            ->toArray();

        // 5. Tiempo promedio de atención (en horas) para solicitudes resueltas
        $solicitudesCerradas = Solicitud::whereNotNull('fecha_resolucion')
            ->select('created_at', 'fecha_resolucion')
            ->get();

        $promedioHoras = null;
        if ($solicitudesCerradas->isNotEmpty()) {
            $totalHoras = $solicitudesCerradas->reduce(function ($carry, $item) {
                return $carry + $item->created_at->diffInHours($item->fecha_resolucion);
            }, 0);
            $promedioHoras = round($totalHoras / $solicitudesCerradas->count(), 1);
        }

        // 6. Últimos tickets registrados
        $ultimasSolicitudes = Solicitud::with(['solicitante', 'recurso', 'responsable'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalSolicitudes',
            'pendientes',
            'enProceso',
            'resueltas',
            'totalRecursos',
            'recursosMantenimiento',
            'recursosOperativos',
            'porTipo',
            'porPrioridad',
            'promedioHoras',
            'ultimasSolicitudes'
        ));
    }
}
