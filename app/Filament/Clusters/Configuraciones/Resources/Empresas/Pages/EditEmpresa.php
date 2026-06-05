<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Empresas\EmpresaResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

    public function getTitle(): string
    {
        return $this->isEditing ? 'Editar Mi Empresa' : '';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.clusters.configuraciones.resources.empresas.pages.view-empresa-master'),
        ]);
    }

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
        if (! auth()->user()?->can('config.ver')) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
    }

    protected function rules(): array
    {
        return [
            'data.ruc' => ['required', 'digits:11', Rule::unique('empresas', 'ruc')->ignore($this->record->id)],
            'data.razon_social' => ['required', 'string', 'max:255'],
            'data.direccion_fiscal' => ['nullable', 'string', 'max:255'],
            'data.entorno' => ['boolean'],
            'data.incluido_tributo' => ['boolean'],
            'data.logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'data.certificado' => ['nullable', 'file', 'mimes:pem,pfx,cer,crt,p12', 'max:5120'],
            'data.certificado_pass' => ['nullable', 'string', 'max:255'],
            'data.user_sol' => ['nullable', 'string', 'max:255'],
            'data.pass_sol' => ['nullable', 'string', 'max:255'],
            'data.sunat_client_id' => ['nullable', 'string', 'max:255'],
            'data.sunat_client_secret' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        if (! $this->canEdit()) {
            abort(403);
        }

        $this->validate();

        $data = $this->data;

        // Normalizar campos de archivo (Livewire puede dejarlos como array o TemporaryUploadedFile)
        foreach (['logo', 'certificado'] as $field) {
            if (! isset($data[$field])) {
                continue;
            }

            $value = $data[$field];

            if (is_array($value)) {
                $value = $value[0] ?? null;
            }

            if ($value instanceof TemporaryUploadedFile) {
                $disk = $field === 'logo' ? 'public' : 'local';
                $dir = $field === 'logo' ? 'empresas/logos' : 'empresas/certificados';
                $data[$field] = $value->store($dir, $disk);
            } elseif ($value === null) {
                unset($data[$field]);
            } else {
                $data[$field] = $value;
            }
        }

        $this->handleRecordUpdate($this->record, $data);
        $this->afterSave();
    }

    protected function getHeaderActions(): array
    {
        if (! $this->isEditing) {
            return [];
        }

        $actions = [];

        if ($this->canEdit()) {
            $actions[] = Action::make('toggleEditing')
                ->label('Volver a vista')
                ->color('gray')
                ->icon('heroicon-o-eye')
                ->action('toggleEditingMode')
                ->tooltip('Cambiar a modo vista (solo lectura)');
        }

        return $actions;
    }

    public function toggleEditingMode(): void
    {
        // Seguridad: solo permitir si tiene permiso de edición
        if (! $this->canEdit()) {
            return;
        }

        $this->isEditing = ! $this->isEditing;

        // Si desactiva edición, recargamos datos originales
        if (! $this->isEditing) {
            $this->fillForm();
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function cancelEditing(): void
    {
        // Seguridad: solo permitir si tiene permiso de edición
        if (! $this->canEdit()) {
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
        if (! $this->canEdit()) {
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

        // Detectar tipo de certificado automáticamente a partir de la extensión del archivo subido
        if (array_key_exists('certificado', $configData)) {
            if (blank($configData['certificado'])) {
                $configData['tipo_certificado'] = null;
            } else {
                $extension = pathinfo($configData['certificado'], PATHINFO_EXTENSION);
                $configData['tipo_certificado'] = strtoupper($extension ?: 'PEM');
            }
        }

        // Actualizar empresa
        $record->update($empresaData);

        // Actualizar o crear empresa_config (solo con campos que tengan valor)
        if (! empty($configData)) {
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
