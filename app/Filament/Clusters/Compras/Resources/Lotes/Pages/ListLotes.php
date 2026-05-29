<?php

namespace App\Filament\Clusters\Compras\Resources\Lotes\Pages;

use App\Filament\Clusters\Compras\Resources\Lotes\LoteResource;
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
                TextColumn::make('precio_compra')
                    ->label('Total pagado')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('fecha_fabricacion')
                    ->label('Fabricación')
                    ->date('d/m/Y')
                    ->sortable(),
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
            ->modifyQueryUsing(fn ($query) => $query->with(['lotePresentaciones.productoPresentacion.unidadMedida']))
            ->defaultSort('created_at', 'desc')
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
                    ->label('Registrar Merma')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->modalHeading('Registrar Merma / Pérdida')
                    ->modalSubmitActionLabel('Registrar')
                    ->form(fn (\App\Models\Lote $record): array => [
                        \Filament\Forms\Components\Select::make('lote_presentacion_id')
                            ->label('Presentación')
                            ->options(
                                $record->lotePresentaciones()
                                    ->with('productoPresentacion')
                                    ->get()
                                    ->mapWithKeys(fn ($lp) => [
                                        $lp->id => ($lp->productoPresentacion?->tipo_presentacion ?: 'Presentación') . " (Stock: {$lp->stock})"
                                    ])
                                    ->all()
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, \Filament\Forms\Set $set) =>
                                $set('max_cantidad', \App\Models\LotePresentacion::find($state)?->stock ?? 0)
                            ),
                        \Filament\Forms\Components\TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->min(1)
                            ->maxValue(fn (\Filament\Forms\Get $get) => $get('max_cantidad') ?? 1000)
                            ->helperText(fn (\Filament\Forms\Get $get) => "Stock disponible: " . ($get('max_cantidad') ?? 0)),
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
                    ])
                    ->action(function (array $data): void {
                        $lotePresentacion = \App\Models\LotePresentacion::with('lote.sucursal', 'productoPresentacion.producto')->findOrFail($data['lote_presentacion_id']);
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
                            $lotePresentacion->update(['stock' => $nuevoStock]);

                            // Crear registro de merma
                            \App\Models\LotePresentacionMerma::create([
                                'lote_presentacion_id' => $lotePresentacion->id,
                                'cantidad' => $cantidad,
                                'tipo_merma' => $data['tipo_merma'],
                                'motivo' => $data['motivo'] ?? null,
                                'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            ]);

                            // Registrar en Kardex
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
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Merma registrada correctamente')
                            ->success()
                            ->send();
                    })
            ]);
    }
}
