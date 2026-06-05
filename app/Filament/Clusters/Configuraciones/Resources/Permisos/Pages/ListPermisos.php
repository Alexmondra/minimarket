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
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->button()
                ->extraAttributes(['class' => 'mm-header-action mm-header-action-primary'])
                ->url(PermisoResource::getUrl('create-rol')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query()->withCount('users'))
            ->recordAction(null)
            ->recordUrl(null)
            ->searchPlaceholder('Buscar...')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre del Rol')
                    ->icon('heroicon-o-shield-check')
                    ->badge()
                    ->color('primary')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permisos')
                    ->icon('heroicon-o-key')
                    ->badge()
                    ->color('success'),
                TextColumn::make('users_count')
                    ->label('Usuarios')
                    ->icon('heroicon-o-users')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Sin usuarios' : (string) $state),
            ])

            ->recordActions([
                Action::make('manage-permisos')
                    ->label('Gestionar')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('primary')
                    ->button()
                    ->size('sm')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-primary'])
                    ->url(fn (Model $record): string => PermisoResource::getUrl('manage-permisos', ['roleId' => $record->id])),
                EditAction::make()
                    ->label('Editar Rol')
                    ->button()
                    ->size('sm')
                    ->color('warning')
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-warning'])
                    ->url(fn (Model $record): string => PermisoResource::getUrl('edit-rol', ['record' => $record])),
                Action::make('rolProtegido')
                    ->label('Protegido')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->button()
                    ->size('sm')
                    ->disabled()
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-muted'])
                    ->visible(fn (Role $record): bool => (int) ($record->users_count ?? $record->users()->count()) > 0),
                DeleteAction::make()
                    ->label('Eliminar Rol')
                    ->button()
                    ->size('sm')
                    ->authorize(fn (Role $record): bool => $record->users()->doesntExist())
                    ->visible(fn (Role $record): bool => (int) ($record->users_count ?? $record->users()->count()) === 0)
                    ->extraAttributes(['class' => 'mm-table-action mm-table-action-danger']),
            ]);
    }
}
