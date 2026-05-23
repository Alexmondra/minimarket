<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ubigeo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo',
        'departamento',
        'provincia',
        'distrito',
        'capital',
        'region_natural',
    ];

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class, 'ubigeo');
    }
}
