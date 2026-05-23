<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use SoftDeletes;
    protected $table = 'sucursales';
    
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
        return $this->belongsTo(Ubigeo::class, 'ubigeo');
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
