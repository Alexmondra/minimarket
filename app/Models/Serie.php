<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Serie extends Model
{
    use SoftDeletes;

    protected $table = 'series';

    protected $fillable = [
        'sucursal_id',
        'tipo_comprobante',
        'serie',
        'correlativo',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
