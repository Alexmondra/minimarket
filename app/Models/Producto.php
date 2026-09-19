<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    protected static function booted(): void
    {
        static::creating(function (Producto $producto) {
            if (empty($producto->codigo_interno)) {
                $producto->codigo_interno = static::generarCodigoInterno($producto->nombre ?? 'PROD');
            }
        });
    }

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

    public function productoSucursales()
    {
        return $this->hasMany(ProductoSucursal::class);
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

    /**
     * Genera un código interno (SKU) único para el producto basado en 4 letras del nombre + 4 caracteres aleatorios.
     * Ejemplo: "PIQUEO SNAX" -> "PIQU-8492"
     */
    public static function generarCodigoInterno(string $nombre): string
    {
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $nombre) ?: 'PROD';
        $prefix = strtoupper(substr($clean, 0, 4));
        if (strlen($prefix) < 4) {
            $prefix = str_pad($prefix, 4, 'X');
        }

        do {
            $random = strtoupper(Str::random(4));
            $codigo = "{$prefix}-{$random}";
        } while (static::where('codigo_interno', $codigo)->exists());

        return $codigo;
    }
}
