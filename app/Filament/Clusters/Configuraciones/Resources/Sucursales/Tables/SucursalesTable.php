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
                    ->color('gray'),
                TextColumn::make('nombre_sucursal')
                    ->searchable()
                    ->sortable()
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
                    ->searchable(),
                TextColumn::make('direccion')
                    ->label('Direccion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telefono')
                    ->label('Telefono')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('impuesto_porcentaje')
                    ->suffix('%')
                    ->numeric(2)
                    ->label('Impuesto'),
                IconColumn::make('activo')
                    ->boolean()
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
