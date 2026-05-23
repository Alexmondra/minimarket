<?php

namespace App\Filament\Clusters\Almacen\Resources\StockSucursal\Pages;

use App\Filament\Clusters\Almacen\Resources\StockSucursal\StockSucursalResource;
use App\Filament\Clusters\Compras\Resources\Compras\CompraResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\View\View;

class ListStockSucursal extends ListRecords
{
    protected static string $resource = StockSucursalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrarCompra')
                ->label('Registrar Compra')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(CompraResource::getUrl('registrar'))
                ->openUrlInNewTab(false),

            Action::make('ajusteEntrada')
                ->label('Ajuste de Entrada')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->action('dispatchAbrirAjusteEntrada'),

            Action::make('ajusteSalida')
                ->label('Ajuste de Salida')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('danger')
                ->action('dispatchAbrirAjusteSalida'),
        ];
    }

    public function dispatchAbrirAjusteEntrada(): void
    {
        $this->dispatch('abrirAjusteEntrada')->to('almacen.ajuste-stock');
    }

    public function dispatchAbrirAjusteSalida(): void
    {
        $this->dispatch('abrirAjusteSalida')->to('almacen.ajuste-stock');
    }

    public function getFooter(): ?View
    {
        return view('filament.clusters.almacen.resources.stock-sucursal.pages.ajuste-stock-modal');
    }
}
