<?php

namespace App\Support\Ventas;

use App\Models\Cliente;
use App\Models\ClientePunto;
use App\Models\ClientePuntoMovimiento;
use App\Models\Documento;

class PuntosService
{
    public const SOLES_POR_PUNTO_GANADO = 10;

    public const VALOR_DESCUENTO_POR_PUNTO = 0.10;

    public function puntosDisponibles(?Cliente $cliente, int $empresaId): int
    {
        if (! $cliente) {
            return 0;
        }

        return (int) ClientePunto::query()
            ->forEmpresa($empresaId)
            ->where('cliente_id', $cliente->id)
            ->sum('puntos');
    }

    public function descuentoPorPuntos(int $puntos): float
    {
        return round($puntos * self::VALOR_DESCUENTO_POR_PUNTO, 2);
    }

    public function puntosGanados(float $totalPagado): int
    {
        return max((int) floor($totalPagado / self::SOLES_POR_PUNTO_GANADO), 0);
    }

    public function registrarCanje(
        Cliente $cliente,
        int $empresaId,
        int $sucursalId,
        int $userId,
        int $puntos,
        float $descuento,
        Documento $documento
    ): void {
        if ($puntos <= 0) {
            return;
        }

        $restante = $puntos;

        ClientePunto::query()
            ->where('cliente_id', $cliente->id)
            ->where('empresa_id', $empresaId)
            ->where('puntos', '>', 0)
            ->orderByDesc('puntos')
            ->get()
            ->each(function (ClientePunto $saldo) use (&$restante): void {
                if ($restante <= 0) {
                    return;
                }

                $consumo = min($restante, $saldo->puntos);
                $saldo->decrement('puntos', $consumo);
                $restante -= $consumo;
            });

        ClientePuntoMovimiento::create([
            'cliente_id' => $cliente->id,
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            'documento_id' => $documento->id,
            'user_id' => $userId,
            'tipo' => 'canje',
            'puntos' => -$puntos,
            'monto_descuento' => $descuento,
            'motivo' => 'Canje aplicado en venta',
        ]);
    }

    public function registrarAcumulacion(
        Cliente $cliente,
        int $empresaId,
        int $sucursalId,
        int $userId,
        int $puntos,
        Documento $documento
    ): void {
        if ($puntos <= 0) {
            return;
        }

        $saldo = ClientePunto::query()->firstOrCreate(
            [
                'cliente_id' => $cliente->id,
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
            ],
            ['puntos' => 0]
        );

        $saldo->increment('puntos', $puntos);

        ClientePuntoMovimiento::create([
            'cliente_id' => $cliente->id,
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            'documento_id' => $documento->id,
            'user_id' => $userId,
            'tipo' => 'acumulacion',
            'puntos' => $puntos,
            'monto_descuento' => 0,
            'motivo' => 'Puntos ganados por venta',
        ]);
    }

    /**
     * Revierte puntos acumulados por una venta cuando esta es anulada.
     */
    public function registrarReversion(
        Cliente $cliente,
        int $empresaId,
        int $sucursalId,
        int $userId,
        int $puntos,
        string $motivo = 'Anulación de venta'
    ): void {
        if ($puntos <= 0) {
            return;
        }

        $restante = $puntos;

        ClientePunto::query()
            ->where('cliente_id', $cliente->id)
            ->where('empresa_id', $empresaId)
            ->where('puntos', '>', 0)
            ->orderByDesc('puntos')
            ->get()
            ->each(function (ClientePunto $saldo) use (&$restante): void {
                if ($restante <= 0) {
                    return;
                }

                $reversion = min($restante, $saldo->puntos);
                $saldo->decrement('puntos', $reversion);
                $restante -= $reversion;
            });

        ClientePuntoMovimiento::create([
            'cliente_id' => $cliente->id,
            'empresa_id' => $empresaId,
            'sucursal_id' => $sucursalId,
            'documento_id' => null,
            'user_id' => $userId,
            'tipo' => 'reversion',
            'puntos' => -$puntos,
            'monto_descuento' => 0,
            'motivo' => $motivo,
        ]);
    }
}
