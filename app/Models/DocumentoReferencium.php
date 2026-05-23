<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoReferencium extends Model
{
    use SoftDeletes;

    protected $table = 'documento_referencia';

    protected $fillable = [
        'documento_id',
        'tipo_relacion',
        'documento_referenciado_id',
        'tipo_documento_ref',
        'serie_ref',
        'numero_ref',
        'motivo_codigo',
        'motivo_descripcion',
        'fecha_emision_ref',
        'moneda_ref',
    ];

    public function documento()
    {
        return $this->belongsTo(Documento::class);
    }

    public function documentoReferenciado()
    {
        return $this->belongsTo(Documento::class, 'documento_referenciado_id');
    }
}
