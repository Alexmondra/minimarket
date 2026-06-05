<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Sucursales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SucursalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                ImageColumn::make('imagen_sucursal')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(null),
                TextColumn::make('codigo')
                    ->label('Codigo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('nombre_sucursal')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->weight('bold')
                    ->label('Sucursal')
                    ->description(fn ($record): string => $record->direccion ?? 'Sin direccion registrada'),
                TextColumn::make('ubigeoRel.departamento')
                    ->label('Departamento')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ubigeoRel.provincia')
                    ->label('Provincia')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ubigeoRel.distrito')
                    ->label('Distrito')
                    ->icon('heroicon-o-map-pin')
                    ->searchable(),
                TextColumn::make('direccion')
                    ->label('Direccion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->icon('heroicon-o-phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('impuesto_porcentaje')
                    ->suffix('%')
                    ->numeric(2)
                    ->badge()
                    ->color('success')
                    ->label('Impuesto'),
                IconColumn::make('activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Activa'),
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
                SelectFilter::make('ubigeo')
                    ->label('Distrito')
                    ->relationship('ubigeoRel', 'distrito')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('activo')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Activas')
                    ->falseLabel('Inactivas'),
                TrashedFilter::make(),
            ])
            ->defaultSort('nombre_sucursal')
            ->recordActions([
                ViewAction::make()
                    ->label('Ver')
                    ->button()
                    ->size('sm')
                    ->color('info')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-info']),
                EditAction::make()
                    ->label('Editar')
                    ->button()
                    ->size('sm')
                    ->color('warning')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-warning']),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-danger']),
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
