<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialSolicitud extends Model
{
    use HasFactory;

    protected $table = 'historial_solicitudes';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'estado_anterior',
        'estado_nuevo',
        'nota',
    ];

    /**
     * Solicitud a la que pertenece este hito del historial.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Usuario responsable de la transición o cambio de estado.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
