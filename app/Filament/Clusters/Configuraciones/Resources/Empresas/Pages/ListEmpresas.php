<?php

namespace App\Filament\Clusters\Configuraciones\Resources\Empresas\Pages;

use App\Filament\Clusters\Configuraciones\Resources\Empresas\EmpresaResource;
use Filament\Resources\Pages\ListRecords;

class ListEmpresas extends ListRecords
{
    protected static string $resource = EmpresaResource::class;

    public function mount(): void
    {
        parent::mount();

        // Redirigir automáticamente a la página de edición de la empresa del usuario
        $empresaId = auth()->user()->empresa_id;

        if ($empresaId) {
            $this->redirect(EmpresaResource::getUrl('edit', ['record' => $empresaId]));
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
