<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sunat extends Model
{
    use SoftDeletes;

    protected $table = 'sunat';

    protected $fillable = [
        'empresa_id',
        'documento_id',
        'estado_sunat',
        'codigo_respuesta_sunat',
        'mensaje_sunat',
        'fecha_envio',
        'fecha_respuesta',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
