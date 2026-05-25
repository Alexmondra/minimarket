<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RegistrarVenta extends Page
{
    protected static string $resource = DocumentoResource::class;

    public string $view = 'filament.pages.registrar-venta';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('ventas.crear'), 403, 'No tienes permiso para registrar ventas.');
    }

    public function getTitle(): string
    {
        return 'Registrar venta';
    }
}
