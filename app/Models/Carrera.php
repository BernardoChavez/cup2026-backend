<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['codigo', 'nombre', 'cupos_maximos'])]
class Carrera extends Model
{
    protected $table = 'carreras';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function postulantesOpcion1()
    {
        return $this->hasMany(Postulante::class, 'id_carrera_opcion1');
    }

    public function postulantesOpcion2()
    {
        return $this->hasMany(Postulante::class, 'id_carrera_opcion2');
    }

    public function postulantesAsignados()
    {
        return $this->hasMany(Postulante::class, 'id_carrera_asignada');
    }
}
