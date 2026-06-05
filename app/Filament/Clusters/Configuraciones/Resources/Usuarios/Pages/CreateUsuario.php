<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Usuarios\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Usuarios\UsuarioResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUsuario extends CreateRecord
{
    protected static string $resource = UsuarioResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()?->empresa_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
