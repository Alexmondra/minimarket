<?php

namespace App\Livewire\Reportes;

use App\Models\Documento;
use App\Support\Reportes\ReporteQueryBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public string $tipoReporte = 'resumen';

    public string $tipoComprobanteFiltro = '';

    public function mount(): void
    {
        $this->fechaDesde = today()->startOfMonth()->format('Y-m-d');
        $this->fechaHasta = today()->format('Y-m-d');
        $this->loadStats();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedMedioPago(): void
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTipoReporte(): void
    {
        $this->resetPage();
    }

    public function updatedTipoComprobanteFiltro(): void
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $qb = app(ReporteQueryBuilder::class);
        $query = $this->applyFilters($qb->ventasYNotasBase());

        $agg = (clone $query)->selectRaw("
            COALESCE(SUM(CASE WHEN tipo_comprobante LIKE 'NOTA_CREDITO%' THEN -total_neto ELSE total_neto END), 0) as total,
            COUNT(*) as cant,
            COALESCE(AVG(CASE WHEN tipo_comprobante LIKE 'NOTA_CREDITO%' THEN -total_neto ELSE total_neto END), 0) as prom
        ")->first();

        $this->stats = [
            'total_ventas' => number_format((float) $agg->total, 2),
            'cantidad' => (int) $agg->cant,
            'promedio' => $agg->cant > 0 ? number_format((float) $agg->prom, 2) : '0.00',
        ];
        $this->loaded = true;
    }

    public function exportarExcel(string $alcance = 'sunat')
    {
        $ventas = $this->ventasParaExportar($alcance)->get();
        $filename = $this->nombreArchivo('ventas', $alcance, 'xls');

        return response()->streamDownload(function () use ($ventas, $alcance) {
            echo view('reportes.exportes.ventas-excel', [
                'ventas' => $ventas,
                'alcance' => $this->etiquetaAlcance($alcance),
                'filtros' => $this->filtrosExportacion(),
                'resumen' => $this->resumenExportacion($ventas),
                'tipoReporte' => $this->tipoReporte,
            ])->render();
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function exportarPdf(string $alcance = 'sunat')
    {
        $ventas = $this->ventasParaExportar($alcance)->get();
        $filename = $this->nombreArchivo('ventas', $alcance, 'pdf');

        $pdf = Pdf::loadView('reportes.exportes.ventas-pdf', [
            'ventas' => $ventas,
            'alcance' => $this->etiquetaAlcance($alcance),
            'filtros' => $this->filtrosExportacion(),
            'resumen' => $this->resumenExportacion($ventas),
            'tipoReporte' => $this->tipoReporte,
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(fn () => print ($pdf->output()), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
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
                $q->whereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$this->search}%"))
                    ->orWhere('serie', 'like', "%{$this->search}%")
                    ->orWhere('numero', 'like', "%{$this->search}%");
            });
        }
        if ($this->tipoComprobanteFiltro === 'VENTA') {
            $query->whereNotIn('tipo_comprobante', [
                'NOTA_CREDITO', 'NOTA_CREDITO_BOLETA',
                'NOTA_CREDITO_FACTURA', 'NOTA_DEBITO',
            ]);
        } elseif ($this->tipoComprobanteFiltro === 'NOTA_CREDITO') {
            $query->whereIn('tipo_comprobante', [
                'NOTA_CREDITO', 'NOTA_CREDITO_BOLETA',
                'NOTA_CREDITO_FACTURA',
            ]);
        }

        return $query;
    }

    private function ventasParaExportar(string $alcance)
    {
        $qb = app(ReporteQueryBuilder::class);

        $relations = ['cliente', 'user', 'sucursal', 'documentoReferencia'];
        if ($this->tipoReporte === 'detalle') {
            $relations[] = 'detalles';
        }

        $query = $this->applyFilters($qb->ventasYNotasBase())
            ->with($relations)
            ->orderBy('fecha_emision')
            ->orderBy('serie')
            ->orderBy('numero');

        if ($alcance !== 'todo') {
            $query->whereIn('tipo_comprobante', [
                'BOLETA', 'FACTURA', 'NOTA_CREDITO',
                'NOTA_CREDITO_BOLETA', 'NOTA_CREDITO_FACTURA'
            ]);
        }

        return $query;
    }

    private function filtrosExportacion(): array
    {
        return [
            'desde' => $this->fechaDesde ?: 'Inicio',
            'hasta' => $this->fechaHasta ?: 'Hoy',
            'medio_pago' => $this->medioPago ?: 'Todos',
            'busqueda' => $this->search ?: 'Sin busqueda',
            'tipo_reporte' => $this->tipoReporte === 'detalle' ? 'Detallado' : 'Resumido',
            'filtro_comprobante' => $this->tipoComprobanteFiltro ?: 'Todos',
        ];
    }

    private function resumenExportacion($ventas): array
    {
        $total = $ventas->sum(fn (Documento $venta) => 
            str_starts_with($venta->tipo_comprobante, 'NOTA_CREDITO')
                ? -(float)$venta->total_neto 
                : (float)$venta->total_neto
        );
        $cantidad = $ventas->count();

        return [
            'total' => number_format($total, 2),
            'cantidad' => $cantidad,
            'promedio' => number_format($cantidad > 0 ? $total / $cantidad : 0, 2),
        ];
    }

    private function etiquetaAlcance(string $alcance): string
    {
        return $alcance === 'todo'
            ? 'Todos los comprobantes, incluyendo tickets'
            : 'Solo boletas y facturas';
    }

    private function nombreArchivo(string $base, string $alcance, string $extension): string
    {
        $desde = $this->fechaDesde ?: 'inicio';
        $hasta = $this->fechaHasta ?: now()->format('Y-m-d');
        $sufijo = $alcance === 'todo' ? 'con-tickets' : 'boletas-facturas';
        $tipo = $this->tipoReporte;

        return "{$base}-{$tipo}-{$sufijo}-{$desde}-{$hasta}.{$extension}";
    }

    public function render()
    {
        $qb = app(ReporteQueryBuilder::class);
        $query = $this->applyFilters($qb->ventasYNotasBase())
            ->with(['cliente', 'user', 'sucursal', 'documentoReferencia'])
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
