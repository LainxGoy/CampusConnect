<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Evidencia extends Model
{
    use HasFactory;

    protected $table = 'evidencias';

    protected $fillable = [
        'solicitud_id',
        'user_id',
        'nombre_original',
        'ruta_archivo',
        'mime_type',
        'tamanio_bytes',
    ];

    protected $appends = [
        'url',
        'tamanio_formateado',
    ];

    /**
     * Solicitud a la que pertenece esta evidencia.
     */
    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(Solicitud::class);
    }

    /**
     * Usuario que subió el archivo.
     */
    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * URL pública directa para visualización y descarga en clientes web y móviles.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk('public')->url($this->ruta_archivo)
        );
    }

    /**
     * Formato legible del tamaño del archivo.
     */
    protected function tamanioFormateado(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->tamanio_bytes;
                if ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                }
                return $bytes . ' B';
            }
        );
    }
}
