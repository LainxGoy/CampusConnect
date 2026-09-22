<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Solicitud extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes';

    protected $fillable = [
        'user_id',
        'tipo_solicitud',
        'titulo',
        'descripcion',
        'ubicacion',
        'prioridad_estimada',
        'estado',
        'tecnico_asignado'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function esEditable(): bool
    {
        return $this->estado === 'Pendiente';
    }

    public function esCancelable(): bool
    {
        return $this->estado === 'Pendiente' && empty($this->tecnico_asignado);
    }

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class);
    }

    public function historialEstados(): HasMany
    {
        return $this->hasMany(SolicitudHistorialEstado::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(SolicitudComentario::class)->latest();
    }
}
