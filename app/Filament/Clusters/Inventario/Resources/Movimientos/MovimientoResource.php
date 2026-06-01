<?php

namespace App\Filament\Clusters\Inventario\Resources\Movimientos;

use App\Filament\Clusters\Inventario\Resources\Movimientos\Pages\ListMovimientos;
use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class MovimientoResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 101;

    protected static ?string $navigationLabel = 'Movimientos';

    protected static ?string $pluralModelLabel = 'Movimientos de Inventario';

    protected static ?string $modelLabel = 'Movimiento';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle del Movimiento de Inventario')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tipo')
                                    ->label('Tipo de Movimiento')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'entrada' => 'success',
                                        'salida' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'entrada' => 'heroicon-m-arrow-trending-up',
                                        'salida' => 'heroicon-m-arrow-trending-down',
                                        default => 'heroicon-m-arrows-right-left',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'entrada' => 'Entrada',
                                        'salida' => 'Salida',
                                        default => ucfirst($state),
                                    }),
                                TextEntry::make('producto_nombre')
                                    ->label('Producto')
                                    ->weight('bold')
                                    ->placeholder('N/A'),
                                TextEntry::make('productoPresentacion.tipo_presentacion')
                                    ->label('Presentación')
                                    ->placeholder('N/A'),
                                TextEntry::make('cantidad')
                                    ->label('Cantidad')
                                    ->weight('bold')
                                    ->color(fn ($state, $record): string => $record->tipo === 'salida' ? 'danger' : 'success')
                                    ->formatStateUsing(fn ($state, $record) => ($record->tipo === 'salida' ? '-' : '+') . ' ' . abs($state) . ($record->productoPresentacion?->unidadMedida?->abreviatura ? ' ' . $record->productoPresentacion->unidadMedida->abreviatura : '')),
                                TextEntry::make('motivo')
                                    ->label('Motivo')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'compra' => 'success',
                                        'venta' => 'info',
                                        'ajuste' => 'warning',
                                        'merma' => 'danger',
                                        'anulacion' => 'gray',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'compra' => 'heroicon-m-shopping-cart',
                                        'venta' => 'heroicon-m-currency-dollar',
                                        'ajuste' => 'heroicon-m-adjustments-horizontal',
                                        'merma' => 'heroicon-m-trash',
                                        'anulacion' => 'heroicon-m-arrow-path',
                                        default => 'heroicon-m-clock',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'compra' => 'Compra',
                                        'venta' => 'Venta',
                                        'ajuste' => 'Ajuste',
                                        'merma' => 'Merma',
                                        'anulacion' => 'Anulación',
                                        default => ucfirst($state),
                                    }),
                                TextEntry::make('referencia')
                                    ->label('Referencia')
                                    ->placeholder('Ninguna')
                                    ->weight('semibold')
                                    ->color('primary'),
                                TextEntry::make('sucursal.nombre_sucursal')
                                    ->label('Sucursal')
                                    ->icon('heroicon-m-building-storefront')
                                    ->placeholder('N/A'),
                                TextEntry::make('user.name')
                                    ->label('Usuario')
                                    ->placeholder('Sistema')
                                    ->icon('heroicon-m-user'),
                                TextEntry::make('stock_final')
                                    ->label('Stock Final')
                                    ->weight('bold')
                                    ->placeholder('N/A'),
                                TextEntry::make('created_at')
                                    ->label('Fecha y Hora')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->placeholder('N/A'),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->icon('heroicon-m-building-storefront')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'salida' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'entrada' => 'heroicon-m-arrow-trending-up',
                        'salida' => 'heroicon-m-arrow-trending-down',
                        default => 'heroicon-m-arrows-right-left',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entrada' => 'Entrada',
                        'salida' => 'Salida',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->productoPresentacion?->tipo_presentacion . ($record->productoPresentacion?->unidadMedida?->abreviatura ? " ({$record->productoPresentacion->unidadMedida->abreviatura})" : "")),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->alignRight()
                    ->weight('bold')
                    ->color(fn (int $state, $record): string => $record->tipo === 'salida' ? 'danger' : 'success')
                    ->formatStateUsing(fn (int $state, $record): string => ($record->tipo === 'salida' ? '-' : '+') . ' ' . abs($state))
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'compra' => 'success',
                        'venta' => 'info',
                        'ajuste' => 'warning',
                        'merma' => 'danger',
                        'anulacion' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'compra' => 'heroicon-m-shopping-cart',
                        'venta' => 'heroicon-m-currency-dollar',
                        'ajuste' => 'heroicon-m-adjustments-horizontal',
                        'merma' => 'heroicon-m-trash',
                        'anulacion' => 'heroicon-m-arrow-path',
                        default => 'heroicon-m-clock',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'compra' => 'Compra',
                        'venta' => 'Venta',
                        'ajuste' => 'Ajuste',
                        'merma' => 'Merma',
                        'anulacion' => 'Anulación',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('referencia')
                    ->label('Referencia')
                    ->weight('medium')
                    ->color('primary')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->icon('heroicon-m-user')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stock_final')
                    ->label('Stock Final')
                    ->numeric()
                    ->alignRight()
                    ->weight('bold')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->color('info'),
            ])
            ->recordAction(ViewAction::class)
            ->filters([
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false),
                        \Filament\Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['hasta'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    })
                    ->columns(2)
                    ->columnSpan(2),

                SelectFilter::make('tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'salida' => 'Salida',
                    ]),

                Filter::make('motivo')
                    ->form([
                        Select::make('motivo')
                            ->label('Motivo')
                            ->options([
                                'compra' => 'Compra',
                                'venta' => 'Venta',
                                'ajuste' => 'Ajuste',
                                'anulacion' => 'Anulacion',
                                'merma_vencido' => 'Merma - Producto Vencido',
                                'merma_roto' => 'Merma - Producto Dañado',
                                'merma_robo' => 'Merma - Robo / Pérdida',
                                'merma_otro' => 'Merma - Otro',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['motivo'] ?? null) {
                            'compra' => $query->where('motivo', 'compra'),
                            'venta' => $query->where('motivo', 'venta'),
                            'ajuste' => $query->where('motivo', 'ajuste'),
                            'anulacion' => $query->where('motivo', 'anulacion'),
                            'merma_vencido' => $query->where('motivo', 'like', '%vencido%'),
                            'merma_roto' => $query->where('motivo', 'like', '%roto%'),
                            'merma_robo' => $query->where('motivo', 'like', '%robo%'),
                            'merma_otro' => $query->where('motivo', 'like', '%Merma%')
                                ->where('motivo', 'not like', '%vencido%')
                                ->where('motivo', 'not like', '%roto%')
                                ->where('motivo', 'not like', '%robo%'),
                            default => $query,
                        };
                    }),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->options(fn (): array => User::query()->pluck('name', 'id')->toArray())
                    ->searchable(),

                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(fn (): array => Sucursal::query()->pluck('nombre_sucursal', 'id')->toArray()),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMovimientos::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->with(['sucursal', 'user', 'productoPresentacion.unidadMedida']);

        return app(SucursalContext::class)->applyToQuery($query);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('movimientos.ver') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
