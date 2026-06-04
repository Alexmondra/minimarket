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

        $agg = (clone $query)->selectRaw('COALESCE(SUM(total_neto), 0) as total, COUNT(*) as cant, COALESCE(AVG(total_neto), 0) as prom')->first();

        $this->stats = [
            'total_ventas' => number_format((float) $agg->total, 2),
            'cantidad' => (int) $agg->cant,
            'promedio' => $agg->cant > 0 ? number_format((float) $agg->prom, 2) : '0.00',
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

        // Payment methods for filter dropdown (cached 1 hour)
        $metodos = cache()->remember('ventas_medios_pago', 3600, function () {
            return Documento::whereNotNull('medio_pago')
                ->distinct()
                ->pluck('medio_pago')
                ->toArray();
        });

        return view('livewire.reportes.ventas-report', [
            'ventas' => $ventas,
            'metodos' => $metodos,
        ]);
    }
}
