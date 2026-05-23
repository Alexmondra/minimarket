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
        'producto_nombre',
        'producto_presentacion_id',
        'precio_unitario',
        'valor_unitario',
        'igv',
        'tipo_afectacion',
        'descuento_unitario',
        'subtotal_bruto',
        'subtotal_descuento',
        'subtotal_neto',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }

    public function presentacion()
    {
        return $this->belongsTo(ProductoPresentacion::class, 'producto_presentacion_id');
    }
}
