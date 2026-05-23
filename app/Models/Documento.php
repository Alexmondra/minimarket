<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $table = 'documentos';

    protected $fillable = [
        'caja_sesion_id',
        'sucursal_id',
        'empresa_id',
        'cliente_id',
        'user_id',
        'tipo_comprobante',
        'serie',
        'numero',
        'fecha_emision',
        'total_bruto',
        'total_descuento',
        'total_neto',
        'op_gravada',
        'op_exonerada',
        'op_inafecta',
        'total_igv',
        'porcentaje_igv',
        'tipo_moneda',
        'medio_pago',
        'monto_recibido',
        'referencia_pago',
        'estado',
        'observaciones',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalle()
    {
        return $this->hasMany(DetalleDocumento::class);
    }
}
