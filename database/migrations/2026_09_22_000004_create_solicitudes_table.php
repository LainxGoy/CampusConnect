<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ticket', 50)->unique();
            $table->string('titulo', 255);
            $table->text('descripcion');
            $table->string('tipo', 50); // mantenimiento, soporte, infraestructura, equipamiento
            $table->string('prioridad', 50)->default('media'); // baja, media, alta, urgente
            $table->string('estado', 50)->default('pendiente'); // pendiente, en_revision, en_proceso, resuelto, cancelado

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('responsable_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('recurso_id')
                ->nullable()
                ->constrained('recursos')
                ->nullOnDelete();

            $table->timestampTz('fecha_resolucion')->nullable();
            $table->timestampsTz();

            // Índices optimizados para PostgreSQL
            $table->index(['user_id', 'estado'], 'idx_solicitudes_user_estado');
            $table->index(['responsable_id', 'estado'], 'idx_solicitudes_resp_estado');
            $table->index(['estado', 'created_at'], 'idx_solicitudes_estado_fecha');
            $table->index(['tipo', 'prioridad'], 'idx_solicitudes_tipo_prioridad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
