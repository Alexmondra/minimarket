<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use App\Livewire\Ventas\RegistrarVentaBehavior;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class_exists(\App\Livewire\Ventas\RegistrarVenta::class);

class RegistrarVenta extends Page
{
    use RegistrarVentaBehavior;

    protected static string $resource = DocumentoResource::class;

    public string $view = 'livewire.ventas.registrar-venta';

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('ventas.crear'), 403, 'No tienes permiso para registrar ventas.');

        $this->mountRegistrarVenta();
    }

    public function getTitle(): string
    {
        return 'Registrar venta';
    }
}
