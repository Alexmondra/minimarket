<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'sucursal_id',
        'proveedor_id',
        'user_id',
        'tipo_comprobante',
        'numero_factura_proveedor',
        'fecha_recepcion',
        'costo_total_factura',
        'observaciones',
        'archivo_comprobante',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_recepcion' => 'date',
        ];
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalle()
    {
        return $this->hasMany(DetalleCompra::class);
    }
}
