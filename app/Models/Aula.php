<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['nombre', 'capacidad_maxima'])]
class Aula extends Model
{
    protected $table = 'aulas';

    public $timestamps = false;

    public function grupos()
    {
        return $this->hasMany(GrupoNivelacion::class, 'id_aula');
    }
}
