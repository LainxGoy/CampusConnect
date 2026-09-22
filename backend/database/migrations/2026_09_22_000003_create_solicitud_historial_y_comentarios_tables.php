<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Trazabilidad de Estados
        Schema::create('solicitud_historial_estados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->onDelete('cascade');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->string('cambiado_por');
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('solicitud_id');
        });

        // Comentarios y Respuestas Administrativas
        Schema::create('solicitud_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicitudes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('contenido');
            $table->timestamps();

            $table->index('solicitud_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_comentarios');
        Schema::dropIfExists('solicitud_historial_estados');
    }
};
