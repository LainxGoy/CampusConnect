<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudComentario extends Model
{
    use HasFactory;

    protected $table = 'solicitud_comentarios';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'contenido'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
