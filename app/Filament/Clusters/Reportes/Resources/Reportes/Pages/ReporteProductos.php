<?php

namespace App\Filament\Clusters\Reportes\Resources\Reportes\Pages;

use App\Filament\Clusters\Reportes\Resources\Reportes;
use Filament\Resources\Pages\Page;

class ReporteProductos extends Page
{
    protected static string $resource = Reportes::class;
    protected static ?string $title = 'Reporte de Productos';
    protected string $view = 'livewire.reportes.reporte-productos';
}
