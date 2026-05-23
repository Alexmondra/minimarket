<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Series\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Series\SerieResource;
use App\Models\Sucursal;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class SeleccionarSucursal extends Page
{
    protected static string $resource = SerieResource::class;

    protected string $view = 'filament.clusters.configuraciones.resources.series.pages.seleccionar-sucursal';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verTabla')
                ->label('Ver en tabla')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(SerieResource::getUrl('index')),
        ];
    }

    public function getSucursales()
    {
        return Sucursal::where('empresa_id', Auth::user()->empresa_id)
            ->where('activo', true)
            ->orderBy('nombre_sucursal')
            ->get();
    }
}
