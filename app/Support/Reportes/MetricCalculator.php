<?php

namespace App\Support\Reportes;

use App\Models\Documento;
use App\Models\DetalleDocumento;
use App\Models\ProductoSucursal;
use App\Models\Cliente;
use App\Support\SucursalContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricCalculator
{
    public function __construct(
        private SucursalContext $context,
    ) {}

    /**
     * Build a KPI data array.
     */
    private function kpi(
        mixed $value,
        string $label,
        string $icon,
        string $color,
        ?float $trend = null,
        ?string $prefix = '',
        ?string $suffix = '',
    ): array {
        return [
            'value' => $value,
            'label' => $label,
            'icon' => $icon,
            'color' => $color,
            'trend' => $trend,
            'trend_up' => $trend !== null ? $trend >= 0 : null,
            'prefix' => $prefix,
            'suffix' => $suffix,
        ];
    }

    /**
     * Ingresos = sum(ventas activas where tipo IN BOLETA, FACTURA, TICKET).
     *
     * NOTA_CREDITO documents are NOT subtracted because they are the
     * comprobante of a cancellation. The original sale already has estado=0
     * (excluded). Subtracting the credit note would be double-deducting.
     *
     * If a partial return happens (sale stays active + credit note issued),
     * the credit note amount should be subtracted. For now, treat credit
     * notes as linked to fully cancelled sales.
     */
    private function ingresosNetos(\Illuminate\Database\Eloquent\Builder $ventasQuery, ?\Illuminate\Database\Eloquent\Builder $ncQuery = null): float
    {
        $ingresosSales = (float) (clone $ventasQuery)
            ->whereNotIn('tipo_comprobante', [
                'NOTA_CREDITO', 'NOTA_CREDITO_BOLETA',
                'NOTA_CREDITO_FACTURA', 'NOTA_DEBITO',
            ])
            ->sum('total_neto');

        $ingresosNC = 0.0;
        if ($ncQuery) {
            $ingresosNC = (float) (clone $ncQuery)->sum('total_neto');
        }

        return $ingresosSales - $ingresosNC;
    }

    public function ventasDelDia(): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $hoy = $this->ingresosNetos($qb->ventasHoy(), $qb->notasCreditoHoy());
        $ayer = $this->ingresosNetos($qb->ventasAyer(), $qb->notasCreditoAyer());
        $trend = $ayer > 0 ? round((($hoy - $ayer) / $ayer) * 100, 1) : null;

        return $this->kpi(
            value: number_format($hoy, 2),
            label: 'Ventas Hoy',
            icon: 'heroicon-o-currency-dollar',
            color: 'emerald',
            trend: $trend,
            prefix: 'S/ ',
        );
    }

    public function gananciaNeta(): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $hoy = $this->calcularGanancia($qb->ventasHoy(), $qb->notasCreditoHoy());
        $ayer = $this->calcularGanancia($qb->ventasAyer(), $qb->notasCreditoAyer());
        $trend = $ayer > 0 ? round((($hoy - $ayer) / $ayer) * 100, 1) : null;

        return $this->kpi(
            value: number_format($hoy, 2),
            label: 'Ganancia Est. del Día',
            icon: 'heroicon-o-arrow-trending-up',
            color: 'teal',
            trend: $trend,
            prefix: 'S/ ',
        );
    }

    public function ticketsVendidos(): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $hoy = $qb->ventasHoy()->count();
        $ayer = $qb->ventasAyer()->count();
        $trend = $ayer > 0 ? round((($hoy - $ayer) / $ayer) * 100, 1) : null;

        return $this->kpi(
            value: $hoy,
            label: 'Tickets Vendidos',
            icon: 'heroicon-o-ticket',
            color: 'blue',
            trend: $trend,
        );
    }

    public function productosVendidos(): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $hoyIds = $qb->ventasHoy()->pluck('id');
        $ayerIds = $qb->ventasAyer()->pluck('id');

        $hoy = DetalleDocumento::whereIn('documento_id', $hoyIds)->count();
        $ayer = DetalleDocumento::whereIn('documento_id', $ayerIds)->count();
        $trend = $ayer > 0 ? round((($hoy - $ayer) / $ayer) * 100, 1) : null;

        return $this->kpi(
            value: $hoy,
            label: 'Productos Vendidos',
            icon: 'heroicon-o-shopping-bag',
            color: 'indigo',
            trend: $trend,
        );
    }

    public function productosBajoStock(): array
    {
        $count = app(ReporteQueryBuilder::class)->productosBajoStock()->count();

        return $this->kpi(
            value: $count,
            label: 'Bajo Stock',
            icon: 'heroicon-o-exclamation-triangle',
            color: 'amber',
        );
    }

    public function productosPorVencer(): array
    {
        $count = app(ReporteQueryBuilder::class)->productosPorVencer(30)->count();

        return $this->kpi(
            value: $count,
            label: 'Por Vencer (30d)',
            icon: 'heroicon-o-clock',
            color: 'orange',
        );
    }

    public function totalClientes(): array
    {
        $total = Cliente::query()->count();

        return $this->kpi(
            value: $total,
            label: 'Total Clientes',
            icon: 'heroicon-o-users',
            color: 'violet',
        );
    }

    public function totalIngresos(): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $mes = $this->ingresosNetos($qb->ventasMesActual(), $qb->notasCreditoMesActual());
        $mesAnterior = $this->ingresosNetos($qb->ventasMesAnterior(), $qb->notasCreditoMesAnterior());
        $trend = $mesAnterior > 0 ? round((($mes - $mesAnterior) / $mesAnterior) * 100, 1) : null;

        return $this->kpi(
            value: number_format($mes, 2),
            label: 'Ingresos del Mes',
            icon: 'heroicon-o-banknotes',
            color: 'emerald',
            trend: $trend,
            prefix: 'S/ ',
        );
    }

    public function gananciaMensual(): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $mes = $this->calcularGanancia($qb->ventasMesActual(), $qb->notasCreditoMesActual());
        $mesAnterior = $this->calcularGanancia($qb->ventasMesAnterior(), $qb->notasCreditoMesAnterior());
        $trend = $mesAnterior > 0 ? round((($mes - $mesAnterior) / $mesAnterior) * 100, 1) : null;

        return $this->kpi(
            value: number_format($mes, 2),
            label: 'Ganancia Est. del Mes',
            icon: 'heroicon-o-arrow-trending-up',
            color: 'teal',
            trend: $trend,
            prefix: 'S/ ',
        );
    }

    public function cajasAbiertasCount(): array
    {
        $count = app(ReporteQueryBuilder::class)->cajasAbiertas()->count();

        return $this->kpi(
            value: $count,
            label: 'Cajas Abiertas',
            icon: 'heroicon-o-lock-open',
            color: $count > 0 ? 'emerald' : 'slate',
        );
    }

    /**
     * Get all KPIs at once.
     */
    public function allKpis(): array
    {
        $sucursalId = $this->context->activeSucursalId();
        $suffix = $sucursalId ? "_{$sucursalId}" : '_global';
        $cacheKey = 'kpis_' . now()->format('Ymd_H') . $suffix;

        return cache()->remember($cacheKey, 300, function () {
            return [
                'ventas_dia' => $this->ventasDelDia(),
                'ganancia_neta' => $this->gananciaNeta(),
                'tickets_vendidos' => $this->ticketsVendidos(),
            ];
        });
    }

    /**
     * Calculate total COST (not profit) for documents in the given query.
     * Cost = sum(precio_compra_unitario * cantidad) for each detail line.
     *
     * IMPORTANT: The ONLY reliable per-unit purchase price is in
     * lote_presentacion.precio_compra (0.67/unit).
     * lote.precio_compra and detalle_compras.precio_compra are TOTAL batch cost,
     * NOT per-unit — using them would give wildly wrong results.
     *
     * Fallback chain (most → least accurate):
     *  1. LotePresentacion.precio_compra (per-unit, matched by lote_id + presentacion_id)
     *  2. DetalleCompra.precio_compra / Lote.precio_compra (total batch — divide by stock?)
     */
    private function calcularCosto(\Illuminate\Database\Eloquent\Builder $documentoQuery): float
    {
        $total = 0.0;

        DetalleDocumento::whereIn('documento_id', (clone $documentoQuery)->select('id'))
            ->select('id', 'documento_id', 'producto_presentacion_id', 'cantidad', 'lote_id')
            ->with(['lote.lotePresentaciones:lote_id,producto_presentacion_id,precio_compra', 'lote.detalleCompra:lote_id,precio_compra'])
            ->chunk(500, function ($detalles) use (&$total) {
                foreach ($detalles as $detalle) {
                    $cantidad = (float) ($detalle->cantidad ?? 1);

                    $lotePresentaciones = $detalle->lote?->lotePresentaciones;
                    $lotePresKeyed = $lotePresentaciones ? $lotePresentaciones->keyBy('producto_presentacion_id') : collect();

                    $lpPrecio = $lotePresKeyed->get($detalle->producto_presentacion_id)?->precio_compra;

                    if ($lpPrecio !== null && (float) $lpPrecio > 0) {
                        $total += (float) $lpPrecio * $cantidad;
                        continue;
                    }

                    $precioCompra = (float) ($detalle->lote?->detalleCompra?->precio_compra
                        ?? $detalle->lote?->precio_compra
                        ?? 0);

                    if ($precioCompra > 100 && $detalle->lote) {
                        $stockTotal = $detalle->lote->stock_total;
                        if ($stockTotal > 0) {
                            $precioCompra = $precioCompra / $stockTotal;
                        }
                    }

                    $total += $precioCompra * $cantidad;
                }
            });

        return $total;
    }

    /**
     * Calculate NET PROFIT for documents in the given query.
     * Profit = ingresos_netos (ventas - notas_credito) - costo
     */
    private function calcularGanancia(\Illuminate\Database\Eloquent\Builder $salesQuery, ?\Illuminate\Database\Eloquent\Builder $ncQuery = null): float
    {
        $ingresos = $this->ingresosNetos($salesQuery, $ncQuery);
        
        $costoSales = $this->calcularCosto($salesQuery);
        $costoNC = $ncQuery ? $this->calcularCosto($ncQuery) : 0.0;
        $costos = $costoSales - $costoNC;

        return $ingresos - $costos;
    }

    /**
     * Compute real profit in one query using direct DB Query Builder joins.
     */
    public function calcularGananciaRealQuery(array $documentoIds): float
    {
        if (empty($documentoIds)) {
            return 0.0;
        }

        return (float) DB::table('documentos_detalles')
            ->join('lotes', function ($join) {
                $join->on('documentos_detalles.lote_id', '=', 'lotes.id')
                    ->whereNull('lotes.deleted_at');
            })
            ->leftJoin('lote_presentacion', function ($join) {
                $join->on('lote_presentacion.lote_id', '=', 'lotes.id')
                    ->on('lote_presentacion.producto_presentacion_id', '=', 'documentos_detalles.producto_presentacion_id');
            })
            ->leftJoin('detalle_compras', function ($join) {
                $join->on('detalle_compras.lote_id', '=', 'lotes.id')
                    ->whereNull('detalle_compras.deleted_at');
            })
            ->leftJoin('compras', function ($join) {
                $join->on('compras.id', '=', 'detalle_compras.compra_id')
                    ->whereNull('compras.deleted_at');
            })
            ->leftJoin(
                DB::raw('(SELECT lote_id, SUM(stock) as stock_total FROM lote_presentacion GROUP BY lote_id) as stock_totals'),
                'stock_totals.lote_id',
                '=',
                'lotes.id'
            )
            ->whereIn('documentos_detalles.documento_id', $documentoIds)
            ->whereNull('documentos_detalles.deleted_at')
            ->selectRaw("
                SUM(
                    CASE 
                        WHEN (compras.id IS NULL OR compras.estado = 'completada')
                        THEN COALESCE(documentos_detalles.cantidad, 0) * (
                            COALESCE(documentos_detalles.precio_unitario, 0) - COALESCE(
                                CASE
                                    WHEN lote_presentacion.precio_compra IS NOT NULL AND lote_presentacion.precio_compra > 0
                                    THEN lote_presentacion.precio_compra
                                    ELSE (
                                        CASE 
                                            WHEN COALESCE(detalle_compras.precio_compra, lotes.precio_compra, 0) > 100 AND COALESCE(stock_totals.stock_total, 0) > 0 
                                            THEN COALESCE(detalle_compras.precio_compra, lotes.precio_compra, 0) / stock_totals.stock_total
                                            ELSE COALESCE(detalle_compras.precio_compra, lotes.precio_compra, 0)
                                        END
                                    )
                                END,
                                0
                            )
                        )
                        ELSE 0
                    END
                ) as total_ganancia
            ")
            ->value('total_ganancia') ?? 0.0;
    }

    /**
     * Get daily sales for the last N days (for charts).
     */
    public function ventasUltimosDias(int $dias = 7): array
    {
        $labels = [];
        $data = [];
        $qb = app(ReporteQueryBuilder::class);

        for ($i = $dias - 1; $i >= 0; $i--) {
            $fecha = today()->subDays($i);
            $labels[] = $fecha->format('d/m');
            
            $ventas = (float) (clone $qb->ventasBase())
                ->whereDate('fecha_emision', $fecha)
                ->sum('total_neto');
                
            $nc = (float) (clone $qb->notasCreditoBase())
                ->whereDate('fecha_emision', $fecha)
                ->sum('total_neto');
                
            $data[] = $ventas - $nc;
        }

        return compact('labels', 'data');
    }

    /**
     * Get monthly sales for the last N months (for charts).
     */
    public function ventasUltimosMeses(int $meses = 12): array
    {
        $labels = [];
        $data = [];
        $qb = app(ReporteQueryBuilder::class);

        for ($i = $meses - 1; $i >= 0; $i--) {
            $fecha = today()->subMonths($i);
            $labels[] = $fecha->format('M');
            
            $ventas = (float) (clone $qb->ventasBase())
                ->whereMonth('fecha_emision', $fecha->month)
                ->whereYear('fecha_emision', $fecha->year)
                ->sum('total_neto');
                
            $nc = (float) (clone $qb->notasCreditoBase())
                ->whereMonth('fecha_emision', $fecha->month)
                ->whereYear('fecha_emision', $fecha->year)
                ->sum('total_neto');
                
            $data[] = $ventas - $nc;
        }

        return compact('labels', 'data');
    }

    /**
     * Get payment method distribution.
     */
    public function metodosPagoDistribution(): array
    {
        $qb = app(ReporteQueryBuilder::class);

        return $qb->ventasMesActual()
            ->select('medio_pago', DB::raw('SUM(total_neto) as total'), DB::raw('COUNT(*) as cantidad'))
            ->whereNotNull('medio_pago')
            ->groupBy('medio_pago')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    /**
     * Get top selling products.
     */
    public function topProductos(int $limit = 10): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $docIds = $qb->ventasMesActual()->pluck('id');

        if ($docIds->isEmpty()) {
            return [];
        }

        return DetalleDocumento::whereIn('documento_id', $docIds)
            ->select(
                'producto_nombre',
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(subtotal_neto) as total_ingresos')
            )
            ->groupBy('producto_nombre')
            ->orderByDesc('total_ventas')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get monthly profit for the last N months.
     * Returns ingresos, costos, and ganancias (ingresos - costos).
     */
    public function gananciasUltimosMeses(int $meses = 12): array
    {
        $labels = [];
        $ingresos = [];
        $costos = [];
        $qb = app(ReporteQueryBuilder::class);

        for ($i = $meses - 1; $i >= 0; $i--) {
            $fecha = today()->subMonths($i);
            $labels[] = $fecha->format('M');

            $query = (clone $qb->ventasBase())
                ->whereMonth('fecha_emision', $fecha->month)
                ->whereYear('fecha_emision', $fecha->year);

            $queryNC = (clone $qb->notasCreditoBase())
                ->whereMonth('fecha_emision', $fecha->month)
                ->whereYear('fecha_emision', $fecha->year);

            $ingSales = (float) (clone $query)->sum('total_neto');
            $ingNC = (float) (clone $queryNC)->sum('total_neto');
            $ingresos[] = $ingSales - $ingNC;

            $costSales = $this->calcularCosto($query);
            $costNC = $this->calcularCosto($queryNC);
            $costos[] = $costSales - $costNC;
        }

        // Ganancia = ingresos - costos
        $ganancias = array_map(fn($i, $c) => $i - $c, $ingresos, $costos);

        return compact('labels', 'ingresos', 'costos', 'ganancias');
    }

    /**
     * Get cumulative weekly sales (Monday to Sunday) for current week.
     */
    public function ventasAcumuladasSemana(): array
    {
        $labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $data = [];
        $qb = app(ReporteQueryBuilder::class);
        
        $startOfWeek = now()->startOfWeek();
        $today = today();
        
        $startOfWeekStr = $startOfWeek->toDateString();
        $todayStr = $today->toDateString();

        $ventasPorFecha = (clone $qb->ventasBase())
            ->whereBetween('fecha_emision', [$startOfWeekStr, $todayStr])
            ->select('fecha_emision', DB::raw('SUM(total_neto) as total'))
            ->groupBy('fecha_emision')
            ->pluck('total', 'fecha_emision')
            ->toArray();

        $ncPorFecha = (clone $qb->notasCreditoBase())
            ->whereBetween('fecha_emision', [$startOfWeekStr, $todayStr])
            ->select('fecha_emision', DB::raw('SUM(total_neto) as total'))
            ->groupBy('fecha_emision')
            ->pluck('total', 'fecha_emision')
            ->toArray();

        $cumulative = 0.0;

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            
            if ($date->greaterThan($today)) {
                $data[] = null;
            } else {
                $dateStr = $date->toDateString();
                $ventas = (float) ($ventasPorFecha[$dateStr] ?? 0.0);
                $nc = (float) ($ncPorFecha[$dateStr] ?? 0.0);
                
                $net = $ventas - $nc;
                $cumulative += $net;
                $data[] = $cumulative;
            }
        }

        // Last non-null value is the cumulative week total
        $total = 0.0;
        foreach (array_reverse($data) as $v) {
            if ($v !== null) {
                $total = $v;
                break;
            }
        }

        return compact('labels', 'data', 'total');
    }

    /**
     * Get daily sales for a specific month and year.
     */
    public function ventasDiariasMes(int $year, int $month): array
    {
        $qb = app(ReporteQueryBuilder::class);
        $date = Carbon::create($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        
        $isCurrentMonth = ($year === today()->year && $month === today()->month);
        $limitDay = $isCurrentMonth ? today()->day : $daysInMonth;

        $ventas = $qb->ventasBase()
            ->whereYear('fecha_emision', $year)
            ->whereMonth('fecha_emision', $month)
            ->select(DB::raw('DAY(fecha_emision) as dia'), DB::raw('SUM(total_neto) as total'))
            ->groupBy('dia')
            ->pluck('total', 'dia')
            ->toArray();

        $notasCredito = $qb->notasCreditoBase()
            ->whereYear('fecha_emision', $year)
            ->whereMonth('fecha_emision', $month)
            ->select(DB::raw('DAY(fecha_emision) as dia'), DB::raw('SUM(total_neto) as total'))
            ->groupBy('dia')
            ->pluck('total', 'dia')
            ->toArray();

        $labels = [];
        $data = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $labels[] = $d;
            
            if ($isCurrentMonth && $d > $limitDay) {
                $data[] = null;
            } else {
                $v = (float) ($ventas[$d] ?? 0.0);
                $nc = (float) ($notasCredito[$d] ?? 0.0);
                $data[] = max(0.0, $v - $nc);
            }
        }

        return compact('labels', 'data');
    }

    /**
     * Get monthly profit vs investment vs sales for the last N months.
     * Real profit is only calculated if the lot has been paid (compra.estado = 'completada' or no purchase detail).
     */
    public function gananciasIngresosVentas(int $meses = 3): array
    {
        $labels = [];
        $ingresos = [];
        $ventas = [];
        $ganancias = [];
        $qb = app(ReporteQueryBuilder::class);

        for ($i = $meses - 1; $i >= 0; $i--) {
            $fecha = today()->subMonths($i);
            $labels[] = ucfirst($fecha->translatedFormat('M'));
            
            $startOfMonth = $fecha->copy()->startOfMonth();
            $endOfMonth = $fecha->copy()->endOfMonth();

            // 1. Inversión (Ingreso de Inventario en el mes)
            $loteQuery = \App\Models\Lote::query();
            $this->context->applyToQuery($loteQuery, 'sucursal_id');
            $loteIds = $loteQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->pluck('id');

            $inversion = 0.0;
            if ($loteIds->isNotEmpty()) {
                $inversion = (float) \App\Models\LotePresentacion::whereIn('lote_id', $loteIds)
                    ->selectRaw('SUM(stock_inicial * precio_compra) as total')
                    ->value('total') ?? 0.0;
            }
            $ingresos[] = $inversion;

            // 2. Ventas (Ventas Reales en el mes)
            $ventasQuery = (clone $qb->ventasBase())
                ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth]);
            
            $ncQuery = (clone $qb->notasCreditoBase())
                ->whereBetween('fecha_emision', [$startOfMonth, $endOfMonth]);

            $ventas[] = max(0.0, $this->ingresosNetos($ventasQuery, $ncQuery));

            // 3. Ganancia Real
            $ventasIds = (clone $ventasQuery)->pluck('id')->toArray();
            $ncIds = (clone $ncQuery)->pluck('id')->toArray();

            $gananciaReal = $this->calcularGananciaRealQuery($ventasIds) - $this->calcularGananciaRealQuery($ncIds);

            $ganancias[] = max(0.0, $gananciaReal);
        }

        return compact('labels', 'ingresos', 'ventas', 'ganancias');
    }

    /**
     * Get top products for a specific period (dia, semana, mes).
     */
    public function topProductosPeriodo(string $periodo = 'mes', int $limit = 10): array
    {
        $qb = app(ReporteQueryBuilder::class);
        
        $query = match ($periodo) {
            'dia' => $qb->ventasHoy(),
            'semana' => $qb->ventasEnRango(now()->startOfWeek(), now()->endOfWeek()),
            'mes' => $qb->ventasMesActual(),
            default => $qb->ventasMesActual(),
        };

        $docIds = $query->pluck('id');

        if ($docIds->isEmpty()) {
            return [];
        }

        return DetalleDocumento::whereIn('documento_id', $docIds)
            ->select(
                'producto_nombre',
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(subtotal_neto) as total_ingresos')
            )
            ->groupBy('producto_nombre')
            ->orderByDesc('total_ventas')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}

