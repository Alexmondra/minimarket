<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Empresas\EmpresaResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEmpresa extends EditRecord
{
    protected static string $resource = EmpresaResource::class;


    protected array $sensitiveFields = [
        'certificado_pass',
        'pass_sol',
        'sunat_client_secret',
    ];

    /**
     * Controla el modo vista (false) / edición (true).
     */
    public bool $isEditing = false;

    /**
     * Verifica si el usuario tiene permiso de editar configuración.
     */
    public function canEdit(): bool
    {
        return auth()->user()?->can('config.editar') ?? false;
    }

    public function mount(int|string $record): void
    {
        parent::mount($record);

        // Si el usuario no tiene permiso de ver configuración, redirigir
        if (!auth()->user()?->can('config.ver')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        // Solo mostrar el toggle si el usuario tiene permiso de edición
        if ($this->canEdit()) {
            $actions[] = Action::make('toggleEditing')
                ->label(fn (): string => $this->isEditing
                    ? '🔍 Modo Vista'
                    : '✏️ Modo Edición')
                ->color(fn (): string => $this->isEditing ? 'gray' : 'warning')
                ->icon(fn (): string => $this->isEditing
                    ? 'heroicon-o-eye'
                    : 'heroicon-o-pencil-square')
                ->action('toggleEditingMode')
                ->tooltip(fn (): string => $this->isEditing
                    ? 'Cambiar a modo vista (solo lectura)'
                    : 'Habilitar campos para modificar los datos de la empresa');
        }

        return $actions;
    }

    public function toggleEditingMode(): void
    {
        // Seguridad: solo permitir si tiene permiso de edición
        if (!$this->canEdit()) {
            return;
        }

        $this->isEditing = !$this->isEditing;

        // Si desactiva edición, recargamos datos originales
        if (!$this->isEditing) {
            $this->fillForm();
        }
    }

    protected function getFormActions(): array
    {
        // Solo mostrar botones de guardar/cancelar si el usuario tiene permiso
        if (!$this->isEditing || !$this->canEdit()) {
            return [];
        }

        return [
            Action::make('save')
                ->label('💾 Guardar cambios')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action('save'),
            Action::make('cancel')
                ->label('Cancelar')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->action('cancelEditing'),
        ];
    }

    public function cancelEditing(): void
    {
        // Seguridad: solo permitir si tiene permiso de edición
        if (!$this->canEdit()) {
            return;
        }

        $this->isEditing = false;
        $this->fillForm();
    }

    protected function afterSave(): void
    {
        $this->isEditing = false;

        Notification::make()
            ->title('Empresa actualizada exitosamente')
            ->success()
            ->send();
    }

    /**
     * Evita redirección después de guardar.
     */
    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Seguridad: verificar permiso antes de guardar
        if (!$this->canEdit()) {
            abort(403, 'No tienes permiso para editar la configuración.');
        }

        // Separar datos de empresa_config
        $configFields = [
            'tipo_certificado',
            'certificado',
            'certificado_pass',
            'user_sol',
            'pass_sol',
            'sunat_client_id',
            'sunat_client_secret',
        ];

        $empresaData = collect($data)->except($configFields)->toArray();
        $configData = collect($data)->only($configFields)->toArray();

        // Limpiar campos sensibles vacíos para no sobrescribir valores existentes
        foreach ($this->sensitiveFields as $field) {
            if (array_key_exists($field, $configData) && blank($configData[$field])) {
                unset($configData[$field]);
            }
        }

        // Actualizar empresa
        $record->update($empresaData);

        // Actualizar o crear empresa_config (solo con campos que tengan valor)
        if (!empty($configData)) {
            $record->empresaConfig()->updateOrCreate(
                ['empresa_id' => $record->id],
                $configData
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar datos de empresa_config si existen
        $config = $this->record->empresaConfig;

        if ($config) {
            $data = array_merge($data, $config->toArray());
        }

        // NUNCA pre-rellenar campos sensibles en modo edición
        // Los placeholders "Dejar vacío si no desea cambiarlo" serán suficientes
        foreach ($this->sensitiveFields as $field) {
            $data[$field] = '';
        }

        return $data;
    }
}
