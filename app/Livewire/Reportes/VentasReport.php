<?php

namespace App\Livewire\Reportes;

use App\Models\Documento;
use App\Support\Reportes\MetricCalculator;
use App\Support\Reportes\ReporteQueryBuilder;
use App\Support\SucursalContext;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class VentasReport extends Component
{
    use WithPagination;

    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public string $medioPago = '';
    public string $search = '';

    public array $stats = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->fechaDesde = today()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedFechaDesde(): void { $this->resetPage(); $this->loadStats(); }
    public function updatedFechaHasta(): void { $this->resetPage(); $this->loadStats(); }
    public function updatedMedioPago(): void { $this->resetPage(); $this->loadStats(); }
    public function updatedSearch(): void { $this->resetPage(); }

    public function loadStats(): void
    {
        $qb = app(ReporteQueryBuilder::class);
        $query = $this->applyFilters($qb->ventasBase());

        $this->stats = [
            'total_ventas' => number_format((clone $query)->sum('total_neto'), 2),
            'cantidad' => (clone $query)->count(),
            'promedio' => (clone $query)->count() > 0
                ? number_format((clone $query)->avg('total_neto'), 2)
                : '0.00',
        ];
        $this->loaded = true;
    }

    public function exportar(): void
    {
        // Placeholder for export functionality
    }

    private function applyFilters($query)
    {
        if ($this->fechaDesde) {
            $query->whereDate('fecha_emision', '>=', $this->fechaDesde);
        }
        if ($this->fechaHasta) {
            $query->whereDate('fecha_emision', '<=', $this->fechaHasta);
        }
        if ($this->medioPago) {
            $query->where('medio_pago', $this->medioPago);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$this->search}%"))
                  ->orWhere('serie', 'like', "%{$this->search}%")
                  ->orWhere('numero', 'like', "%{$this->search}%");
            });
        }
        return $query;
    }

    public function render()
    {
        $qb = app(ReporteQueryBuilder::class);
        $query = $this->applyFilters($qb->ventasBase())
            ->with(['cliente', 'user', 'sucursal'])
            ->latest('fecha_emision');

        $ventas = $query->paginate(15);

        // Payment methods for filter dropdown
        $metodos = Documento::whereNotNull('medio_pago')
            ->distinct()
            ->pluck('medio_pago')
            ->toArray();

        return view('livewire.reportes.ventas-report', [
            'ventas' => $ventas,
            'metodos' => $metodos,
        ]);
    }
}
