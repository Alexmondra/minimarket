<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Permisos\PermisoResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class EditRol extends EditRecord
{
    protected static string $resource = PermisoResource::class;

    protected static ?string $title = 'Editar Rol';

    protected static ?string $breadcrumb = 'Editar Rol';

    protected function resolveRecord($key): Model
    {
        return Role::findOrFail($key);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique('roles', 'name', ignoreRecord: true)
                    ->label('Nombre del Rol'),
                TextInput::make('guard_name')
                    ->default('web')
                    ->required()
                    ->maxLength(255)
                    ->label('Guard')
                    ->hidden(),
            ]);
    }
}
