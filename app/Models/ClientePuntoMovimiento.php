<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientePuntoMovimiento extends Model
{
    protected $table = 'cliente_punto_movimientos';

    protected $fillable = [
        'cliente_id',
        'empresa_id',
        'sucursal_id',
        'documento_id',
        'user_id',
        'tipo',
        'puntos',
        'monto_descuento',
        'motivo',
    ];

    protected function casts(): array
    {
        return [
            'puntos' => 'integer',
            'monto_descuento' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
