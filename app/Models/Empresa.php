<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Empresa extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Empresa $empresa) {
            if (empty($empresa->slug)) {
                $empresa->slug = static::generateUniqueSlug($empresa->razon_social);
            }
        });
    }

    public static function generateUniqueSlug(string $razonSocial): string
    {
        // Remove common business prefixes/suffixes to get a short, readable slug
        $limpio = preg_replace(
            ['/\bMINIMARKET\b/i', '/\bS\.?A\.?C\.?\b/i', '/\bS\.?A\.?\b/i', '/\bE\.?I\.?R\.?L\.?\b/i', '/\bS\.?R\.?L\.?\b/i', '/\bS\.?C\.?R\.?L\.?\b/i', '/\bR\.?L\.?\b/i', '/["\'.]/'],
            '',
            $razonSocial
        );
        $limpio = trim(preg_replace('/\s+/', ' ', $limpio));

        $base = Str::slug($limpio);
        $base = $base ?: 'empresa';
        if (mb_strlen($base) > 25) {
            $base = mb_substr($base, 0, 25);
        }
        $base = rtrim($base, '-');

        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    protected $fillable = [
        'ruc',
        'logo',
        'incluido_tributo',
        'razon_social',
        'slug',
        'direccion_fiscal',
        'entorno',
    ];

    protected function casts(): array
    {
        return [
            'incluido_tributo' => 'boolean',
            'entorno' => 'boolean',
        ];
    }

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function empresaConfig()
    {
        return $this->hasOne(EmpresaConfig::class);
    }
}
