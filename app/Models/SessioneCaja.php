<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessioneCaja extends Model
{
    use SoftDeletes;

    protected $table = 'sesiones_caja';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'user_id',
        'fecha_apertura',
        'saldo_inicial',
        'fecha_cierre',
        'saldo_teorico',
        'saldo_real',
        'diferencia',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'datetime',
            'fecha_cierre' => 'datetime',
            'saldo_inicial' => 'decimal:2',
            'saldo_teorico' => 'decimal:2',
            'saldo_real' => 'decimal:2',
            'diferencia' => 'decimal:2',
            'estado' => 'boolean',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'caja_sesion_id');
    }
}
