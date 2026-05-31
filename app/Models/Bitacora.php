<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['id_usuario', 'accion', 'tabla_afectada', 'registro_id', 'descripcion', 'ip_direccion'])]
class Bitacora extends Model
{
    protected $table = 'bitacora';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
