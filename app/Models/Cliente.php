<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo_documento',
        'documento',
        'nombre',
        'apellido',
        'razon_social',
        'sexo',
        'fecha_nacimiento',
        'telefono',
        'email',
        'direccion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }

    public function puntos()
    {
        return $this->hasMany(ClientePunto::class);
    }

    public function movimientosPuntos()
    {
        return $this->hasMany(ClientePuntoMovimiento::class);
    }
}
