<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use App\Models\SessioneCaja;
use App\Support\SucursalContext;
use App\Support\Ventas\CajaService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PuntoVenta extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Punto de venta';

    protected static ?string $title = 'Punto de venta';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static ?string $slug = 'punto-venta';

    public string $view = 'filament.pages.punto-venta';

    public ?int $sucursalId = null;

    public ?float $saldoInicial = null;

    public ?string $observaciones = null;

    public ?SessioneCaja $cajaAbiertaEnOtraSucursal = null;

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('ventas.crear'), 403, 'No tienes permiso para registrar ventas.');

        $this->sucursalId = app(SucursalContext::class)->resolveSucursalForWrite();

        if (! $this->sucursalId) {
            Notification::make()
                ->title('Selecciona una sucursal para continuar')
                ->warning()
                ->send();

            $this->redirect(SeleccionarSucursal::getUrl());

            return;
        }

        // Buscar si el usuario ya tiene una caja abierta en cualquier sucursal
        $cajaGlobal = SessioneCaja::query()
            ->where('user_id', Auth::id())
            ->where('estado', true)
            ->whereNull('fecha_cierre')
            ->with('sucursal')
            ->first();

        if ($cajaGlobal) {
            if ($cajaGlobal->sucursal_id === $this->sucursalId) {
                // Si la tiene abierta en esta sucursal, redirigir al POS directamente
                $this->redirect(DocumentoResource::getUrl('registrar'));
                return;
            } else {
                // Si la tiene abierta en otra sucursal, guardar referencia para mostrar advertencia de bloqueo
                $this->cajaAbiertaEnOtraSucursal = $cajaGlobal;
            }
        }
    }

    public function abrirCajaManual(): void
    {
        abort_unless(Auth::user()?->can('cajas.abrir'), 403, 'No tienes permiso para abrir cajas.');

        $this->validate([
            'saldoInicial' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // Re-verificar si tiene caja abierta globalmente
        $tieneCajaAbierta = SessioneCaja::query()
            ->where('user_id', Auth::id())
            ->where('estado', true)
            ->whereNull('fecha_cierre')
            ->exists();

        if ($tieneCajaAbierta) {
            Notification::make()
                ->title('Ya tienes una sesión de caja abierta')
                ->danger()
                ->send();
            return;
        }

        SessioneCaja::create([
            'empresa_id' => Auth::user()->empresa_id,
            'sucursal_id' => $this->sucursalId,
            'user_id' => Auth::id(),
            'fecha_apertura' => now(),
            'saldo_inicial' => round((float) $this->saldoInicial, 2),
            'estado' => true,
            'observaciones' => $this->observaciones,
        ]);

        Notification::make()
            ->title('Caja abierta correctamente')
            ->success()
            ->send();

        $this->redirect(DocumentoResource::getUrl('registrar'));
    }

    public function tieneCajaAbierta(): bool
    {
        if (! $this->sucursalId) {
            return false;
        }

        return app(CajaService::class)->cajaAbierta(Auth::id(), $this->sucursalId) !== null;
    }

    public function getSucursalNombreProperty(): string
    {
        $sucursal = app(SucursalContext::class)
            ->allowedSucursales(Auth::user())
            ->firstWhere('id', $this->sucursalId);

        return $sucursal?->nombre_sucursal ?? 'Sin sucursal seleccionada';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('ventas.crear') ?? false;
    }
}
