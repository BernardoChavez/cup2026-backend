<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['id_gestion_academica', 'id_docente', 'id_aula', 'id_materia', 'nombre', 'horario'])]
class GrupoNivelacion extends Model
{
    protected $table = 'grupos_nivelacion';

    public $timestamps = false;

    public function gestionAcademica()
    {
        return $this->belongsTo(GestionAcademica::class, 'id_gestion_academica');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente');
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }

    public function postulantes()
    {
        return $this->belongsToMany(Postulante::class, 'inscritos_grupos', 'id_grupo', 'id_postulante');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_grupo');
    }
}
