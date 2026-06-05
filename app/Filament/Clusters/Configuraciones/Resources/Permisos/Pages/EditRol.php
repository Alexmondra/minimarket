<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Permisos\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Permisos\PermisoResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
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
            DeleteAction::make()
                ->label('Eliminar rol')
                ->visible(fn (Role $record): bool => $record->users()->doesntExist())
                ->authorize(fn (Role $record): bool => $record->users()->doesntExist())
                ->button()
                ->extraAttributes(['class' => 'mm-table-action mm-table-action-danger']),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Editar perfil de acceso')
                    ->description('Actualiza el nombre visible del rol sin alterar sus permisos asignados.')
                    ->icon('heroicon-o-shield-check')
                    ->extraAttributes(['class' => 'mm-crud-card mm-crud-card-violet'])
                    ->schema([
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
                    ]),
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
