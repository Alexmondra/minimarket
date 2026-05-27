<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoPresentacionBarra extends Model
{
    protected $table = 'producto_presentacion_barras';

    protected $fillable = [
        'producto_presentacion_id',
        'codigo_barra',
    ];

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }
}
