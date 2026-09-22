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
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 255);
            $table->string('tipo', 50); // infraestructura, equipamiento
            $table->string('categoria', 100)->nullable();
            $table->string('ubicacion', 255);
            $table->string('estado', 50)->default('operativo'); // operativo, mantenimiento, fuera_servicio
            $table->text('descripcion')->nullable();
            $table->timestampsTz();

            $table->index(['tipo', 'estado'], 'idx_recursos_tipo_estado');
            $table->index('ubicacion', 'idx_recursos_ubicacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
