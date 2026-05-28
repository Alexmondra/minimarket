<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoInventario extends Model
{
    use SoftDeletes;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'producto_nombre',
        'producto_presentacion_id',
        'tipo',
        'cantidad',
        'motivo',
        'referencia',
        'user_id',
        'stock_final',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function productoPresentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
