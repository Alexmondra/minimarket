<?php

namespace App\Filament\Clusters\Compras\Resources\Compras\Pages;

use App\Filament\Clusters\Compras\Resources\Compras\CompraResource;
use App\Support\SucursalContext;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ListCompras extends ListRecords
{
    protected static string $resource = CompraResource::class;

    protected function getHeaderActions(): array
    {
        return Auth::user()->can('compras.crear') ? [
            Action::make('crearCompra')
                ->label('Crear Compra')
                ->icon('heroicon-o-plus')
                ->url(route('filament.admin.resources.compras.registrar'))
                ->color('primary')
                ->size('lg'),
        ] : [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => route('filament.admin.resources.compras.view', $record))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('proveedor.nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sucursal.nombre_sucursal')
                    ->label('Sucursal')
                    ->sortable(),
                TextColumn::make('tipo_comprobante')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
                TextColumn::make('numero_factura_proveedor')
                    ->label('N° Factura')
                    ->searchable(),
                TextColumn::make('fecha_recepcion')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('costo_total_factura')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('archivo_comprobante')
                    ->label('Comprobante')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $state) {
                            return '<span class="text-gray-400">—</span>';
                        }

                        $extension = pathinfo($state, PATHINFO_EXTENSION);
                        $url = route('filament.compras.comprobante', $record);

                        if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
                            return '<a href="'.$url.'" target="_blank" class="inline-block">
                                        <img src="'.$url.'" alt="Comprobante" class="w-16 h-16 object-cover rounded border border-gray-200 hover:opacity-80 transition-opacity">
                                    </a>';
                        }

                        if (strtolower($extension) === 'pdf') {
                            return '<a href="'.$url.'" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-danger-50 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300 rounded-md text-xs font-medium hover:bg-danger-100 dark:hover:bg-danger-900/50 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"></path><path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path></svg>
                                        Ver PDF
                                    </a>';
                        }

                        return '<span class="text-gray-400">—</span>';
                    })
                    ->html()
                    ->alignCenter(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'completada' => 'success',
                        'anulada' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label('Registró')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completada' => 'Completada',
                        'anulada' => 'Anulada',
                    ]),
                SelectFilter::make('tipo_comprobante')
                    ->label('Tipo Comprobante')
                    ->options([
                        'factura' => 'Factura',
                        'boleta' => 'Boleta',
                        'nota_credito' => 'Nota de Crédito',
                        'nota_debito' => 'Nota de Débito',
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
