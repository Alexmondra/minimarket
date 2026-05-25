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
        'subtotal',
        'total_neto',
        'op_gravada',
        'op_exonerada',
        'op_inafecta',
        'total_igv',
        'porcentaje_igv',
        'tipo_moneda',
        'medio_pago',
        'monto_recibido',
        'vuelto',
        'puntos_ganados',
        'puntos_canjeados',
        'descuento_puntos',
        'referencia_pago',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'total_bruto' => 'decimal:2',
            'total_descuento' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total_neto' => 'decimal:2',
            'op_gravada' => 'decimal:2',
            'op_exonerada' => 'decimal:2',
            'op_inafecta' => 'decimal:2',
            'total_igv' => 'decimal:2',
            'porcentaje_igv' => 'decimal:2',
            'monto_recibido' => 'decimal:2',
            'vuelto' => 'decimal:2',
            'descuento_puntos' => 'decimal:2',
            'puntos_ganados' => 'integer',
            'puntos_canjeados' => 'integer',
        ];
    }

    public function cajaSesion()
    {
        return $this->belongsTo(SessioneCaja::class, 'caja_sesion_id');
    }

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

    public function detalles()
    {
        return $this->hasMany(DetalleDocumento::class);
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    public function sunat()
    {
        return $this->hasOne(Sunat::class);
    }
}
