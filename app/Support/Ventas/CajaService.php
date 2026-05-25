<?php

namespace App\Support\Ventas;

use App\Models\SessioneCaja;

class CajaService
{
    public function cajaAbierta(int $userId, int $sucursalId): ?SessioneCaja
    {
        return SessioneCaja::query()
            ->where('user_id', $userId)
            ->where('sucursal_id', $sucursalId)
            ->where('estado', true)
            ->whereNull('fecha_cierre')
            ->latest('fecha_apertura')
            ->first();
    }

    public function requireCajaAbierta(int $userId, int $sucursalId): SessioneCaja
    {
        return $this->cajaAbierta($userId, $sucursalId)
            ?? throw new \RuntimeException('Debes abrir una caja antes de registrar ventas.');
    }

    public function saldoTeorico(SessioneCaja $caja): float
    {
        $ventas = $caja->documentos()
            ->where('estado', true)
            ->sum('total_neto');

        return round((float) $caja->saldo_inicial + (float) $ventas, 2);
    }
}
