<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoSucursal extends Model
{
    use SoftDeletes;

    protected $table = 'producto_sucursal';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'lote_presentacion_id',
        'stock_minimo',
        'precio',
        'minimo_mayorista',
        'precio_mayorista',
        'activo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function lotePresentacion()
    {
        return $this->belongsTo(LotePresentacion::class, 'lote_presentacion_id');
    }

    /**
     * Get the current stock for this product-sucursal-presentation.
     * Calculated from the last movement's stock_final.
     */
    public function getStockAttribute(): int
    {
        return (int) ($this->lotePresentacion?->stock ?? 0);
    }
}
