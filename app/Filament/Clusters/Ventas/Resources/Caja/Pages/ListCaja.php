<?php

namespace App\Filament\Clusters\Ventas\Resources\Caja\Pages;

use App\Filament\Clusters\Ventas\Resources\Caja\CajaResource;
use App\Models\SessioneCaja;
use App\Support\SucursalContext;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListCaja extends ListRecords
{
    protected static string $resource = CajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('abrirCaja')
                ->label('Abrir caja')
                ->icon('heroicon-o-lock-open')
                ->color('success')
                ->visible(Auth::user()?->can('cajas.abrir') ?? false)
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
                            ->warning()
                            ->send();

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
                }),
        ];
    }
}
