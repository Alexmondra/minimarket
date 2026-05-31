<?php

namespace App\Support\Reportes;

use App\Models\Documento;
use App\Models\ProductoSucursal;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\SessioneCaja;
use App\Models\Cliente;
use App\Models\DetalleDocumento;
use App\Support\SucursalContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ReporteQueryBuilder
{
    public function __construct(
        private SucursalContext $context,
    ) {}

    /**
     * Base query: ONLY real sales (BOLETA, FACTURA, TICKET) with estado=1.
     * Excludes: cancelled docs, credit notes, debit notes.
     */
    public function ventasBase(): Builder
    {
        return $this->context->applyToQuery(
            Documento::query()
                ->where('estado', true)
                ->whereNotIn('tipo_comprobante', [
                    'NOTA_CREDITO', 'NOTA_CREDITO_BOLETA',
                    'NOTA_CREDITO_FACTURA', 'NOTA_DEBITO',
                ])
        );
    }

    /**
     * Base query for cancelled/voided documents.
     */
    public function ventasAnuladasBase(): Builder
    {
        return $this->context->applyToQuery(
            Documento::query()->where('estado', false)
        );
    }

    public function ventasEnRango(Carbon $desde, Carbon $hasta): Builder
    {
        return $this->ventasBase()
            ->whereBetween('fecha_emision', [$desde->startOfDay(), $hasta->endOfDay()]);
    }

    public function ventasHoy(): Builder
    {
        return $this->ventasBase()->whereDate('fecha_emision', today());
    }

    public function ventasAyer(): Builder
    {
        return $this->ventasBase()->whereDate('fecha_emision', today()->subDay());
    }

    public function ventasMesActual(): Builder
    {
        return $this->ventasBase()
            ->whereMonth('fecha_emision', now()->month)
            ->whereYear('fecha_emision', now()->year);
    }

    public function ventasMesAnterior(): Builder
    {
        return $this->ventasBase()
            ->whereMonth('fecha_emision', now()->subMonth()->month)
            ->whereYear('fecha_emision', now()->subMonth()->year);
    }

    public function stockBase(): Builder
    {
        return $this->context->applyToQuery(
            ProductoSucursal::query()->where('activo', true)
        );
    }

    public function lotesBase(): Builder
    {
        return $this->context->applyToQuery(
            Lote::query()
        );
    }

    public function cajasBase(): Builder
    {
        return $this->context->applyToQuery(
            SessioneCaja::query()
        );
    }

    public function clientesBase(): Builder
    {
        return Cliente::query();
    }

    public function detalleBase(): Builder
    {
        return DetalleDocumento::query()
            ->whereHas('documento', function (Builder $q) {
                $q->where('estado', true);
                $this->context->applyToQuery($q);
            });
    }

    public function productosBajoStock(): Builder
    {
        return $this->stockBase()
            ->whereHas('lotePresentacion', function (Builder $q) {
                $q->whereRaw('lote_presentacion.stock <= producto_sucursal.stock_minimo')
                    ->where('lote_presentacion.stock', '>', 0);
            })
            ->with(['producto', 'sucursal', 'lotePresentacion']);
    }

    public function productosPorVencer(int $dias = 30): Builder
    {
        return $this->lotesBase()
            ->where('fecha_vencimiento', '>=', today())
            ->where('fecha_vencimiento', '<=', today()->addDays($dias))
            ->whereHas('lotePresentaciones', function (Builder $q) {
                $q->where('stock', '>', 0);
            })
            ->with(['lotePresentaciones', 'sucursal']);
    }

    public function productosVencidos(): Builder
    {
        return $this->lotesBase()
            ->where('fecha_vencimiento', '<', today())
            ->whereHas('lotePresentaciones', function (Builder $q) {
                $q->where('stock', '>', 0);
            })
            ->with(['lotePresentaciones', 'sucursal']);
    }

    public function cajasAbiertas(): Builder
    {
        return $this->cajasBase()
            ->where('estado', true)
            ->whereNull('fecha_cierre')
            ->with(['user', 'sucursal']);
    }
}
