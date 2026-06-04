<?php

namespace App\Filament\Clusters\Inventario\Resources\Lotes\Pages;

use App\Filament\Clusters\Inventario\Resources\Lotes\LoteResource;
use App\Models\Lote;
use App\Support\SucursalContext;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListLotes extends ListRecords
{
    protected static string $resource = LoteResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Buscar lote, producto o sucursal...')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('codigo_lote')
                    ->label('Lote')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->icon('heroicon-m-clipboard-document')
                    ->description(fn (Lote $record): string => collect([
                        $record->sucursal?->nombre_sucursal,
                        $record->ubicacion ? "Ubic: {$record->ubicacion}" : null,
                    ])->filter()->implode(' • '))
                    ->wrap()
                    ->extraAttributes(['class' => 'min-w-[120px]']),
                TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Lote $record): string =>
                        $record->lotePresentaciones
                            ->map(fn ($lp) => ($lp->productoPresentacion?->tipo_presentacion ?: 'Presentación') . ': ' . $lp->stock . ' und')
                            ->take(3)
                            ->join(' • ')
                    )
                    ->wrap()
                    ->limit(30),
                TextColumn::make('stock_total')
                    ->label('Stock')
                    ->numeric()
                    ->alignRight()
                    ->weight('bold')
                    ->color(fn (Lote $record): string => match (self::lotVisualState($record)) {
                        'stock_bajo' => 'warning',
                        'sin_stock' => 'gray',
                        default => 'success',
                    })
                    ->description(fn (Lote $record): string => 'S/ ' . number_format((float) ($record->precio_compra ?? 0), 2))
                    ->extraAttributes(['class' => 'min-w-[80px]']),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn (Lote $record): ?string => 
                        $record->fecha_vencimiento
                            ? ($record->fecha_vencimiento->isPast()
                                ? 'Vencido hace ' . now()->startOfDay()->diffInDays($record->fecha_vencimiento->startOfDay()) . ' días'
                                : 'Vence en ' . now()->startOfDay()->diffInDays($record->fecha_vencimiento->startOfDay()) . ' días')
                            : null
                    )
                    ->color(fn (Lote $record): string => 
                        $record->fecha_vencimiento
                            ? ($record->fecha_vencimiento->isPast()
                                ? 'danger'
                                : (now()->startOfDay()->diffInDays($record->fecha_vencimiento->startOfDay(), false) <= 30 ? 'warning' : 'gray'))
                            : 'gray'
                    )
                    ->visibleFrom('md'),
                TextColumn::make('estado_lote')
                    ->label('Estado')
                    ->badge()
                    ->state(fn (Lote $record): string => self::lotVisualState($record))
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'activo' => 'Activo',
                        'por_vencer' => 'Por Vencer',
                        'vencido' => 'Vencido',
                        'por_confirmar' => '⚠️ Por Confirmar Merma',
                        'stock_bajo' => 'Stock Bajo',
                        'sin_stock' => 'Sin Stock',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'por_vencer' => 'warning',
                        'vencido' => 'danger',
                        'por_confirmar' => 'danger',
                        'stock_bajo' => 'warning',
                        'sin_stock' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'activo' => 'heroicon-m-check-circle',
                        'por_vencer' => 'heroicon-m-exclamation-triangle',
                        'vencido' => 'heroicon-m-x-circle',
                        'por_confirmar' => 'heroicon-m-clock',
                        'stock_bajo' => 'heroicon-m-arrow-trending-down',
                        'sin_stock' => 'heroicon-m-minus-circle',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Lote $record): string => $record->created_at?->diffForHumans() ?? '')
                    ->visibleFrom('xl'),
            ])
            ->modifyQueryUsing(fn ($query) =>
                $query->with(['lotePresentaciones.productoPresentacion.unidadMedida'])
                    ->whereHas('lotePresentaciones', fn ($q) => $q->where('stock', '>', 0))
                    ->orderByRaw("CASE
                        WHEN estado_lote = 'por_confirmar' THEN 0
                        WHEN estado_lote = 'stock_bajo' THEN 1
                        WHEN estado_lote = 'por_vencer' OR (fecha_vencimiento IS NOT NULL AND fecha_vencimiento > NOW() AND fecha_vencimiento <= DATE_ADD(NOW(), INTERVAL 30 DAY)) THEN 2
                        ELSE 3
                    END ASC")
            )
            ->recordClasses(fn (\App\Models\Lote $record): ?string =>
                match (self::lotVisualState($record)) {
                    'por_confirmar' => 'alert-pulse bg-rose-100/80 dark:bg-rose-950/30 border-l-4 border-l-rose-500',
                    'stock_bajo' => 'alert-pulse-amber bg-amber-50/80 dark:bg-amber-950/20 border-l-4 border-l-amber-500',
                    'vencido' => 'bg-rose-50/60 dark:bg-rose-950/10 border-l-4 border-l-rose-500',
                    'por_vencer' => 'bg-amber-50/40 dark:bg-amber-950/5 border-l-4 border-l-amber-400',
                    default => null,
                }
            )
            ->striped()
            ->filters([
                SelectFilter::make('producto_nombre')
                    ->label('Producto')
                    ->options(fn (): array => Lote::query()
                        ->select('producto_nombre')
                        ->whereNotNull('producto_nombre')
                        ->distinct()
                        ->orderBy('producto_nombre')
                        ->pluck('producto_nombre', 'producto_nombre')
                        ->all()),
                SelectFilter::make('estado_lote')
                    ->label('Estado')
                    ->options([
                        'activo' => 'Activo',
                        'por_confirmar' => 'Por Confirmar Merma',
                        'vencido' => 'Vencido',
                    ]),
                SelectFilter::make('sucursal_id')
                    ->label('Sucursal')
                    ->options(fn (): array => app(SucursalContext::class)
                        ->sucursalesForWrite()
                        ->pluck('nombre_sucursal', 'id')
                        ->all()),
                Filter::make('fecha_fabricacion')
                    ->label('Fabricación')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'] ?? null, fn (Builder $q, $date) => $q->whereDate('fecha_fabricacion', '>=', $date))
                            ->when($data['hasta'] ?? null, fn (Builder $q, $date) => $q->whereDate('fecha_fabricacion', '<=', $date));
                    }),
                Filter::make('fecha_vencimiento')
                    ->label('Vencimiento')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('hasta')
                            ->label('Hasta')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['desde'] ?? null, fn (Builder $q, $date) => $q->whereDate('fecha_vencimiento', '>=', $date))
                            ->when($data['hasta'] ?? null, fn (Builder $q, $date) => $q->whereDate('fecha_vencimiento', '<=', $date));
                    }),
            ], layout: FiltersLayout::Dropdown)
            ->filtersFormColumns(4)
            ->actions([
                \Filament\Actions\Action::make('registrarMerma')
                    ->label(fn (\App\Models\Lote $record): string =>
                        self::lotVisualState($record) === 'por_confirmar'
                            ? 'Confirmar Vencido'
                            : 'Registrar Merma')
                    ->icon(fn (\App\Models\Lote $record): string =>
                        self::lotVisualState($record) === 'por_confirmar'
                            ? 'heroicon-m-check'
                            : 'heroicon-m-trash')
                    ->color(fn (\App\Models\Lote $record): string =>
                        match (self::lotVisualState($record)) {
                            'por_confirmar', 'vencido' => 'danger',
                            'stock_bajo', 'por_vencer' => 'warning',
                            default => 'danger',
                        })
                    ->button()
                    ->size('sm')
                    ->extraAttributes(fn (\App\Models\Lote $record): array => [
                        'class' => match (self::lotVisualState($record)) {
                            'por_confirmar' => 'alert-pulse',
                            'stock_bajo' => 'alert-pulse-amber',
                            default => '',
                        },
                    ])
                    ->modalHeading(fn (\App\Models\Lote $record): string =>
                        self::lotVisualState($record) === 'por_confirmar'
                            ? 'Confirmar Merma de Lote Vencido'
                            : 'Registrar Merma / Pérdida')
                    ->modalSubmitActionLabel(fn (\App\Models\Lote $record): string =>
                        self::lotVisualState($record) === 'por_confirmar'
                            ? 'Confirmar'
                            : 'Registrar')
                    ->form(function (\App\Models\Lote $record): array {
                        if (self::lotVisualState($record) === 'por_confirmar') {
                            $lps = $record->lotePresentaciones()
                                ->where('stock', '>', 0)
                                ->with('productoPresentacion')
                                ->get();

                            $infoText = "Este lote está vencido y tiene stock real. Al confirmar la merma, se registrará el retiro de la totalidad del stock de las siguientes presentaciones:<br><ul class='list-disc pl-5 mt-2'>";
                            foreach ($lps as $lp) {
                                $infoText .= "<li><strong>" . ($lp->productoPresentacion?->tipo_presentacion ?: 'Presentación') . "</strong>: " . $lp->stock . " unidades</li>";
                            }
                            $infoText .= "</ul>";

                            return [
                                \Filament\Forms\Components\Placeholder::make('info_confirmacion')
                                    ->label('')
                                    ->content(new \Illuminate\Support\HtmlString($infoText)),
                                \Filament\Forms\Components\Textarea::make('motivo')
                                    ->label('Observación / Motivo de Confirmación')
                                    ->placeholder('Observaciones sobre el lote vencido (opcional)')
                                    ->maxLength(500),
                            ];
                        }

                        $isExpired = ($record->fecha_vencimiento && $record->fecha_vencimiento->isPast()) && $record->stock_total > 0;
                        if ($isExpired) {
                            return [
                                \Filament\Forms\Components\Placeholder::make('advertencia')
                                    ->label('')
                                    ->content("⚠️ Este lote está vencido. Al proceder, se registrará la totalidad del stock restante ({$record->stock_total} unidades) como merma automáticamente."),
                            ];
                        }

                        return [
                            \Filament\Forms\Components\Select::make('lote_presentacion_id')
                                ->label('Presentación')
                                ->options(
                                    $record->lotePresentaciones()
                                        ->with('productoPresentacion')
                                        ->get()
                                        ->mapWithKeys(fn ($lp) => [$lp->id => ($lp->productoPresentacion?->tipo_presentacion ?: 'Presentación') . " (Stock: {$lp->stock})"])
                                        ->all()
                                )
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, $set) =>
                                    $set('max_cantidad', \App\Models\LotePresentacion::find($state)?->stock ?? 0)
                                ),
                            \Filament\Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->integer()
                                ->required()
                                ->minValue(1)
                                ->maxValue(fn ($get) => $get('max_cantidad') ?? 1000)
                                ->validationMessages([
                                    'max' => 'Cantidad excedida, no existe esta cantidad en el stock disponible.',
                                ])
                                ->helperText(fn ($get) => "Stock disponible: " . ($get('max_cantidad') ?? 0)),
                            \Filament\Forms\Components\Select::make('tipo_merma')
                                ->label('Tipo de Merma')
                                ->options([
                                    'vencido' => 'Vencido / Expirado',
                                    'roto' => 'Roto / Dañado',
                                    'robo' => 'Robo / Pérdida',
                                    'otro' => 'Otro motivo',
                                ])
                                ->required(),
                            \Filament\Forms\Components\Textarea::make('motivo')
                                ->label('Observación / Motivo')
                                ->placeholder('Describa brevemente la razón de la pérdida')
                                ->maxLength(500),
                        ];
                    })
                    ->action(function (\App\Models\Lote $record, array $data): void {
                        if (self::lotVisualState($record) === 'por_confirmar') {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                $lps = $record->lotePresentaciones()
                                    ->where('stock', '>', 0)
                                    ->with('productoPresentacion.producto')
                                    ->get();

                                foreach ($lps as $lp) {
                                    $cantidad = $lp->stock;

                                    \App\Models\LotePresentacionMerma::create([
                                        'lote_presentacion_id' => $lp->id,
                                        'cantidad' => $cantidad,
                                        'tipo_merma' => 'vencido',
                                        'motivo' => 'Merma de lote vencido confirmado manualmente' . (isset($data['motivo']) && trim($data['motivo']) !== '' ? ': ' . trim($data['motivo']) : ''),
                                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                    ]);

                                    \App\Models\MovimientoInventario::create([
                                        'empresa_id' => \Illuminate\Support\Facades\Auth::user()->empresa_id ?? 1,
                                        'sucursal_id' => $record->sucursal_id,
                                        'producto_nombre' => $lp->productoPresentacion?->producto?->nombre ?? $record->producto_nombre,
                                        'producto_presentacion_id' => $lp->producto_presentacion_id,
                                        'tipo' => 'salida_merma',
                                        'cantidad' => -$cantidad,
                                        'motivo' => "Merma de lote vencido - Lote {$record->codigo_lote}",
                                        'referencia' => "LotePresentacion:{$lp->id}",
                                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                        'stock_final' => 0,
                                    ]);

                                    $lp->update([
                                        'stock' => 0,
                                        'estado' => \App\Models\LotePresentacion::ESTADO_MERMA,
                                    ]);
                                }

                                $record->update(['estado_lote' => 'agotado']);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Mermas de lote vencido confirmadas con éxito')
                                ->success()
                                ->send();
                            return;
                        }

                        $isExpired = ($record->fecha_vencimiento && $record->fecha_vencimiento->isPast()) && $record->stock_total > 0;

                        if ($isExpired) {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                                foreach ($record->lotePresentaciones as $lp) {
                                    if ($lp->stock > 0) {
                                        $cantidad = $lp->stock;
                                        \App\Models\LotePresentacionMerma::create([
                                            'lote_presentacion_id' => $lp->id,
                                            'cantidad' => $cantidad,
                                            'tipo_merma' => 'vencido',
                                            'motivo' => 'Merma manual de lote vencido (procesamiento rápido)',
                                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                        ]);

                                        \App\Models\MovimientoInventario::create([
                                            'empresa_id' => \Illuminate\Support\Facades\Auth::user()->empresa_id ?? 1,
                                            'sucursal_id' => $record->sucursal_id,
                                            'producto_nombre' => $lp->productoPresentacion?->producto?->nombre ?? $record->producto_nombre,
                                            'producto_presentacion_id' => $lp->producto_presentacion_id,
                                            'tipo' => 'salida_merma',
                                            'cantidad' => -$cantidad,
                                            'motivo' => "Merma rápida lote vencido - Lote {$record->codigo_lote}",
                                            'referencia' => "LotePresentacion:{$lp->id}",
                                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                            'stock_final' => 0,
                                        ]);

                                        $lp->update([
                                            'stock' => 0,
                                            'estado' => \App\Models\LotePresentacion::ESTADO_MERMA,
                                        ]);
                                    }
                                }

                                $record->update(['estado_lote' => 'agotado']);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Lote vencido procesado por completo como merma')
                                ->success()
                                ->send();
                            return;
                        }

                        $lotePresentacion = \App\Models\LotePresentacion::with('lote.sucursal', 'productoPresentacion.producto')
                            ->findOrFail($data['lote_presentacion_id']);
                        $cantidad = (int) $data['cantidad'];

                        if ($lotePresentacion->stock < $cantidad) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error: Stock insuficiente')
                                ->danger()
                                ->send();
                            return;
                        }

                        \Illuminate\Support\Facades\DB::transaction(function () use ($lotePresentacion, $cantidad, $data) {
                            $nuevoStock = $lotePresentacion->stock - $cantidad;

                            $estadolp = $nuevoStock === 0 ? \App\Models\LotePresentacion::ESTADO_MERMA : $lotePresentacion->estado;

                            $lotePresentacion->update([
                                'stock' => $nuevoStock,
                                'estado' => $estadolp,
                            ]);

                            \App\Models\LotePresentacionMerma::create([
                                'lote_presentacion_id' => $lotePresentacion->id,
                                'cantidad' => $cantidad,
                                'tipo_merma' => $data['tipo_merma'],
                                'motivo' => $data['motivo'] ?? null,
                                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            ]);

                            \App\Models\MovimientoInventario::create([
                                'empresa_id' => \Illuminate\Support\Facades\Auth::user()->empresa_id ?? 1,
                                'sucursal_id' => $lotePresentacion->lote->sucursal_id,
                                'producto_nombre' => $lotePresentacion->productoPresentacion?->producto?->nombre ?? $lotePresentacion->lote->producto_nombre,
                                'producto_presentacion_id' => $lotePresentacion->producto_presentacion_id,
                                'tipo' => 'salida_merma',
                                'cantidad' => -$cantidad,
                                'motivo' => "Merma ({$data['tipo_merma']}) - Lote {$lotePresentacion->lote->codigo_lote}: " . ($data['motivo'] ?? ''),
                                'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                'stock_final' => $nuevoStock,
                            ]);

                            if ($lotePresentacion->lote->stock_total <= 0) {
                                $lotePresentacion->lote->update(['estado_lote' => 'agotado']);
                            }
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Merma registrada correctamente')
                            ->success()
                            ->send();
                    })
                    ->label('Acciones'),
            ]);
    }

    protected static function lotVisualState(Lote $record): string
    {
        if ($record->stock_total <= 0) {
            return 'sin_stock';
        }

        if ($record->estado_lote === 'por_confirmar') {
            return 'por_confirmar';
        }

        if ($record->fecha_vencimiento && $record->fecha_vencimiento->isPast()) {
            return 'vencido';
        }

        if ($record->lotePresentaciones->contains(fn ($lp) => $lp->stock > 0 && $lp->productoSucursal && $lp->stock <= $lp->productoSucursal->stock_minimo)) {
            return 'stock_bajo';
        }

        if ($record->fecha_vencimiento && now()->startOfDay()->diffInDays($record->fecha_vencimiento->startOfDay(), false) <= 30) {
            return 'por_vencer';
        }

        return 'activo';
    }
}
