<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Usuarios\UsuarioResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUsuario extends ViewRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Enviar a eliminados')
                ->before(fn (User $record): bool => $record->update(['activo' => false]))
                ->button()
                ->extraAttributes(['class' => 'mm-table-action mm-table-action-danger']),
            RestoreAction::make()
                ->label('Restablecer usuario')
                ->after(fn (User $record): bool => $record->update(['activo' => true]))
                ->button()
                ->extraAttributes(['class' => 'mm-table-action mm-table-action-success']),
        ];
    }
}
