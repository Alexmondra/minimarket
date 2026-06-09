<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Escritorio;
use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MiPerfil extends EditProfile
{
    protected static ?string $title = 'Mi perfil';

    protected string $view = 'filament.pages.mi-perfil';

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    public static function getLabel(): string
    {
        return 'Mi perfil';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de mi cuenta')
                    ->description('Actualiza solo tu informacion basica. Roles y sucursales los gestiona el administrador.')
                    ->icon('heroicon-o-user-circle')
                    ->extraAttributes(['class' => 'mm-crud-card mm-crud-card-emerald'])
                    ->columns(3)
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Foto de perfil')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeMode('cover')
                            ->imageResizeTargetWidth('512')
                            ->imageResizeTargetHeight('512')
                            ->imagePreviewHeight('130')
                            ->directory('users/avatars')
                            ->disk('public')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->helperText('Se optimiza a formato cuadrado. Maximo 2 MB.')
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(2),
                    ]),
                Section::make('Cambiar contrasena')
                    ->description('Dejalo vacio si no quieres modificar tu contrasena.')
                    ->icon('heroicon-o-lock-closed')
                    ->extraAttributes(['class' => 'mm-crud-card mm-crud-card-violet'])
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Nueva contrasena')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->rule(Password::min(8)->mixedCase()->numbers())
                            ->same('passwordConfirmation')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                            ->autocomplete('new-password'),
                        TextInput::make('passwordConfirmation')
                            ->label('Confirmar contrasena')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->required(fn ($get): bool => filled($get('password')))
                            ->dehydrated(false)
                            ->autocomplete('new-password'),
                    ]),
            ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        unset($data['passwordConfirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $record->update($data);

        return $record;
    }

    protected function getRedirectUrl(): ?string
    {
        return Escritorio::getUrl();
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perfil actualizado correctamente';
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Guardar cambios')
                ->icon('heroicon-o-check-circle')
                ->submit('save')
                ->button()
                ->extraAttributes(['class' => 'mm-table-action mm-table-action-success']),
        ];
    }
}
