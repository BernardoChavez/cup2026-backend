<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['id_grupo', 'id_postulante', 'fecha', 'estado'])]
class Asistencia extends Model
{
    protected $table = 'asistencias';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function grupo()
    {
        return $this->belongsTo(GrupoNivelacion::class, 'id_grupo');
    }

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'id_postulante');
    }
}
