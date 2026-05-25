<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleDocumento extends Model
{
    use SoftDeletes;

    protected $table = 'detalle_documentos';

    protected $fillable = [
        'documento_id',
        'lote_id',
        'producto_id',
        'producto_nombre',
        'producto_presentacion_id',
        'producto_sucursal_id',
        'cantidad',
        'precio_unitario',
        'valor_unitario',
        'igv',
        'total_igv',
        'tipo_afectacion',
        'descuento_unitario',
        'subtotal_bruto',
        'subtotal_descuento',
        'subtotal_neto',
        'total_linea',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:3',
            'precio_unitario' => 'decimal:2',
            'valor_unitario' => 'decimal:2',
            'igv' => 'decimal:2',
            'total_igv' => 'decimal:2',
            'descuento_unitario' => 'decimal:2',
            'subtotal_bruto' => 'decimal:2',
            'subtotal_descuento' => 'decimal:2',
            'subtotal_neto' => 'decimal:2',
            'total_linea' => 'decimal:2',
        ];
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function presentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }

    public function productoSucursal()
    {
        return $this->belongsTo(ProductoSucursal::class);
    }
}
