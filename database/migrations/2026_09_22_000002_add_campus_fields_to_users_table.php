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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('id')
                ->constrained('roles')
                ->nullOnDelete();

            $table->string('codigo_estudiantil', 50)->nullable()->after('email')->index();
            $table->string('telefono', 50)->nullable()->after('codigo_estudiantil');
            $table->boolean('is_active')->default(true)->after('telefono');

            $table->index(['role_id', 'is_active'], 'idx_users_role_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'codigo_estudiantil', 'telefono', 'is_active']);
        });
    }
};
