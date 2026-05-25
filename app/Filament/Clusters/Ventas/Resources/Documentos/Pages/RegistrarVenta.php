<?php

namespace App\Filament\Clusters\Ventas\Resources\Documentos\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use App\Filament\Pages\PuntoVenta;
use App\Livewire\Ventas\RegistrarVentaBehavior;
use App\Support\SucursalContext;
use App\Support\Ventas\CajaService;
use Filament\Notifications\Notification;
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

        $sucursalId = app(SucursalContext::class)->resolveSucursalForWrite();

        if (! $sucursalId || ! app(CajaService::class)->cajaAbierta(Auth::id(), $sucursalId)) {
            Notification::make()
                ->title('Debes abrir caja para vender')
                ->warning()
                ->send();

            $this->redirect(PuntoVenta::getUrl());

            return;
        }
    }

    public function getTitle(): string
    {
        return 'Registrar venta';
    }
}
