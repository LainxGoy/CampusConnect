<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'codigo_ticket',
        'titulo',
        'descripcion',
        'tipo',
        'prioridad',
        'estado',
        'user_id',
        'responsable_id',
        'recurso_id',
        'fecha_resolucion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_resolucion' => 'datetime',
        ];
    }

    /**
     * Estudiante o usuario que originó la solicitud.
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Responsable técnico asignado para resolver el ticket.
     */
    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    /**
     * Recurso de infraestructura o equipamiento asociado (opcional).
     */
    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class, 'recurso_id');
    }

    /**
     * Historial de auditoría y transiciones de estado.
     */
    public function historiales(): HasMany
    {
        return $this->hasMany(HistorialSolicitud::class, 'solicitud_id')->orderBy('created_at', 'asc');
    }

    /**
     * Comentarios y notas de seguimiento en la solicitud.
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class, 'solicitud_id')->orderBy('created_at', 'asc');
    }

    /**
     * Archivos adjuntos y evidencias multimedia.
     */
    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class, 'solicitud_id');
    }

    /**
     * Scopes útiles.
     */
    public function scopePendientes(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'en_revision']);
    }

    public function scopeEnProceso(Builder $query): Builder
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeResueltas(Builder $query): Builder
    {
        return $query->where('estado', 'resuelto');
    }

    /**
     * Generador secuencial de código único de ticket.
     */
    public static function generarCodigoTicket(): string
    {
        $prefijo = 'TKT-' . Carbon::now()->format('Ym');
        $ultimo = self::where('codigo_ticket', 'like', "{$prefijo}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimo) {
            $partes = explode('-', $ultimo->codigo_ticket);
            $secuencia = intval(end($partes)) + 1;
        } else {
            $secuencia = 1;
        }

        return sprintf('%s-%04d', $prefijo, $secuencia);
    }
}
