<?php

namespace App\Livewire\Reportes;

use Livewire\Component;

class DashboardOverview extends Component
{
    public array $links = [];

    public function mount(): void
    {
        $this->links = [
            [
                'url' => \App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteVentas::getUrl(),
                'title' => 'Ventas',
                'desc' => 'Análisis detallado de ventas, filtros por fecha, sucursal, método de pago y exportación.',
                'icon' => 'heroicon-o-currency-dollar',
                'color' => 'emerald',
            ],
            [
                'url' => \App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteGanancias::getUrl(),
                'title' => 'Ganancias',
                'desc' => 'Ingresos vs costos, margen de ganancia, tendencias y productos más rentables.',
                'icon' => 'heroicon-o-arrow-trending-up',
                'color' => 'teal',
            ],
            [
                'url' => \App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReportePerdidas::getUrl(),
                'title' => 'Pérdidas',
                'desc' => 'Productos vencidos, mermas, devoluciones y anulaciones con impacto económico.',
                'icon' => 'heroicon-o-arrow-trending-down',
                'color' => 'rose',
            ],
            [
                'url' => \App\Filament\Clusters\Reportes\Resources\Reportes\Pages\ReporteProductos::getUrl(),
                'title' => 'Productos',
                'desc' => 'Bajo stock, por vencer, vencidos, más vendidos y sin movimiento.',
                'icon' => 'heroicon-o-shopping-bag',
                'color' => 'violet',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.reportes.dashboard-overview');
    }
}
