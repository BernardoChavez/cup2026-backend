<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['anio', 'periodo', 'activo'])]
class GestionAcademica extends Model
{
    protected $table = 'gestiones_academicas';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function postulantes()
    {
        return $this->hasMany(Postulante::class, 'id_gestion_academica');
    }

    public function grupos()
    {
        return $this->hasMany(GrupoNivelacion::class, 'id_gestion_academica');
    }
}
