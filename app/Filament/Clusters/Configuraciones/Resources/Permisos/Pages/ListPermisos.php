<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Permisos\PermisoResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class ListPermisos extends ListRecords
{
    protected static string $resource = PermisoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo Rol')
                ->url(PermisoResource::getUrl('create-rol')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query())
            ->recordUrl(fn (Model $record): string => PermisoResource::getUrl('manage-permisos', ['roleId' => $record->id]))
            ->searchPlaceholder('Buscar...')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre del Rol')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permisos')
                    ->badge()
                    ->color('success'),
            ])

            ->recordActions([
                Action::make('manage-permisos')
                    ->label('Gestionar Permisos')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->url(fn (Model $record): string => PermisoResource::getUrl('manage-permisos', ['roleId' => $record->id])),
                EditAction::make()
                    ->label('Editar Rol')
                    ->url(fn (Model $record): string => PermisoResource::getUrl('edit-rol', ['record' => $record])),
                DeleteAction::make()
                    ->label('Eliminar Rol'),
            ]);
    }
}
