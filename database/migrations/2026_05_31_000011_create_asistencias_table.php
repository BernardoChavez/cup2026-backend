<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_grupo')->constrained('grupos_nivelacion')->onDelete('cascade');
            $table->foreignId('id_postulante')->constrained('postulantes')->onDelete('cascade');
            $table->date('fecha');
            $table->string('estado', 20);
        });

        // Add CHECK constraint for PostgreSQL
        DB::statement("ALTER TABLE asistencias ADD CONSTRAINT chk_estado_asistencia CHECK (estado IN ('PRESENTE', 'FALTA', 'LICENCIA'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
