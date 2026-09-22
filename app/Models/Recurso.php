<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recurso extends Model
{
    use HasFactory;

    protected $table = 'recursos';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'categoria',
        'ubicacion',
        'estado',
        'descripcion',
    ];

    /**
     * Solicitudes asociadas a este recurso.
     */
    public function solicitudes(): HasMany
    {
        return $this->hasMany(Solicitud::class);
    }

    /**
     * Scopes útiles para consultas y reportes.
     */
    public function scopeOperativos(Builder $query): Builder
    {
        return $query->where('estado', 'operativo');
    }

    public function scopeEnMantenimiento(Builder $query): Builder
    {
        return $query->where('estado', 'mantenimiento');
    }

    public function scopeInfraestructura(Builder $query): Builder
    {
        return $query->where('tipo', 'infraestructura');
    }

    public function scopeEquipamiento(Builder $query): Builder
    {
        return $query->where('tipo', 'equipamiento');
    }
}
