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
        Schema::create('grupos_nivelacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_gestion_academica')->constrained('gestiones_academicas');
            $table->foreignId('id_docente')->nullable()->constrained('docentes')->onDelete('set null');
            $table->foreignId('id_aula')->nullable()->constrained('aulas')->onDelete('set null');
            $table->foreignId('id_materia')->constrained('materias');
            $table->string('nombre', 100);
            $table->string('horario', 100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos_nivelacion');
    }
};
