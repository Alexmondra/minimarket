<?php

namespace App\Filament\Clusters\Ventas\Resources\Caja;

use App\Filament\Clusters\Ventas\Resources\Caja\Pages\ListCaja;
use App\Models\SessioneCaja;
use App\Support\SucursalContext;
use App\Support\Ventas\CajaService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CajaResource extends Resource
{
    protected static ?string $model = SessioneCaja::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static string|UnitEnum|null $navigationGroup = 'Ventas';

    protected static ?string $navigationLabel = 'Caja';

    public static function getPages(): array
    {
        return [
            'index' => ListCaja::route('/'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),
                TextColumn::make('fecha_apertura')
                    ->label('Apertura')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('fecha_cierre')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('-'),
                TextColumn::make('saldo_inicial')
                    ->label('Inicial')
                    ->money('PEN'),
                TextColumn::make('saldo_teorico')
                    ->label('Teorico')
                    ->money('PEN')
                    ->state(fn (SessioneCaja $record) => $record->saldo_teorico ?? app(CajaService::class)->saldoTeorico($record)),
                TextColumn::make('saldo_real')
                    ->label('Real')
                    ->money('PEN')
                    ->placeholder('-'),
                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->money('PEN')
                    ->color(fn ($state) => (float) $state === 0.0 ? 'success' : 'danger')
                    ->placeholder('-'),
                IconColumn::make('estado')
                    ->label('Abierta')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('cerrarCaja')
                    ->label('Cerrar caja')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->visible(fn (SessioneCaja $record) => $record->estado && (Auth::user()?->can('cajas.cerrar') ?? false))
                    ->form([
                        TextInput::make('saldo_real')
                            ->label('Saldo real')
                            ->numeric()
                            ->required(),
                        Textarea::make('observaciones')
                            ->label('Observaciones')
                            ->rows(3),
                    ])
                    ->action(function (array $data, SessioneCaja $record): void {
                        $teorico = app(CajaService::class)->saldoTeorico($record);
                        $real = round((float) $data['saldo_real'], 2);

                        $record->update([
                            'fecha_cierre' => now(),
                            'saldo_teorico' => $teorico,
                            'saldo_real' => $real,
                            'diferencia' => round($real - $teorico, 2),
                            'estado' => false,
                            'observaciones' => $data['observaciones'] ?? null,
                        ]);
                    }),
            ])
            ->defaultSort('fecha_apertura', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return app(SucursalContext::class)->applyToQuery(parent::getEloquentQuery()->with(['sucursal', 'user']));
    }

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('cajas.ver') ?? false;
    }
}
