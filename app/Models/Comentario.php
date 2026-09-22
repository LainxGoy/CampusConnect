<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comentario extends Model
{
    use HasFactory;

    protected $table = 'comentarios';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'mensaje',
        'es_interno',
    ];

    protected function casts(): array
    {
        return [
            'es_interno' => 'boolean',
        ];
    }

    /**
     * Solicitud en la que se publicó el comentario.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Usuario que escribió el comentario.
     */
    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
