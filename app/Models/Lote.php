<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lote extends Model
{
    use SoftDeletes;

    protected $table = 'lotes';

    protected $fillable = [
        'sucursal_id',
        'codigo_lote',
        'producto_nombre',
        'fecha_fabricacion',
        'fecha_vencimiento',
        'ubicacion',
        'precio_compra',
        'observaciones',
        'estado_lote',
    ];

    protected function casts(): array
    {
        return [
            'fecha_fabricacion' => 'date',
            'fecha_vencimiento' => 'date',
            'precio_compra' => 'decimal:2',
        ];
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function lotePresentaciones()
    {
        return $this->hasMany(LotePresentacion::class, 'lote_id');
    }

    public function detalleCompra()
    {
        return $this->hasOne(DetalleCompra::class);
    }

    public function getStockTotalAttribute(): int
    {
        if ($this->relationLoaded('lotePresentaciones')) {
            return (int) $this->lotePresentaciones->sum('stock');
        }

        return (int) $this->lotePresentaciones()->sum('stock');
    }
}
