<?php

namespace App\Livewire;

use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Livewire\Component;

class SucursalSelector extends Component
{
    public string $selectedSucursalId = '';

    public bool $visible = false;

    public array $sucursales = [];

    public function mount(): void
    {
        $context = app(SucursalContext::class);
        $user = auth()->user();

        $this->visible = $context->shouldShowTopbarSelector($user);
        $this->sucursales = $context->allowedSucursales($user)
            ->map(fn ($sucursal): array => [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre_sucursal,
            ])
            ->values()
            ->all();
        $this->selectedSucursalId = (string) ($context->activeSucursalId() ?? '');
    }

    public function updatedSelectedSucursalId(): void
    {
        $context = app(SucursalContext::class);
        $sucursalId = $this->selectedSucursalId === '' ? null : (int) $this->selectedSucursalId;

        $context->selectSucursal($sucursalId);

        Notification::make()
            ->title($sucursalId ? 'Sucursal activa actualizada' : 'Modo todas las sucursales')
            ->success()
            ->send();

        $this->redirect(request()->headers->get('referer') ?: filament()->getUrl());
    }

    public function render()
    {
        return view('livewire.sucursal-selector');
    }
}
