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
        Schema::create('inscritos_grupos', function (Blueprint $table) {
            $table->foreignId('id_postulante')->constrained('postulantes')->onDelete('cascade');
            $table->foreignId('id_grupo')->constrained('grupos_nivelacion')->onDelete('cascade');
            $table->primary(['id_postulante', 'id_grupo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscritos_grupos');
    }
};
