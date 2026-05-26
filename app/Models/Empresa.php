<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ruc',
        'logo',
        'incluido_tributo',
        'razon_social',
        'direccion_fiscal',
        'entorno',
    ];

    protected function casts(): array
    {
        return [
            'incluido_tributo' => 'boolean',
            'entorno' => 'boolean',
        ];
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function empresaConfig()
    {
        return $this->hasOne(EmpresaConfig::class);
    }
}
