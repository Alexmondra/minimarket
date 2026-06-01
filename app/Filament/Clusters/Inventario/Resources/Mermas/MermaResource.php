<?php

namespace App\Filament\Clusters\Inventario\Resources\Mermas;

use App\Filament\Clusters\Inventario\Resources\Mermas\Pages\ListMermas;
use App\Models\LotePresentacionMerma;
use App\Models\User;
use App\Models\Sucursal;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class MermaResource extends Resource
{
    protected static ?string $model = LotePresentacionMerma::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trash';

    protected static string|UnitEnum|null $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Productos Dañados y Vencidos';

    protected static ?string $pluralModelLabel = 'Mermas y Pérdidas';

    protected static ?string $modelLabel = 'Merma';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalle de Merma / Pérdida')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('lotePresentacion.productoPresentacion.producto.nombre')
                                    ->label('Producto')
                                    ->weight('bold')
                                    ->placeholder('N/A'),
                                TextEntry::make('lotePresentacion.productoPresentacion.tipo_presentacion')
                                    ->label('Presentación')
                                    ->placeholder('N/A'),
                                TextEntry::make('lotePresentacion.lote.codigo_lote')
                                    ->label('Lote')
                                    ->copyable()
                                    ->placeholder('N/A'),
                                TextEntry::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->suffix(' unidades'),
                                TextEntry::make('tipo_merma')
                                    ->label('Tipo de Merma')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'vencido' => 'danger',
                                        'roto' => 'warning',
                                        'robo' => 'orange',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'vencido' => 'Vencido',
                                        'roto' => 'Roto / Dañado',
                                        'robo' => 'Robo / Pérdida',
                                        default => ucfirst($state),
                                    }),
                                TextEntry::make('lotePresentacion.lote.sucursal.nombre_sucursal')
                                    ->label('Sucursal')
                                    ->icon('heroicon-m-building-storefront')
                                    ->placeholder('N/A'),
                                TextEntry::make('user.name')
                                    ->label('Registrado por')
                                    ->placeholder('Sistema (Automático)')
                                    ->icon('heroicon-m-user'),
                                TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->placeholder('N/A'),
                            ]),
                        TextEntry::make('motivo')
                            ->label('Observación / Motivo')
                            ->placeholder('Sin observaciones registradas.')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('lotePresentacion.lote.sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->icon('heroicon-m-building-storefront')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('lotePresentacion.lote.codigo_lote')
                    ->label('Lote')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->copyable()
                    ->icon('heroicon-m-clipboard-document'),

                TextColumn::make('lotePresentacion.productoPresentacion.producto.nombre')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->lotePresentacion?->productoPresentacion?->tipo_presentacion),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->alignRight()
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('tipo_merma')
                    ->label('Tipo Merma')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vencido' => 'danger',
                        'roto' => 'warning',
                        'robo' => 'orange',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'vencido' => 'heroicon-m-x-circle',
                        'roto' => 'heroicon-m-exclamation-triangle',
                        'robo' => 'heroicon-m-shield-exclamation',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vencido' => 'Vencido',
                        'roto' => 'Roto / Dañado',
                        'robo' => 'Robo / Pérdida',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo / Observación')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->icon('heroicon-m-user')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ?: 'Sistema (Auto)')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->color('info')
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

                SelectFilter::make('tipo_merma')
                    ->label('Tipo Merma')
                    ->options([
                        'vencido' => 'Vencido',
                        'roto' => 'Roto / Dañado',
                        'robo' => 'Robo / Pérdida',
                        'otro' => 'Otro',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->options(fn (): array => User::query()->pluck('name', 'id')->toArray())
                    ->searchable(),

                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(fn (): array => Sucursal::query()->pluck('nombre_sucursal', 'id')->toArray())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'] ?? null, function ($q, $sucursalId) {
                            $q->whereHas('lotePresentacion.lote', fn ($l) => $l->where('sucursal_id', $sucursalId));
                        });
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(4);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMermas::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['lotePresentacion.lote.sucursal', 'lotePresentacion.productoPresentacion.producto', 'user']);

        $activeId = app(SucursalContext::class)->activeSucursalId();
        if ($activeId) {
            $query->whereHas('lotePresentacion.lote', function ($q) use ($activeId) {
                $q->where('sucursal_id', $activeId);
            });
        } else {
            $allowedIds = app(SucursalContext::class)->allowedSucursalIds();
            $query->whereHas('lotePresentacion.lote', function ($q) use ($allowedIds) {
                $q->whereIn('sucursal_id', $allowedIds->all());
            });
        }

        return $query;
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
