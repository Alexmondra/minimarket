<?php

namespace App\Filament\Clusters\Inventario\Resources\Movimientos;

use App\Filament\Clusters\Inventario\Resources\Movimientos\Pages\ListMovimientos;
use App\Models\MovimientoInventario;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
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
use Illuminate\Support\Str;
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
                Section::make('Resumen del movimiento')
                    ->description('Lectura rapida para entender que entro, que salio y por que.')
                    ->icon('heroicon-m-arrows-right-left')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                TextEntry::make('tipo')
                                    ->label('Direccion')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'entrada' => 'success',
                                        'salida' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'entrada' => 'heroicon-m-arrow-left-circle',
                                        'salida' => 'heroicon-m-arrow-right-circle',
                                        default => 'heroicon-m-arrows-right-left',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'entrada' => 'Entrada',
                                        'salida' => 'Salida',
                                        default => ucfirst($state),
                                    })
                                    ->size('lg'),
                                TextEntry::make('cantidad')
                                    ->label('Cantidad movida')
                                    ->badge()
                                    ->weight('bold')
                                    ->color(fn ($state, $record): string => $record->tipo === 'salida' ? 'danger' : 'success')
                                    ->icon(fn ($state, $record): string => $record->tipo === 'salida' ? 'heroicon-m-arrow-up-right' : 'heroicon-m-arrow-down-left')
                                    ->formatStateUsing(fn ($state, $record) => ($record->tipo === 'salida' ? '-' : '+') . ' ' . abs($state) . ($record->productoPresentacion?->unidadMedida?->abreviatura ? ' ' . $record->productoPresentacion->unidadMedida->abreviatura : '')),
                                TextEntry::make('motivo')
                                    ->label('Motivo')
                                    ->badge()
                                    ->color(fn (?string $state): string => self::movementReasonColor($state))
                                    ->icon(fn (?string $state): string => self::movementReasonIcon($state))
                                    ->formatStateUsing(fn (?string $state): string => self::movementReasonLabel($state)),
                            ]),
                    ]),
                Section::make('Producto y trazabilidad')
                    ->icon('heroicon-m-cube')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])
                            ->schema([
                                TextEntry::make('producto_nombre')
                                    ->label('Producto')
                                    ->weight('bold')
                                    ->icon('heroicon-m-cube')
                                    ->iconColor('primary')
                                    ->placeholder('N/A'),
                                TextEntry::make('productoPresentacion.tipo_presentacion')
                                    ->label('Presentación')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('N/A'),
                                TextEntry::make('referencia')
                                    ->label('Referencia')
                                    ->placeholder('Ninguna')
                                    ->weight('semibold')
                                    ->color('primary')
                                    ->badge(),
                                TextEntry::make('sucursal.nombre_sucursal')
                                    ->label('Sucursal')
                                    ->icon('heroicon-m-building-storefront')
                                    ->badge()
                                    ->color('info')
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
                                    ->since()
                                    ->placeholder('N/A'),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Buscar producto, referencia, motivo o sucursal...')
            ->columns([
                TextColumn::make('tipo')
                    ->label('Mov.')
                    ->badge()
                    ->grow(false)
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'salida' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'entrada' => 'heroicon-m-arrow-down-left',
                        'salida' => 'heroicon-m-arrow-up-right',
                        default => 'heroicon-m-arrows-right-left',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'entrada' => 'Entrada',
                        'salida' => 'Salida',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->where('producto_nombre', 'like', "%{$search}%")
                                ->orWhere('referencia', 'like', "%{$search}%")
                                ->orWhere('motivo', 'like', "%{$search}%")
                                ->orWhereHas('sucursal', fn (Builder $sub) => $sub->where('nombre_sucursal', 'like', "%{$search}%"));
                        });
                    })
                    ->sortable()
                    ->description(function ($record): string {
                        $partes = [];

                        if (filled($record->tipo)) {
                            $partes[] = $record->tipo === 'entrada' ? 'ENTRADA' : 'SALIDA';
                        }

                        if (filled($record->motivo)) {
                            $partes[] = strtoupper(self::movementReasonLabel($record->motivo));
                        }

                        if (filled($record->productoPresentacion?->tipo_presentacion)) {
                            $partes[] = $record->productoPresentacion->tipo_presentacion . ($record->productoPresentacion?->unidadMedida?->abreviatura ? " ({$record->productoPresentacion->unidadMedida->abreviatura})" : "");
                        }

                        if (filled($record->sucursal?->nombre_sucursal)) {
                            $partes[] = $record->sucursal->nombre_sucursal;
                        }

                        return implode(' • ', $partes);
                    })
                    ->wrap(),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->alignRight()
                    ->weight('bold')
                    ->color(fn (int $state, $record): string => $record->tipo === 'salida' ? 'danger' : 'success')
                    ->icon(fn (int $state, $record): string => $record->tipo === 'salida' ? 'heroicon-m-arrow-up-right' : 'heroicon-m-arrow-down-left')
                    ->formatStateUsing(fn (int $state, $record): string => ($record->tipo === 'salida' ? '-' : '+') . ' ' . abs($state))
                    ->description(fn ($state, $record): string => 'Stock final: ' . number_format((float) ($record->stock_final ?? 0)))
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->badge()
                    ->color(fn (?string $state): string => self::movementReasonColor($state))
                    ->icon(fn (?string $state): string => self::movementReasonIcon($state))
                    ->formatStateUsing(fn (?string $state): string => self::movementReasonLabel($state))
                    ->sortable()
                    ->visibleFrom('lg'),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn ($record): string => $record->created_at?->diffForHumans() ?? '')
                    ->sortable()
                    ->visibleFrom('xl'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->size('sm')
                    ->color('info')
                    ->modalHeading(fn (MovimientoInventario $record): string => $record->producto_nombre ?: 'Detalle de movimiento')
                    ->modalDescription(fn (MovimientoInventario $record): string => collect([
                        $record->tipo === 'entrada' ? 'Ingreso de inventario' : 'Salida de inventario',
                        self::movementReasonLabel($record->motivo),
                        $record->sucursal?->nombre_sucursal,
                    ])->filter()->implode(' • '))
                    ->modalWidth('4xl'),
            ])
            ->recordAction(ViewAction::class)
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->native(false),
                        DatePicker::make('hasta')
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
            ], layout: FiltersLayout::Dropdown)
            ->filtersFormColumns(4);
    }

    protected static function movementReasonColor(?string $state): string
    {
        $value = Str::lower((string) $state);

        return match (true) {
            str_contains($value, 'vencido') => 'danger',
            str_contains($value, 'roto'), str_contains($value, 'dañado'), str_contains($value, 'danado') => 'warning',
            str_contains($value, 'robo'), str_contains($value, 'perdida') => 'danger',
            str_contains($value, 'merma') => 'danger',
            $value === 'compra' => 'success',
            $value === 'venta' => 'info',
            $value === 'ajuste' => 'warning',
            $value === 'anulacion' => 'gray',
            default => 'gray',
        };
    }

    protected static function movementReasonIcon(?string $state): string
    {
        $value = Str::lower((string) $state);

        return match (true) {
            str_contains($value, 'vencido') => 'heroicon-m-no-symbol',
            str_contains($value, 'roto'), str_contains($value, 'dañado'), str_contains($value, 'danado') => 'heroicon-m-wrench-screwdriver',
            str_contains($value, 'robo'), str_contains($value, 'perdida') => 'heroicon-m-shield-exclamation',
            str_contains($value, 'merma') => 'heroicon-m-trash',
            $value === 'compra' => 'heroicon-m-shopping-cart',
            $value === 'venta' => 'heroicon-m-currency-dollar',
            $value === 'ajuste' => 'heroicon-m-adjustments-horizontal',
            $value === 'anulacion' => 'heroicon-m-arrow-path',
            default => 'heroicon-m-clock',
        };
    }

    protected static function movementReasonLabel(?string $state): string
    {
        $value = Str::lower((string) $state);

        return match (true) {
            str_contains($value, 'vencido') => 'Vencido',
            str_contains($value, 'roto'), str_contains($value, 'dañado'), str_contains($value, 'danado') => 'Dañado',
            str_contains($value, 'robo'), str_contains($value, 'perdida') => 'Pérdida',
            $value === 'compra' => 'Compra',
            $value === 'venta' => 'Venta',
            $value === 'ajuste' => 'Ajuste',
            $value === 'anulacion' => 'Anulación',
            str_contains($value, 'merma') => 'Merma',
            blank($state) => 'Sin motivo',
            default => Str::headline((string) $state),
        };
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
