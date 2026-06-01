<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lote;
use App\Models\ProductoSucursal;
use App\Support\SucursalContext;
use Carbon\Carbon;

class AlertasBell extends Component
{
    public array $lotesPorVencer = [];
    public array $productosStockBajo = [];
    public int $totalAlertas = 0;

    public function mount(): void
    {
        $this->cargarAlertas();
    }

    public function cargarAlertas(): void
    {
        $context = app(SucursalContext::class);
        $today = Carbon::today();
        $unMesDespues = Carbon::today()->addMonth();

        // 1. Lotes por vencer en los próximos 30 días o por confirmar merma con stock > 0
        $queryLotes = Lote::query()
            ->with(['lotePresentaciones.productoPresentacion.producto'])
            ->whereIn('estado_lote', ['activo', 'vencido', 'por_confirmar'])
            ->where(function ($q) use ($today, $unMesDespues) {
                $q->whereBetween('fecha_vencimiento', [$today, $unMesDespues])
                  ->orWhere('estado_lote', 'por_confirmar');
            })
            ->whereHas('lotePresentaciones', function ($q) {
                $q->where('stock', '>', 0);
            });

        $lotes = $context->applyToQuery($queryLotes)->get();

        $this->lotesPorVencer = $lotes->map(function ($lote) use ($today) {
            $diasRestantes = $today->diffInDays($lote->fecha_vencimiento->startOfDay(), false);
            return [
                'id' => $lote->id,
                'codigo_lote' => $lote->codigo_lote,
                'producto_nombre' => $lote->producto_nombre,
                'fecha_vencimiento' => $lote->fecha_vencimiento->format('d/m/Y'),
                'stock' => $lote->stock_total,
                'dias_restantes' => $diasRestantes,
            ];
        })->toArray();

        // 2. Productos con stock bajo (lote_presentacion.stock <= producto_sucursal.stock_minimo)
        $queryStock = ProductoSucursal::query()
            ->with(['producto', 'lotePresentacion.productoPresentacion'])
            ->where('activo', true)
            ->whereHas('lotePresentacion', function ($q) {
                $q->whereColumn('stock', '<=', 'producto_sucursal.stock_minimo')
                  ->where('stock', '>', 0);
            });

        $stockBajo = $context->applyToQuery($queryStock)->get();

        $this->productosStockBajo = $stockBajo->map(function ($ps) {
            return [
                'id' => $ps->id,
                'producto_nombre' => $ps->producto?->nombre ?? 'Producto',
                'presentacion' => $ps->lotePresentacion?->productoPresentacion?->tipo_presentacion ?? '',
                'stock' => $ps->stock,
                'stock_minimo' => $ps->stock_minimo,
            ];
        })->toArray();

        $this->totalAlertas = count($this->lotesPorVencer) + count($this->productosStockBajo);
    }

    public function render()
    {
        return view('livewire.alertas-bell');
    }
}
