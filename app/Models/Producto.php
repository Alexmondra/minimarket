<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'categoria_id',
        'marca_id',
        'codigo_interno',
        'nombre',
        'slug',
        'descripcion',
        'afecto_igv',
        'activo',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function presentaciones()
    {
        return $this->hasMany(ProductoPresentacion::class);
    }

    public function primeraPresentacion()
    {
        return $this->hasOne(ProductoPresentacion::class)->ofMany('id', 'min');
    }

    /**
     * Get the priority presentation with an image.
     * Priority order:
     * 1. Presentation with unit abbreviation 'und' (Unidad) that has an image
     * 2. Any other presentation with an image
     * 3. null if none found
     *
     * Uses query builder directly for reliability regardless of eager loading state.
     */
    public function presentacionPrioritaria()
    {
        // Priority 1: presentation with 'und' unidad de medida that has image
        $unitPresentation = $this->presentaciones()
            ->whereHas('unidadMedida', function ($query) {
                $query->where('abreviatura', 'und');
            })
            ->whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->first();

        if ($unitPresentation) {
            return $unitPresentation;
        }

        // Priority 2: fallback to any presentation with image
        return $this->presentaciones()
            ->whereNotNull('imagen')
            ->where('imagen', '!=', '')
            ->first();
    }

    /**
     * Get the image URL following the priority logic:
     * 1. Image from unit presentation (unidad)
     * 2. Image from any other presentation
     * 3. null if none found
     */
    public function getImagenPrioritariaAttribute(): ?string
    {
        $presentacion = $this->presentacionPrioritaria();
        
        if ($presentacion && $presentacion->imagen) {
            return $presentacion->imagen_url;
        }
        
        return null;
    }

    /**
     * Get the thumbnail URL for the product image.
     * This is used by Filament ImageColumn to display thumbnails.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->imagen_prioritaria;
    }

    /**
     * Get all presentations that have images, for the modal.
     * Filters from loaded collection to avoid N+1 queries.
     */
    public function getPresentacionesConImagenAttribute()
    {
        return $this->presentaciones
            ->whereNotNull('imagen')
            ->where('imagen', '!=', '');
    }
}
