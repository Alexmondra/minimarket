<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Ubigeos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UbigeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                TextColumn::make('ubigeo')
                    ->label('Codigo')
                    ->badge()
                    ->color('primary')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('Superficie')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('Y')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('x')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('departamento')
                    ->label('Departamento')
                    ->icon('heroicon-o-map')
                    ->weight('semibold')
                    ->searchable(),
                TextColumn::make('provincia')
                    ->label('Provincia')
                    ->searchable(),
                TextColumn::make('distrito')
                    ->label('Distrito')
                    ->icon('heroicon-o-map-pin')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('capital')
                    ->label('Capital')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region_natural')
                    ->label('Region natural')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
