<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'id_usuario',
    'id_gestion_academica',
    'ci',
    'fecha_nacimiento',
    'sexo',
    'direccion',
    'telefono',
    'colegio_procedencia',
    'ciudad',
    'titulo_bachiller',
    'otros_requisitos',
    'id_carrera_opcion1',
    'id_carrera_opcion2',
    'id_carrera_asignada',
    'pago_procesado',
    'nro_transaccion_pago',
    'monto_pago',
    'estado_final'
])]
class Postulante extends Model
{
    protected $table = 'postulantes';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'titulo_bachiller' => 'boolean',
            'pago_procesado' => 'boolean',
            'monto_pago' => 'decimal:2',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function gestionAcademica()
    {
        return $this->belongsTo(GestionAcademica::class, 'id_gestion_academica');
    }

    public function carreraOpcion1()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera_opcion1');
    }

    public function carreraOpcion2()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera_opcion2');
    }

    public function carreraAsignada()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera_asignada');
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class, 'id_postulante');
    }

    public function grupos()
    {
        return $this->belongsToMany(GrupoNivelacion::class, 'inscritos_grupos', 'id_postulante', 'id_grupo');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'id_postulante');
    }
}
