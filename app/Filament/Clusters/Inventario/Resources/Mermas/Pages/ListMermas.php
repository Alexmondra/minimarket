<?php

namespace App\Filament\Clusters\Inventario\Resources\Mermas\Pages;

use App\Filament\Clusters\Inventario\Resources\Mermas\MermaResource;
use App\Models\LotePresentacionMerma;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMermas extends ListRecords
{
    protected static string $resource = MermaResource::class;

    protected string $view = 'filament.clusters.inventario.resources.mermas.pages.list-mermas';

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getStatsProperty(): array
    {
        $baseQuery = MermaResource::getEloquentQuery();

        $totalRegistros = (clone $baseQuery)->count();
        $totalUnidades = (clone $baseQuery)->sum('cantidad');
        $impactoEstimado = $this->sumImpacto((clone $baseQuery));
        $ultimosSieteDias = (clone $baseQuery)
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        $tipos = (clone $baseQuery)
            ->selectRaw('tipo_merma, COUNT(*) as total, COALESCE(SUM(cantidad), 0) as unidades')
            ->groupBy('tipo_merma')
            ->get()
            ->keyBy('tipo_merma');

        return [
            'totalRegistros' => $totalRegistros,
            'totalUnidades' => $totalUnidades,
            'impactoEstimado' => $impactoEstimado,
            'ultimosSieteDias' => $ultimosSieteDias,
            'vencidos' => (int) ($tipos->get('vencido')->total ?? 0),
            'rotos' => (int) ($tipos->get('roto')->total ?? 0),
            'robos' => (int) ($tipos->get('robo')->total ?? 0),
            'otros' => (int) ($tipos->get('otro')->total ?? 0),
        ];
    }

    public function getHighlightsProperty(): array
    {
        $stats = $this->stats;

        $principalTipo = collect([
            'Vencidos' => $stats['vencidos'],
            'Dañados' => $stats['rotos'],
            'Pérdidas' => $stats['robos'],
            'Otros' => $stats['otros'],
        ])->sortDesc()->keys()->first();

        return [
            'principal_tipo' => $principalTipo ?: 'Sin registros',
            'promedio_impacto' => $stats['totalRegistros'] > 0
                ? $stats['impactoEstimado'] / $stats['totalRegistros']
                : 0,
            'promedio_unidades' => $stats['totalRegistros'] > 0
                ? $stats['totalUnidades'] / $stats['totalRegistros']
                : 0,
        ];
    }

    protected function sumImpacto(Builder $query): float
    {
        return (float) $query
            ->join('lote_presentacion', 'lote_presentacion_mermas.lote_presentacion_id', '=', 'lote_presentacion.id')
            ->selectRaw('COALESCE(SUM(lote_presentacion_mermas.cantidad * COALESCE(lote_presentacion.precio_compra, 0)), 0) as total')
            ->value('total');
    }
}
