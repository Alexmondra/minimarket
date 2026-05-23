<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaConfig extends Model
{
    use SoftDeletes;

    protected $table = 'empresa_config';

    protected $fillable = [
        'empresa_id',
        'tipo_certificado',
        'certificado',
        'certificado_pass',
        'user_sol',
        'pass_sol',
        'sunat_client_id',
        'sunat_client_secret',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
