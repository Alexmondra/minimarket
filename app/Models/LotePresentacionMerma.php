<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotePresentacionMerma extends Model
{
    protected $table = 'lote_presentacion_mermas';

    protected $fillable = [
        'lote_presentacion_id',
        'cantidad',
        'tipo_merma',
        'motivo',
        'user_id',
    ];

    public function lotePresentacion()
    {
        return $this->belongsTo(LotePresentacion::class, 'lote_presentacion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
