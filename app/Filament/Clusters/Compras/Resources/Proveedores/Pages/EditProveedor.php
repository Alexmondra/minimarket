<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Pages;

use App\Filament\Clusters\Compras\Resources\Proveedores\ProveedorResource;
use App\Models\Proveedor;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class EditProveedor extends Page
{
    protected static string $resource = ProveedorResource::class;

    protected string $view = 'filament.clusters.compras.resources.proveedores.pages.edit-proveedor';

    public $recordId = null;
    public $nombre = '';
    public $tipo_documento = 'RUC';
    public $numero_documento = '';
    public $razon_social = '';
    public $direccion = '';
    public $telefono = '';
    public $email = '';
    public $contacto_principal = '';
    public $telefono_contacto = '';
    public $rubro = '';
    public $observaciones = '';
    public $estado = true;

    public function mount($record = null): void
    {
        if ($record) {
            $this->recordId = $record;
            $proveedor = Proveedor::where('empresa_id', auth()->user()->empresa_id)
                ->withTrashed()
                ->find($record);

            if (!$proveedor) {
                throw new ModelNotFoundException('Proveedor no encontrado.');
            }

            $this->nombre = $proveedor->nombre;
            $this->tipo_documento = $proveedor->tipo_documento ?? 'RUC';
            $this->numero_documento = $proveedor->numero_documento ?? '';
            $this->razon_social = $proveedor->razon_social ?? '';
            $this->direccion = $proveedor->direccion ?? '';
            $this->telefono = $proveedor->telefono ?? '';
            $this->email = $proveedor->email ?? '';
            $this->contacto_principal = $proveedor->contacto_principal ?? '';
            $this->telefono_contacto = $proveedor->telefono_contacto ?? '';
            $this->rubro = $proveedor->rubro ?? '';
            $this->observaciones = $proveedor->observaciones ?? '';
            $this->estado = (bool) $proveedor->estado;
        }
    }

    protected function rules(): array
    {
        return [
            'nombre' => 'required|max:255',
            'tipo_documento' => 'required|in:RUC,DNI,CE,OTRO',
            'numero_documento' => 'required|max:20',
            'razon_social' => 'nullable|max:255',
            'direccion' => 'nullable|max:255',
            'telefono' => 'nullable|max:20',
            'email' => 'nullable|email|max:255',
            'contacto_principal' => 'nullable|max:255',
            'telefono_contacto' => 'nullable|max:20',
            'rubro' => 'nullable|max:255',
            'observaciones' => 'nullable|max:65535',
            'estado' => 'boolean',
        ];
    }

    public function getRecord()
    {
        return Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->withTrashed()
            ->find($this->recordId);
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $proveedor = $this->getRecord();

            $proveedor->update([
                'nombre' => $this->nombre,
                'tipo_documento' => $this->tipo_documento,
                'numero_documento' => $this->numero_documento,
                'razon_social' => $this->razon_social,
                'direccion' => $this->direccion,
                'telefono' => $this->telefono,
                'email' => $this->email,
                'contacto_principal' => $this->contacto_principal,
                'telefono_contacto' => $this->telefono_contacto,
                'rubro' => $this->rubro,
                'observaciones' => $this->observaciones,
                'estado' => $this->estado,
            ]);

            Notification::make()
                ->title('Proveedor actualizado con éxito')
                ->success()
                ->send();

            $this->redirect(ProveedorResource::getUrl('index'));
        });
    }
}
