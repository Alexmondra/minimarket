<?php

namespace App\Filament\Clusters\Reportes\Resources\Reportes\Pages;

use App\Filament\Clusters\Reportes\Resources\Reportes;
use Filament\Resources\Pages\Page;

class ReporteDashboard extends Page
{
    protected static string $resource = Reportes::class;

    protected static ?string $title = 'Dashboard de Reportes';

    protected string $view = 'livewire.reportes.reporte-dashboard';
}
