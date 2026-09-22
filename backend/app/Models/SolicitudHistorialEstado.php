<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudHistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'solicitud_historial_estados';
    public $timestamps = false;

    protected $fillable = [
        'solicitud_id',
        'estado_anterior',
        'estado_nuevo',
        'cambiado_por',
        'observacion',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }
}
