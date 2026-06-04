<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProveedoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(fn ($record) => route('filament.admin.resources.compras.index', ['proveedor_id' => $record->id]))
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->label('Nombre')
                    ->formatStateUsing(fn ($state) => '🚛 ' . $state)
                    ->weight('bold')
                    ->size('sm'),
                TextColumn::make('compras_count')
                    ->label('Compras')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('tipo_documento')
                    ->label('Tipo doc.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'RUC' => 'warning',
                        'DNI' => 'info',
                        'CE' => 'purple',
                        default => 'gray',
                    }),
                TextColumn::make('numero_documento')
                    ->searchable()
                    ->label('N° documento')
                    ->monospace()
                    ->size('sm'),
                TextColumn::make('razon_social')
                    ->searchable()
                    ->label('Razón social')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefono')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('contacto_principal')
                    ->label('Contacto')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rubro')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('verCompras')
                    ->label('Ver Compras')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('primary')
                    ->url(fn ($record) => route('filament.admin.resources.compras.index', ['proveedor_id' => $record->id])),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
