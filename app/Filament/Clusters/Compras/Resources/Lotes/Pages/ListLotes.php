<?php

namespace App\Filament\Clusters\Compras\Resources\Lotes\Pages;

use App\Filament\Clusters\Compras\Resources\Lotes\LoteResource;
use Filament\Resources\Pages\ListRecords;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Support\SucursalContext;

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
            ]);
    }
}
