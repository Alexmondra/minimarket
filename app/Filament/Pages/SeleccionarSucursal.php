<?php

namespace App\Filament\Pages;

use App\Support\SucursalContext;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SeleccionarSucursal extends Page
{
    protected static ?string $slug = 'seleccionar-sucursal';

    protected static bool $shouldRegisterNavigation = false;

    public string $view = 'filament.pages.seleccionar-sucursal';

    public array $sucursales = [];

    public function mount(): void
    {
        $context = app(SucursalContext::class);
        $user = auth()->user();
        $context->normalizeSession($user);

        $this->sucursales = $context->allowedSucursales($user)
            ->map(fn ($sucursal): array => [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre_sucursal,
                'codigo' => $sucursal->codigo,
                'direccion' => $sucursal->direccion,
            ])
            ->values()
            ->all();

        if (! $context->requiresSelectionPage($user) && ! $context->isAdmin($user)) {
            $this->redirect(Filament::getUrl());
        }
    }

    public function getTitle(): string
    {
        return 'Seleccionar sucursal';
    }

    public function seleccionar(int $sucursalId): void
    {
        $context = app(SucursalContext::class);

        $context->selectSucursal($sucursalId);

        Notification::make()
            ->title('Sucursal seleccionada')
            ->success()
            ->send();

        $this->redirect(Filament::getUrl());
    }
}
