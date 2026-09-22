@extends('layouts.app')

@section('title', 'Dashboard Administrativo')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Panel de Métricas y Operaciones</h1>
        <p class="page-subtitle">Consolidación en tiempo real del estado de solicitudes estudiantiles y recursos del campus.</p>
    </div>
    <div>
        <a href="{{ route('recursos.create') }}" class="btn btn-primary">
            + Nuevo Recurso
        </a>
    </div>
</div>

<!-- 1. Fila de KPIs de Tickets -->
<div class="grid-4">
    <div class="stat-card">
        <span class="stat-label">Total Solicitudes</span>
        <span class="stat-value">{{ $totalSolicitudes }}</span>
        <span class="stat-sub">Tickets registrados en total</span>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--warning);">
        <span class="stat-label">Pendientes de Asignar</span>
        <span class="stat-value" style="color: var(--warning);">{{ $pendientes }}</span>
        <span class="stat-sub">Requieren revisión técnica</span>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--info);">
        <span class="stat-label">En Proceso / Atención</span>
        <span class="stat-value" style="color: var(--info);">{{ $enProceso }}</span>
        <span class="stat-sub">En manos del equipo técnico</span>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--success);">
        <span class="stat-label">Tickets Resueltos</span>
        <span class="stat-value" style="color: var(--success);">{{ $resueltas }}</span>
        <span class="stat-sub">Casos cerrados satisfactoriamente</span>
    </div>
</div>

<!-- 2. Fila de Desglose Operativo y Recursos -->
<div class="grid-2">
    <!-- Métricas de Infraestructura & Tiempos -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Infraestructura y Rendimiento de Atención</h2>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed var(--border);">
                <span style="font-weight: 500;">Tiempo Promedio de Resolución:</span>
                <span class="badge badge-info" style="font-size: 0.85rem;">
                    {{ $promedioHoras !== null ? $promedioHoras . ' horas' : 'Sin datos suficientes' }}
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed var(--border);">
                <span style="font-weight: 500;">Total Recursos Registrados:</span>
                <strong style="font-size: 1.1rem;">{{ $totalRecursos }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed var(--border);">
                <span style="font-weight: 500;">Recursos 100% Operativos:</span>
                <span class="badge badge-success">{{ $recursosOperativos }} activos</span>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0;">
                <span style="font-weight: 500;">Recursos en Mantenimiento:</span>
                <span class="badge badge-warning">{{ $recursosMantenimiento }} bajo servicio</span>
            </div>
        </div>
    </div>

    <!-- Distribución por Tipos de Solicitud -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Distribución de Solicitudes por Categoría</h2>
        </div>
        <div style="display: flex; flex-direction: column; gap: 0.85rem;">
            @php
                $tiposConfig = [
                    'mantenimiento' => ['nombre' => 'Mantenimiento Preventivo/Correctivo', 'color' => 'badge-warning'],
                    'soporte' => ['nombre' => 'Soporte Tecnológico / TI', 'color' => 'badge-info'],
                    'infraestructura' => ['nombre' => 'Infraestructura Física', 'color' => 'badge-secondary'],
                    'equipamiento' => ['nombre' => 'Equipamiento y Mobiliario', 'color' => 'badge-success'],
                ];
            @endphp

            @foreach($tiposConfig as $clave => $item)
                @php $cantidad = $porTipo[$clave] ?? 0; @endphp
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.9rem;">{{ $item['nombre'] }}</span>
                    <span class="badge {{ $item['color'] }}">
                        {{ $cantidad }} {{ $cantidad === 1 ? 'ticket' : 'tickets' }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- 3. Tabla de Solicitudes Recientes -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Últimas Solicitudes Ingresadas</h2>
        <a href="/api/v1/solicitudes" target="_blank" class="btn btn-secondary btn-sm">
            Ver JSON de API
        </a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Título / Asunto</th>
                    <th>Tipo</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Solicitante</th>
                    <th>Recurso Afectado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ultimasSolicitudes as $solicitud)
                    <tr>
                        <td>
                            <strong>{{ $solicitud->codigo_ticket }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $solicitud->titulo }}</div>
                            <small style="color: var(--text-muted);">{{ Str::limit($solicitud->descripcion, 60) }}</small>
                        </td>
                        <td>
                            <span class="badge badge-secondary">{{ $solicitud->tipo }}</span>
                        </td>
                        <td>
                            @php
                                $badgePrioridad = match($solicitud->prioridad) {
                                    'urgente' => 'badge-danger',
                                    'alta' => 'badge-warning',
                                    'media' => 'badge-info',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgePrioridad }}">{{ $solicitud->prioridad }}</span>
                        </td>
                        <td>
                            @php
                                $badgeEstado = match($solicitud->estado) {
                                    'resuelto' => 'badge-success',
                                    'en_proceso' => 'badge-warning',
                                    'pendiente' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeEstado }}">{{ str_replace('_', ' ', $solicitud->estado) }}</span>
                        </td>
                        <td>
                            <div>{{ $solicitud->solicitante?->name ?? 'Estudiante' }}</div>
                            @if($solicitud->solicitante?->codigo_estudiantil)
                                <small style="color: var(--text-muted);">{{ $solicitud->solicitante->codigo_estudiantil }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $solicitud->recurso ? $solicitud->recurso->nombre : 'Sin recurso específico' }}
                        </td>
                        <td>
                            {{ $solicitud->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No hay solicitudes registradas aún.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
