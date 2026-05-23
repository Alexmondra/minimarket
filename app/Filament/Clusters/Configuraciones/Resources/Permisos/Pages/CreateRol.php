<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Permisos\PermisoResource;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class CreateRol extends CreateRecord
{
    protected static string $resource = PermisoResource::class;

    protected static ?string $title = 'Crear Rol';

    protected static ?string $breadcrumb = 'Crear Rol';

    public function getModel(): string
    {
        return Role::class;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique('roles', 'name')
                    ->label('Nombre del Rol'),
                TextInput::make('guard_name')
                    ->default('web')
                    ->required()
                    ->maxLength(255)
                    ->label('Guard')
                    ->hidden(),
            ]);
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Rol creado correctamente. Ahora puedes asignarle permisos desde "Gestionar Permisos".')
            ->success()
            ->send();
    }
}
