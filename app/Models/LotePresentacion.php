<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotePresentacion extends Model
{
    protected $table = 'lote_presentacion';

    protected $fillable = [
        'lote_id',
        'producto_presentacion_id',
        'stock',
        'precio_oferta',
    ];

    public function lote()
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }

    public function productoSucursal()
    {
        return $this->hasOne(ProductoSucursal::class, 'lote_presentacion_id');
    }
}
