<?php

namespace App\Livewire\Compras\Components;

use App\Models\DetalleCompra;
use Livewire\Component;

class HistorialProducto extends Component
{
    public bool $showModal = false;
    public ?int $productoId = null;
    public array $historialCompras = [];

    protected $listeners = ['verHistorialProducto' => 'cargarHistorial'];

    public function cargarHistorial(int $productoId): void
    {
        $this->productoId = $productoId;
        $this->historialCompras = DetalleCompra::query()
            ->whereHas('lote.lotePresentaciones.productoPresentacion', fn ($query) => $query->where('producto_id', $productoId))
            ->with(['compra.proveedor', 'lote.lotePresentaciones'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DetalleCompra $detalle): array => [
                'proveedor' => $detalle->compra?->proveedor?->nombre ?? 'N/A',
                'created_at' => $detalle->created_at?->format('Y-m-d'),
                'codigo_lote' => $detalle->lote?->codigo_lote,
                'cantidad' => (int) ($detalle->lote?->lotePresentaciones?->sum('stock') ?? 0),
                'total' => (float) $detalle->precio_compra,
            ])
            ->toArray();

        $this->showModal = count($this->historialCompras) > 0;
    }

    public function cerrar(): void
    {
        $this->showModal = false;
        $this->productoId = null;
        $this->historialCompras = [];
    }

    public function render()
    {
        return view('livewire.compras.components.historial-producto');
    }
}
