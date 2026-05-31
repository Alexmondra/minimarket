<?php

namespace App\Filament\Clusters\Reportes\Resources\Reportes\Pages;

use App\Filament\Clusters\Reportes\Resources\Reportes;
use Filament\Resources\Pages\Page;

class ReporteVentas extends Page
{
    protected static string $resource = Reportes::class;

    protected static ?string $title = 'Reporte de Ventas';

    protected string $view = 'livewire.reportes.reporte-ventas';
}
