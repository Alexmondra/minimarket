<?php

namespace App\Filament\Clusters\Inventario\Resources\Lotes\Pages;

use App\Filament\Clusters\Inventario\Resources\Lotes\LoteResource;
use App\Models\Lote;
use App\Support\SucursalContext;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
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
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('codigo_lote')
                    ->label('Cód. Lote')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->sortable(),
                TextColumn::make('producto_nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lotePresentaciones.productoPresentacion.tipo_presentacion')
                    ->label('Presentaciones')
                    ->listWithLineBreaks()
                    ->bulleted(),
                TextColumn::make('stock_total')
                    ->label('Stock total')
                    ->numeric(),
                TextColumn::make('fecha_vencimiento')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('ubicacion')
                    ->label('Ubicación')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estado_lote')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'vencido' => 'danger',
                        'agotado' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) =>
                $query->with(['lotePresentaciones.productoPresentacion.unidadMedida'])
                    ->orderByRaw("CASE WHEN fecha_vencimiento <= ? AND estado_lote != 'agotado' THEN 0 ELSE 1 END ASC", [now()->toDateString()])
            )
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn (\App\Models\Lote $record): ?string =>
                (($record->estado_lote === 'vencido' || $record->fecha_vencimiento->isPast()) && $record->stock_total > 0)
                    ? 'bg-danger-50/40 dark:bg-danger-950/20 text-danger-700 dark:text-danger-300'
                    : null
            )
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
                        'vencido' => 'Vencido',
                        'agotado' => 'Agotado',
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
            ])
            ->actions([
                \Filament\Actions\Action::make('registrarMerma')
                    ->label(fn (\App\Models\Lote $record): string =>
                        $record->lotePresentaciones()
                            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                            ->exists()
                            ? 'Confirmar Merma'
                            : 'Registrar Merma')
                    ->icon('heroicon-o-trash')
                    ->color(fn (\App\Models\Lote $record): string =>
                        $record->lotePresentaciones()
                            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                            ->exists()
                            ? 'warning'
                            : 'danger')
                    ->modalHeading(fn (\App\Models\Lote $record): string =>
                        $record->lotePresentaciones()
                            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                            ->exists()
                            ? 'Confirmar Merma de Lote Vencido'
                            : 'Registrar Merma / Pérdida')
                    ->modalSubmitActionLabel(fn (\App\Models\Lote $record): string =>
                        $record->lotePresentaciones()
                            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                            ->exists()
                            ? 'Confirmar'
                            : 'Registrar')
                    ->form(function (\App\Models\Lote $record): array {
                        $hasPending = $record->lotePresentaciones()
                            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                            ->exists();
                        if ($hasPending) {
                            $pendingLps = $record->lotePresentaciones()
                                ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                                ->with('productoPresentacion')
                                ->get();

                            $infoText = "Este lote está vencido y su stock se ha enviado a merma automáticamente en estado pendiente de confirmar. Las siguientes presentaciones serán confirmadas:<br><ul class='list-disc pl-5 mt-2'>";
                            foreach ($pendingLps as $lp) {
                                $mermaQty = \App\Models\LotePresentacionMerma::where('lote_presentacion_id', $lp->id)
                                    ->whereNull('user_id')
                                    ->value('cantidad') ?? 0;
                                $infoText .= "<li><strong>" . ($lp->productoPresentacion?->tipo_presentacion ?: 'Presentación') . "</strong>: " . $mermaQty . " unidades</li>";
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

                        $isExpired = ($record->fecha_vencimiento->isPast() || $record->estado_lote === 'vencido') && $record->stock_total > 0;
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
                        $hasPending = $record->lotePresentaciones()
                            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                            ->exists();

                        if ($hasPending) {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                $pendingLps = $record->lotePresentaciones()
                                    ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
                                    ->get();

                                foreach ($pendingLps as $lp) {
                                    $lp->update([
                                        'estado' => \App\Models\LotePresentacion::ESTADO_MERMA,
                                    ]);

                                    $merma = \App\Models\LotePresentacionMerma::where('lote_presentacion_id', $lp->id)
                                        ->whereNull('user_id')
                                        ->first();

                                    if ($merma) {
                                        $motivoConfirmado = isset($data['motivo']) && trim($data['motivo']) !== ''
                                            ? " | Confirmado por usuario: " . trim($data['motivo'])
                                            : "";
                                        $merma->update([
                                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                            'motivo' => $merma->motivo . $motivoConfirmado,
                                        ]);
                                    }

                                    $mov = \App\Models\MovimientoInventario::where('referencia', "LotePresentacion:{$lp->id}")
                                        ->whereNull('user_id')
                                        ->first();
                                    if ($mov) {
                                        $mov->update([
                                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                                        ]);
                                    }
                                }

                                $record->update(['estado_lote' => 'agotado']);
                            });

                            \Filament\Notifications\Notification::make()
                                ->title('Mermas de lote vencido confirmadas con éxito')
                                ->success()
                                ->send();
                            return;
                        }

                        $isExpired = ($record->fecha_vencimiento->isPast() || $record->estado_lote === 'vencido') && $record->stock_total > 0;

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
}
?>
