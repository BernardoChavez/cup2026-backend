<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'id_postulante',
    'comp_n1', 'comp_n2', 'comp_n3', 'comp_promedio',
    'mat_n1', 'mat_n2', 'mat_n3', 'mat_promedio',
    'ing_n1', 'ing_n2', 'ing_n3', 'ing_promedio',
    'fis_n1', 'fis_n2', 'fis_n3', 'fis_promedio',
    'promedio_final_global'
])]
class Calificacion extends Model
{
    protected $table = 'calificaciones';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'comp_n1' => 'decimal:2', 'comp_n2' => 'decimal:2', 'comp_n3' => 'decimal:2', 'comp_promedio' => 'decimal:2',
            'mat_n1' => 'decimal:2', 'mat_n2' => 'decimal:2', 'mat_n3' => 'decimal:2', 'mat_promedio' => 'decimal:2',
            'ing_n1' => 'decimal:2', 'ing_n2' => 'decimal:2', 'ing_n3' => 'decimal:2', 'ing_promedio' => 'decimal:2',
            'fis_n1' => 'decimal:2', 'fis_n2' => 'decimal:2', 'fis_n3' => 'decimal:2', 'fis_promedio' => 'decimal:2',
            'promedio_final_global' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Calificacion $calificacion) {
            // Averages per subject: (N1 + N2 + N3) / 3
            $calificacion->comp_promedio = round(($calificacion->comp_n1 + $calificacion->comp_n2 + $calificacion->comp_n3) / 3, 2);
            $calificacion->mat_promedio = round(($calificacion->mat_n1 + $calificacion->mat_n2 + $calificacion->mat_n3) / 3, 2);
            $calificacion->ing_promedio = round(($calificacion->ing_n1 + $calificacion->ing_n2 + $calificacion->ing_n3) / 3, 2);
            $calificacion->fis_promedio = round(($calificacion->fis_n1 + $calificacion->fis_n2 + $calificacion->fis_n3) / 3, 2);

            // Global final average: average of all subject averages
            $calificacion->promedio_final_global = round(
                ($calificacion->comp_promedio + $calificacion->mat_promedio + $calificacion->ing_promedio + $calificacion->fis_promedio) / 4,
                2
            );
        });
    }

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante');
    }
}
