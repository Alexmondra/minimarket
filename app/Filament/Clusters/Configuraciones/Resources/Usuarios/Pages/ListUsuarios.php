<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Usuarios\UsuarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsuarios extends ListRecords
{
    protected static string $resource = UsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo usuario')
                ->icon('heroicon-o-user-plus')
                ->color('success')
                ->button()
                ->extraAttributes(['class' => 'mm-header-action mm-header-action-success']),
        ];
    }
}
