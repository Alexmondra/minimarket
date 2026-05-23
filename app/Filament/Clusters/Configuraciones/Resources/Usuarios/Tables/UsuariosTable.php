<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class UsuariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Nombre'),
                TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),
                TextColumn::make('telefono')
                    ->searchable()
                    ->label('Teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),
                ViewColumn::make('sucursales')
                    ->label('Sucursales')
                    ->view('filament.tables.columns.sucursales-badge'),
                ViewColumn::make('roles')
                    ->label('Roles')
                    ->view('filament.tables.columns.roles-badge'),
                IconColumn::make('activo')
                    ->boolean()
                    ->label('Activo'),
                TextColumn::make('ultimo_acceso')
                    ->dateTime()
                    ->label('Último acceso')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Creado')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Eliminado')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->options(fn () => Role::query()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('roles', fn ($q) => $q->where('id', $data['value']));
                        }
                    }),
                TrashedFilter::make()->label('Eliminados'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activar')
                        ->label('Activar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['activo' => true]))
                        ->requiresConfirmation(),
                    BulkAction::make('desactivar')
                        ->label('Desactivar')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['activo' => false]))
                        ->requiresConfirmation(),
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionados'),
                    RestoreBulkAction::make()
                        ->label('Restaurar seleccionados'),
                ]),
            ]);
    }
}
