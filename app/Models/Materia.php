<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['codigo', 'nombre'])]
class Materia extends Model
{
    protected $table = 'materias';

    public $timestamps = false;

    public function grupos()
    {
        return $this->hasMany(GrupoNivelacion::class, 'id_materia');
    }
}
