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
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_postulante')->unique()->constrained('postulantes')->onDelete('cascade');
            
            // Computación
            $table->decimal('comp_n1', 5, 2)->default(0.00);
            $table->decimal('comp_n2', 5, 2)->default(0.00);
            $table->decimal('comp_n3', 5, 2)->default(0.00);
            $table->decimal('comp_promedio', 5, 2)->default(0.00);

            // Matemáticas
            $table->decimal('mat_n1', 5, 2)->default(0.00);
            $table->decimal('mat_n2', 5, 2)->default(0.00);
            $table->decimal('mat_n3', 5, 2)->default(0.00);
            $table->decimal('mat_promedio', 5, 2)->default(0.00);

            // Inglés
            $table->decimal('ing_n1', 5, 2)->default(0.00);
            $table->decimal('ing_n2', 5, 2)->default(0.00);
            $table->decimal('ing_n3', 5, 2)->default(0.00);
            $table->decimal('ing_promedio', 5, 2)->default(0.00);

            // Física
            $table->decimal('fis_n1', 5, 2)->default(0.00);
            $table->decimal('fis_n2', 5, 2)->default(0.00);
            $table->decimal('fis_n3', 5, 2)->default(0.00);
            $table->decimal('fis_promedio', 5, 2)->default(0.00);

            $table->decimal('promedio_final_global', 5, 2)->default(0.00);
        });

        // Add CHECK constraints for grade values to be between 0 and 100
        $subjects = ['comp', 'mat', 'ing', 'fis'];
        foreach ($subjects as $subject) {
            for ($i = 1; $i <= 3; $i++) {
                $field = "{$subject}_n{$i}";
                DB::statement("ALTER TABLE calificaciones ADD CONSTRAINT chk_{$field} CHECK ({$field} BETWEEN 0 AND 100)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
