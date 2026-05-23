<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoPresentacion extends Model
{
    use SoftDeletes;

    protected $table = 'producto_presentacion';

    protected $fillable = [
        'producto_id',
        'unidad_medida_id',
        'cantidad',
        'tipo_presentacion',
        'imagen',
        'codigo_barra',
        'es_pesable',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UniMedida::class, 'unidad_medida_id');
    }

    /**
     * Get the full URL of the imagen.
     */
    public function getImagenUrlAttribute(): ?string
    {
        if (! filled($this->imagen)) {
            return null;
        }

        $path = ltrim($this->imagen, '/');

        $path = str_replace([
            'storage/public/',
            'storage/',
            'public/',
        ], '', $path);

        return url('/storage/' . $path);
    }
}
