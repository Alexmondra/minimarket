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

    /**
     * Calculate the next series prefix for a given empresa and tipo_comprobante.
     * Returns the letter prefix + 3-digit suffix (e.g., 'B002', 'F001').
     */
    public static function siguienteSeriePorEmpresa(int $empresaId, string $tipoComprobante): string
    {
        $prefix = match ($tipoComprobante) {
            'BOLETA' => 'B',
            'FACTURA' => 'F',
            'NOTA_CREDITO' => 'NC',
            'NOTA_DEBITO' => 'ND',
            'TICKET' => 'T',
            default => 'T',
        };

        $existingSeries = static::withTrashed()
            ->whereHas('sucursal', function ($query) use ($empresaId) {
                $query->withTrashed()->where('empresa_id', $empresaId);
            })
            ->where('tipo_comprobante', $tipoComprobante)
            ->pluck('serie');

        $maxIndex = 0;
        foreach ($existingSeries as $s) {
            $digits = preg_replace('/[^0-9]/', '', $s);
            if (is_numeric($digits)) {
                $num = (int) $digits;
                if ($num > $maxIndex) {
                    $maxIndex = $num;
                }
            }
        }

        $nextIndex = $maxIndex + 1;

        if (strlen($prefix) === 1) {
            return $prefix . sprintf('%03d', $nextIndex);
        } else {
            return $prefix . sprintf('%02d', $nextIndex);
        }
    }
}
