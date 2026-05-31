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
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->unique()->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('id_gestion_academica')->constrained('gestiones_academicas');
            $table->string('ci', 20)->unique();
            $table->date('fecha_nacimiento');
            $table->string('sexo', 10);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('colegio_procedencia', 150)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->boolean('titulo_bachiller')->default(false);
            $table->text('otros_requisitos')->nullable();
            
            $table->foreignId('id_carrera_opcion1')->constrained('carreras');
            $table->foreignId('id_carrera_opcion2')->constrained('carreras');
            $table->foreignId('id_carrera_asignada')->nullable()->constrained('carreras');
            
            $table->boolean('pago_procesado')->default(false);
            $table->string('nro_transaccion_pago', 100)->unique()->nullable();
            $table->decimal('monto_pago', 10, 2)->nullable();
            $table->string('estado_final', 20)->default('CURSANDO');
        });

        // Add CHECK constraints for PostgreSQL
        DB::statement("ALTER TABLE postulantes ADD CONSTRAINT chk_sexo CHECK (sexo IN ('M', 'F'))");
        DB::statement("ALTER TABLE postulantes ADD CONSTRAINT chk_estado_final CHECK (estado_final IN ('APROBADO', 'REPROBADO', 'CURSANDO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};
