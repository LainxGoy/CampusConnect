@extends('layouts.app')

@section('title', 'Gestión de Recursos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Catálogo de Recursos Institucionales</h1>
        <p class="page-subtitle">Administración de infraestructura física (aulas, laboratorios) y equipamiento tecnológico.</p>
    </div>
    <div>
        <a href="{{ route('recursos.create') }}" class="btn btn-primary">
            + Nuevo Recurso
        </a>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="card" style="margin-bottom: 1.25rem;">
    <form action="{{ route('recursos.index') }}" method="GET" class="filter-bar">
        <div style="flex: 2; min-width: 200px;">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Buscar por código, nombre o ubicación..." 
                value="{{ request('search') }}"
            >
        </div>

        <div style="flex: 1; min-width: 150px;">
            <select name="tipo" class="form-control">
                <option value="">-- Todos los tipos --</option>
                <option value="infraestructura" {{ request('tipo') == 'infraestructura' ? 'selected' : '' }}>Infraestructura</option>
                <option value="equipamiento" {{ request('tipo') == 'equipamiento' ? 'selected' : '' }}>Equipamiento</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 150px;">
            <select name="estado" class="form-control">
                <option value="">-- Todos los estados --</option>
                <option value="operativo" {{ request('estado') == 'operativo' ? 'selected' : '' }}>Operativo</option>
                <option value="mantenimiento" {{ request('estado') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                <option value="fuera_servicio" {{ request('estado') == 'fuera_servicio' ? 'selected' : '' }}>Fuera de servicio</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-secondary">Filtrar</button>
            @if(request()->anyFilled(['search', 'tipo', 'estado']))
                <a href="{{ route('recursos.index') }}" class="btn btn-secondary" title="Limpiar filtros">✕ Limpiar</a>
            @endif
        </div>
    </form>
</div>

<!-- Tabla de Catálogo -->
<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre del Recurso</th>
                    <th>Tipo</th>
                    <th>Categoría</th>
                    <th>Ubicación Física</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recursos as $recurso)
                    <tr>
                        <td>
                            <strong style="color: var(--primary);">{{ $recurso->codigo }}</strong>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $recurso->nombre }}</div>
                            @if($recurso->descripcion)
                                <small style="color: var(--text-muted);">{{ Str::limit($recurso->descripcion, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($recurso->tipo === 'infraestructura')
                                <span class="badge badge-info">Infraestructura</span>
                            @else
                                <span class="badge badge-secondary">Equipamiento</span>
                            @endif
                        </td>
                        <td>
                            {{ $recurso->categoria ?? 'General' }}
                        </td>
                        <td>
                            📍 {{ $recurso->ubicacion }}
                        </td>
                        <td>
                            @php
                                $badgeEstado = match($recurso->estado) {
                                    'operativo' => 'badge-success',
                                    'mantenimiento' => 'badge-warning',
                                    'fuera_servicio' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                                $textoEstado = match($recurso->estado) {
                                    'operativo' => 'Operativo',
                                    'mantenimiento' => 'En Mantenimiento',
                                    'fuera_servicio' => 'Fuera de Servicio',
                                    default => $recurso->estado,
                                };
                            @endphp
                            <span class="badge {{ $badgeEstado }}">{{ $textoEstado }}</span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <a href="{{ route('recursos.edit', $recurso) }}" class="btn btn-secondary btn-sm">
                                Editar
                            </a>

                            <form 
                                action="{{ route('recursos.destroy', $recurso) }}" 
                                method="POST" 
                                style="display: inline-block;" 
                                onsubmit="return confirm('¿Está seguro de eliminar el recurso {{ $recurso->codigo }}?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2.5rem;">
                            No se encontraron recursos que coincidan con la búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination-container">
        {{ $recursos->links() }}
    </div>
</div>
@endsection
