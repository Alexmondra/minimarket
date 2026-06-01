<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use SoftDeletes;

    protected $table = 'marcas';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'descripcion',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'marca_id');
    }
}
