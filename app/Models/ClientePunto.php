<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientePunto extends Model
{
    use SoftDeletes;

    protected $table = 'cliente_puntos';

    protected $fillable = [
        'cliente_id',
        'empresa_id',
        'sucursal_id',
        'puntos',
    ];

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
}
