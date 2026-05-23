<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleCompra extends Model
{
    use SoftDeletes;

    protected $table = 'detalle_compras';

    protected $fillable = [
        'compra_id',
        'lote_id',
        'precio_compra',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
        ];
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function getCantidadTotalAttribute(): int
    {
        if (!$this->lote) {
            return 0;
        }

        return $this->lote->stock_total;
    }
}
