<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'codigo_estudiantil',
        'telefono',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Rol al que pertenece el usuario.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Solicitudes creadas por el usuario (como estudiante o solicitante).
     */
    public function solicitudesCreadas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'user_id');
    }

    /**
     * Solicitudes asignadas al usuario (como responsable técnico).
     */
    public function solicitudesAsignadas(): HasMany
    {
        return $this->hasMany(Solicitud::class, 'responsable_id');
    }

    /**
     * Comentarios realizados por el usuario.
     */
    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    /**
     * Historial de cambios de estado registrados por el usuario.
     */
    public function historiales(): HasMany
    {
        return $this->hasMany(HistorialSolicitud::class);
    }

    /**
     * Evidencias subidas por el usuario.
     */
    public function evidencias(): HasMany
    {
        return $this->hasMany(Evidencia::class);
    }

    /**
     * Helpers de validación de roles.
     */
    public function esEstudiante(): bool
    {
        return $this->role?->nombre === 'estudiante';
    }

    public function esAdministrativo(): bool
    {
        return $this->role?->nombre === 'administrativo';
    }

    public function esTecnico(): bool
    {
        return $this->role?->nombre === 'tecnico';
    }
}
