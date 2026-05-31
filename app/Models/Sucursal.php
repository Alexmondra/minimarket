<?php

namespace App\Models;

use App\Models\Serie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use SoftDeletes;

    protected $table = 'sucursales';

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Sucursal $sucursal) {
            if (empty($sucursal->codigo)) {
                $existingCodes = static::withTrashed()
                    ->where('empresa_id', $sucursal->empresa_id)
                    ->pluck('codigo')
                    ->filter(fn($c) => preg_match('/^\d{4}$/', $c))
                    ->map(fn($c) => (int)$c);

                $nextCodeInt = $existingCodes->isEmpty() ? 0 : $existingCodes->max() + 1;
                $sucursal->codigo = sprintf('%04d', $nextCodeInt);
            }
        });

        static::created(function (Sucursal $sucursal) {
            // Find maximum series suffix for the same empresa
            $nextSerie = Serie::siguienteSeriePorEmpresa($sucursal->empresa_id, 'BOLETA');
            $index = (int) substr($nextSerie, 1); // e.g., 1 from "B001"

            $suffix3 = sprintf('%03d', $index);
            $suffix2 = sprintf('%02d', $index);

            // Generate the default series records
            $seriesToCreate = [
                [
                    'tipo_comprobante' => 'BOLETA',
                    'serie' => 'B' . $suffix3,
                    'correlativo' => 1,
                ],
                [
                    'tipo_comprobante' => 'FACTURA',
                    'serie' => 'F' . $suffix3,
                    'correlativo' => 1,
                ],
                [
                    'tipo_comprobante' => 'NOTA_CREDITO_BOLETA',
                    'serie' => 'BC' . $suffix2,
                    'correlativo' => 1,
                ],
                [
                    'tipo_comprobante' => 'NOTA_CREDITO_FACTURA',
                    'serie' => 'FC' . $suffix2,
                    'correlativo' => 1,
                ],
                [
                    'tipo_comprobante' => 'TICKET',
                    'serie' => 'T' . $suffix3,
                    'correlativo' => 1,
                ],
            ];

            foreach ($seriesToCreate as $serieData) {
                $sucursal->series()->create($serieData);
            }

            // Auto-assign the creator (logged-in user) to the new branch
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->empresa_id === $sucursal->empresa_id) {
                    $user->sucursales()->attach($sucursal->id);
                }
            }
        });

        static::saving(function (Sucursal $sucursal) {
            $ubigeoCode = $sucursal->ubigeo;
            if ($ubigeoCode) {
                $ubigeo = Ubigeo::where('ubigeo', $ubigeoCode)->first();
                if ($ubigeo) {
                    $departamento = strtoupper(trim($ubigeo->departamento));
                    $exempt = ['LORETO', 'MADRE DE DIOS', 'UCAYALI', 'SAN MARTIN', 'AMAZONAS'];
                    if (in_array($departamento, $exempt)) {
                        $sucursal->impuesto_porcentaje = 0.00;
                    } else {
                        $sucursal->impuesto_porcentaje = 18.00;
                    }
                } else {
                    $sucursal->impuesto_porcentaje = 18.00;
                }
            } else {
                $sucursal->impuesto_porcentaje = 18.00;
            }
        });
    }

    protected $fillable = [
        'empresa_id',
        'codigo',
        'ubigeo',
        'direccion',
        'telefono',
        'email',
        'nombre_sucursal',
        'imagen_sucursal',
        'impuesto_porcentaje',
        'configuracion_extra',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'configuracion_extra' => 'json',
            'activo' => 'boolean',
            'impuesto_porcentaje' => 'decimal:2',
        ];
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function ubigeoRel()
    {
        return $this->belongsTo(Ubigeo::class, 'ubigeo', 'ubigeo');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'sucursal_user')
            ->withTimestamps()
            ->withPivot('deleted_at')
            ->wherePivotNull('deleted_at');
    }

    public function series()
    {
        return $this->hasMany(Serie::class);
    }
}
