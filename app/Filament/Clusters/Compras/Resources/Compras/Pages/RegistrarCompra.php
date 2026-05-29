<?php

namespace App\Filament\Clusters\Compras\Resources\Compras\Pages;

use App\Filament\Clusters\Compras\Resources\Compras\CompraResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RegistrarCompra extends Page
{
    protected static string $resource = CompraResource::class;

    public string $view = 'filament.pages.registrar-compra';

    public function getTitle(): string
    {
        return 'Registrar Compra';
    }

    public static function getNavigationLabel(): string
    {
        return 'Registrar Compra';
    }

    public function mount(): void
    {
        if (request()->has('compra_id')) {
            abort_unless(Auth::user()->can('compras.editar'), 403, 'No tienes permiso para editar compras.');
        } else {
            abort_unless(Auth::user()->can('compras.crear'), 403, 'No tienes permiso para crear compras.');
        }
    }
}
