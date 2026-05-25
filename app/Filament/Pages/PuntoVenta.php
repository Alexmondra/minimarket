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

        if ($this->tieneCajaAbierta()) {
            $this->redirect(DocumentoResource::getUrl('registrar'));
            return;
        }

        if (Auth::user()?->can('cajas.abrir')) {
            $this->mountAction('abrirCaja');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('abrirCaja')
                ->label('Abrir caja')
                ->icon('heroicon-o-lock-open')
                ->color('success')
                ->visible(fn (): bool => Auth::user()?->can('cajas.abrir') ?? false)
                ->form([
                    Select::make('sucursal_id')
                        ->label('Sucursal')
                        ->options(fn (): array => app(SucursalContext::class)
                            ->sucursalesForWrite()
                            ->pluck('nombre_sucursal', 'id')
                            ->all())
                        ->default(fn (): ?int => app(SucursalContext::class)->resolveSucursalForWrite())
                        ->disabled(fn (): bool => app(SucursalContext::class)->activeSucursalId() !== null)
                        ->dehydrated()
                        ->required(),
                    TextInput::make('saldo_inicial')
                        ->label('Saldo inicial')
                        ->numeric()
                        ->required(),
                    Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    $sucursalId = app(SucursalContext::class)->resolveSucursalForWrite((int) $data['sucursal_id']);
                    abort_unless($sucursalId, 403);

                    $abierta = SessioneCaja::query()
                        ->where('user_id', Auth::id())
                        ->where('sucursal_id', $sucursalId)
                        ->where('estado', true)
                        ->whereNull('fecha_cierre')
                        ->exists();

                    if ($abierta) {
                        Notification::make()
                            ->title('Ya tienes una caja abierta en esta sucursal')
                            ->success()
                            ->send();

                        $this->redirect(DocumentoResource::getUrl('registrar'));

                        return;
                    }

                    SessioneCaja::create([
                        'empresa_id' => Auth::user()->empresa_id,
                        'sucursal_id' => $sucursalId,
                        'user_id' => Auth::id(),
                        'fecha_apertura' => now(),
                        'saldo_inicial' => round((float) $data['saldo_inicial'], 2),
                        'estado' => true,
                        'observaciones' => $data['observaciones'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Caja abierta correctamente')
                        ->success()
                        ->send();

                    $this->redirect(DocumentoResource::getUrl('registrar'));
                }),
            Action::make('irAVentas')
                ->label('Ir a ventas')
                ->icon('heroicon-o-arrow-right')
                ->visible(fn (): bool => $this->tieneCajaAbierta())
                ->url(DocumentoResource::getUrl('registrar')),
        ];
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
