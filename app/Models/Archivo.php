<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use SoftDeletes;

    protected $table = 'archivos';

    protected $fillable = [
        'documento_id',
        'tipo_archivo',
        'proveedor_almacenamiento',
        'bucket',
        'ruta_archivo',
        'nombre_archivo',
    ];
}
