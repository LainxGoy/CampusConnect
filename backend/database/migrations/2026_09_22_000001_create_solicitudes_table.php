<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo_solicitud', [
                'Mantenimiento',
                'Soporte Tecnológico',
                'Infraestructura',
                'Equipamiento'
            ]);
            $table->string('titulo', 150);
            $table->text('descripcion');
            $table->string('ubicacion', 100);
            $table->enum('prioridad_estimada', ['Baja', 'Media', 'Alta', 'Crítica'])->default('Media');
            $table->enum('estado', ['Pendiente', 'En Proceso', 'Resuelto', 'Cerrado', 'Cancelado'])->default('Pendiente');
            $table->string('tecnico_asignado', 150)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'estado']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
