<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotePresentacion extends Model
{
    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_MERMA = 'merma';

    protected $table = 'lote_presentacion';

    protected $fillable = [
        'lote_id',
        'producto_presentacion_id',
        'stock_inicial',
        'stock',
        'precio_compra',
        'precio_oferta',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_oferta' => 'decimal:2',
        ];
    }

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

    public function mermas()
    {
        return $this->hasMany(LotePresentacionMerma::class, 'lote_presentacion_id');
    }
}
