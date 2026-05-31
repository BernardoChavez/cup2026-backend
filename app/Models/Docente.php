<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['id_usuario', 'profesional_area', 'maestria', 'diplomado_edu_sup', 'contratado'])]
class Docente extends Model
{
    protected $table = 'docentes';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'profesional_area' => 'boolean',
            'maestria' => 'boolean',
            'diplomado_edu_sup' => 'boolean',
            'contratado' => 'boolean',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function grupos()
    {
        return $this->hasMany(GrupoNivelacion::class, 'id_docente');
    }
}
