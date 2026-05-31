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
        'hash',
        'fecha_envio',
        'fecha_respuesta',
    ];

    protected function casts(): array
    {
        return [
            'estado_sunat' => 'boolean',
            'fecha_envio' => 'datetime',
            'fecha_respuesta' => 'datetime',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }
}
