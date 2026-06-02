<?php

namespace App\Filament\Clusters\Inventario\Resources\Mermas;

use App\Filament\Clusters\Inventario\Resources\Mermas\Pages\ListMermas;
use App\Models\LotePresentacionMerma;
use App\Models\Sucursal;
use App\Models\User;
use App\Support\SucursalContext;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                Section::make('Resumen de incidencia')
                    ->description('Lectura rápida para tomar acción sin revisar toda la fila.')
                    ->icon('heroicon-m-shield-exclamation')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                TextEntry::make('tipo_merma')
                                    ->label('Estado')
                                    ->badge()
                                    ->size('lg')
                                    ->icon(fn (string $state): string => match ($state) {
                                        'vencido' => 'heroicon-m-no-symbol',
                                        'roto' => 'heroicon-m-wrench-screwdriver',
                                        'robo' => 'heroicon-m-shield-exclamation',
                                        default => 'heroicon-m-question-mark-circle',
                                    })
                                    ->color(fn (string $state): string => match ($state) {
                                        'vencido' => 'danger',
                                        'roto' => 'warning',
                                        'robo' => 'orange',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'vencido' => 'Producto vencido',
                                        'roto' => 'Producto danado',
                                        'robo' => 'Perdida / faltante',
                                        'otro' => 'Otra incidencia',
                                        default => ucfirst($state),
                                    }),
                                TextEntry::make('cantidad')
                                    ->label('Cantidad afectada')
                                    ->numeric()
                                    ->suffix(' unidades')
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-m-cube-transparent'),
                                TextEntry::make('impacto_estimado')
                                    ->label('Impacto estimado')
                                    ->state(fn (LotePresentacionMerma $record): float => (float) $record->cantidad * (float) ($record->lotePresentacion?->precio_compra ?? 0))
                                    ->money('PEN')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-m-banknotes'),
                            ]),
                    ]),
                Section::make('Producto y lote')
                    ->icon('heroicon-m-archive-box')
                    ->compact()
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])
                            ->schema([
                                TextEntry::make('lotePresentacion.productoPresentacion.producto.nombre')
                                    ->label('Producto')
                                    ->weight('bold')
                                    ->icon('heroicon-m-cube')
                                    ->iconColor('primary')
                                    ->placeholder('N/A'),
                                TextEntry::make('lotePresentacion.productoPresentacion.tipo_presentacion')
                                    ->label('Presentación')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('N/A'),
                                TextEntry::make('lotePresentacion.lote.codigo_lote')
                                    ->label('Lote')
                                    ->copyable()
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-m-clipboard-document')
                                    ->placeholder('N/A'),
                                TextEntry::make('lotePresentacion.lote.sucursal.nombre_sucursal')
                                    ->label('Sucursal')
                                    ->icon('heroicon-m-building-storefront')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('N/A'),
                                TextEntry::make('user.name')
                                    ->label('Registrado por')
                                    ->placeholder('Sistema (Automático)')
                                    ->icon('heroicon-m-user'),
                                TextEntry::make('created_at')
                                    ->label('Fecha de Registro')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->since()
                                    ->placeholder('N/A'),
                            ]),
                    ]),
                Section::make('Observación')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->compact()
                    ->schema([
                        TextEntry::make('motivo')
                            ->hiddenLabel()
                            ->placeholder('Sin observaciones registradas.')
                            ->prose()
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Buscar producto, lote, sucursal o motivo...')
            ->columns([
                TextColumn::make('tipo_merma')
                    ->label('Alerta')
                    ->badge()
                    ->grow(false)
                    ->color(fn (string $state): string => match ($state) {
                        'vencido' => 'danger',
                        'roto' => 'warning',
                        'robo' => 'orange',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'vencido' => 'heroicon-m-no-symbol',
                        'roto' => 'heroicon-m-wrench-screwdriver',
                        'robo' => 'heroicon-m-shield-exclamation',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'vencido' => 'Vencido',
                        'roto' => 'Dañado',
                        'robo' => 'Pérdida',
                        'otro' => 'Otro',
                        default => ucfirst($state),
                    })
                    ->sortable()
                    ->visibleFrom('md'),

                TextColumn::make('lotePresentacion.productoPresentacion.producto.nombre')
                    ->label('Producto')
                    ->weight('bold')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->whereHas('lotePresentacion.productoPresentacion.producto', fn (Builder $sub) => $sub->where('nombre', 'like', "%{$search}%"))
                                ->orWhereHas('lotePresentacion.lote', fn (Builder $sub) => $sub
                                    ->where('codigo_lote', 'like', "%{$search}%")
                                    ->orWhereHas('sucursal', fn (Builder $sucursal) => $sucursal->where('nombre_sucursal', 'like', "%{$search}%")))
                                ->orWhere('motivo', 'like', "%{$search}%");
                        });
                    })
                    ->sortable()
                    ->description(function (LotePresentacionMerma $record): string {
                        $partes = [];

                        if (filled($record->tipo_merma)) {
                            $partes[] = match ($record->tipo_merma) {
                                'vencido' => 'VENCIDO',
                                'roto' => 'DANADO',
                                'robo' => 'PERDIDA',
                                'otro' => 'OTRO',
                                default => strtoupper($record->tipo_merma),
                            };
                        }

                        if (filled($record->lotePresentacion?->productoPresentacion?->tipo_presentacion)) {
                            $partes[] = $record->lotePresentacion->productoPresentacion->tipo_presentacion;
                        }

                        if (filled($record->lotePresentacion?->lote?->codigo_lote)) {
                            $partes[] = 'Lote ' . $record->lotePresentacion->lote->codigo_lote;
                        }

                        if (filled($record->lotePresentacion?->lote?->sucursal?->nombre_sucursal)) {
                            $partes[] = $record->lotePresentacion->lote->sucursal->nombre_sucursal;
                        }

                        return implode(' • ', $partes);
                    })
                    ->wrap(),

                TextColumn::make('impacto_estimado')
                    ->label('Impacto')
                    ->state(fn (LotePresentacionMerma $record): float => (float) $record->cantidad * (float) ($record->lotePresentacion?->precio_compra ?? 0))
                    ->money('PEN')
                    ->description(fn (LotePresentacionMerma $record): string => number_format((float) $record->cantidad) . ' uds')
                    ->alignRight()
                    ->weight('bold')
                    ->color('danger')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query
                        ->join('lote_presentacion', 'lote_presentacion_mermas.lote_presentacion_id', '=', 'lote_presentacion.id')
                        ->orderByRaw("lote_presentacion_mermas.cantidad * COALESCE(lote_presentacion.precio_compra, 0) {$direction}")
                        ->select('lote_presentacion_mermas.*')),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (LotePresentacionMerma $record): string => $record->created_at?->diffForHumans() ?? '')
                    ->color('slate')
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->size('sm')
                    ->color('info')
                    ->modalHeading(fn (LotePresentacionMerma $record): string => $record->lotePresentacion?->productoPresentacion?->producto?->nombre ?: 'Detalle de merma')
                    ->modalDescription(fn (LotePresentacionMerma $record): string => collect([
                        match ($record->tipo_merma) {
                            'vencido' => 'Incidencia por vencimiento',
                            'roto' => 'Incidencia por dano',
                            'robo' => 'Incidencia por perdida',
                            'otro' => 'Incidencia registrada manualmente',
                            default => 'Incidencia registrada',
                        },
                        $record->lotePresentacion?->productoPresentacion?->tipo_presentacion,
                        $record->lotePresentacion?->lote?->codigo_lote ? 'Lote ' . $record->lotePresentacion->lote->codigo_lote : null,
                    ])->filter()->implode(' • '))
                    ->modalWidth('4xl')
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
            ], layout: FiltersLayout::Dropdown)
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
