<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Usuarios\UsuarioResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUsuario extends EditRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['empresa_id'] = auth()->user()?->empresa_id;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->button()
                ->color('info')
                ->extraAttributes(['class' => 'mm-table-action mm-table-action-info']),
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

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
