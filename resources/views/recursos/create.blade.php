@extends('layouts.app')

@section('title', 'Registrar Recurso')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Registrar Nuevo Recurso Institucional</h1>
        <p class="page-subtitle">Agrega infraestructura física o equipamiento al catálogo del campus.</p>
    </div>
    <div>
        <a href="{{ route('recursos.index') }}" class="btn btn-secondary">
            ← Volver al Listado
        </a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('recursos.store') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <!-- Código -->
            <div class="form-group">
                <label for="codigo" class="form-label">Código Institucional *</label>
                <input 
                    type="text" 
                    id="codigo" 
                    name="codigo" 
                    class="form-control" 
                    placeholder="Ej: AUL-204 o EQ-PROY-03" 
                    value="{{ old('codigo') }}" 
                    required
                >
                @error('codigo')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nombre -->
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre Descriptivo *</label>
                <input 
                    type="text" 
                    id="nombre" 
                    name="nombre" 
                    class="form-control" 
                    placeholder="Ej: Proyector Epson Láser 4K" 
                    value="{{ old('nombre') }}" 
                    required
                >
                @error('nombre')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <!-- Tipo -->
            <div class="form-group">
                <label for="tipo" class="form-label">Tipo de Recurso *</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    <option value="infraestructura" {{ old('tipo') === 'infraestructura' ? 'selected' : '' }}>
                        Infraestructura (Aulas, Labs, Auditorios)
                    </option>
                    <option value="equipamiento" {{ old('tipo') === 'equipamiento' ? 'selected' : '' }}>
                        Equipamiento (Proyectores, PCs, Clima)
                    </option>
                </select>
                @error('tipo')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Categoría -->
            <div class="form-group">
                <label for="categoria" class="form-label">Categoría / Familia</label>
                <input 
                    type="text" 
                    id="categoria" 
                    name="categoria" 
                    class="form-control" 
                    placeholder="Ej: Audiovisual, Laboratorios, Cómputo" 
                    value="{{ old('categoria') }}"
                >
                @error('categoria')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <!-- Ubicación -->
            <div class="form-group">
                <label for="ubicacion" class="form-label">Ubicación Física *</label>
                <input 
                    type="text" 
                    id="ubicacion" 
                    name="ubicacion" 
                    class="form-control" 
                    placeholder="Ej: Pabellón B, Piso 2, Sala 201" 
                    value="{{ old('ubicacion') }}" 
                    required
                >
                @error('ubicacion')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- Estado Inicial -->
            <div class="form-group">
                <label for="estado" class="form-label">Estado Operativo *</label>
                <select id="estado" name="estado" class="form-control" required>
                    <option value="operativo" {{ old('estado', 'operativo') === 'operativo' ? 'selected' : '' }}>
                        Operativo / Disponible
                    </option>
                    <option value="mantenimiento" {{ old('estado') === 'mantenimiento' ? 'selected' : '' }}>
                        En Mantenimiento
                    </option>
                    <option value="fuera_servicio" {{ old('estado') === 'fuera_servicio' ? 'selected' : '' }}>
                        Fuera de Servicio
                    </option>
                </select>
                @error('estado')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Descripción -->
        <div class="form-group">
            <label for="descripcion" class="form-label">Detalles y Especificaciones Técnicas</label>
            <textarea 
                id="descripcion" 
                name="descripcion" 
                class="form-control" 
                rows="4" 
                placeholder="Observaciones, números de serie, requerimientos de mantenimiento..."
            >{{ old('descripcion') }}</textarea>
            @error('descripcion')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
            <a href="{{ route('recursos.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                Guardar Recurso
            </button>
        </div>
    </form>
</div>
@endsection
