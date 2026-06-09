<?php

namespace App\Filament\Pages;

use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class IngresoProductos extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $navigationLabel = 'Ingreso de productos';

    protected static ?string $title = 'Ingreso de productos';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $slug = 'ingreso-productos';

    public string $view = 'filament.pages.ingreso-productos';

    public ?int $sucursalId = null;

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403, 'No tienes permiso para ingresar productos.');

        $this->sucursalId = app(SucursalContext::class)->resolveSucursalForWrite();

        if (! $this->sucursalId) {
            Notification::make()
                ->title('Selecciona una sucursal para ingresar productos')
                ->warning()
                ->send();

            $this->redirect(SeleccionarSucursal::getUrl());
        }
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user?->can('productos.crear')
            || $user?->can('compras.crear')
            || $user?->can('ventas.crear')
            || false;
    }
}
