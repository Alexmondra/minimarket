<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Pages;

use App\Filament\Clusters\Compras\Resources\Proveedores\ProveedorResource;
use App\Models\Proveedor;
use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class CreateProveedor extends Page
{
    protected static string $resource = ProveedorResource::class;

    protected string $view = 'filament.clusters.compras.resources.proveedores.pages.create-proveedor';

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

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $proveedor = Proveedor::create([
                'empresa_id' => auth()->user()->empresa_id,
                'sucursal_id' => app(SucursalContext::class)->resolveSucursalForWrite(null),
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
                ->title('Proveedor creado con éxito')
                ->success()
                ->send();

            $this->redirect(ProveedorResource::getUrl('index'));
        });
    }
}
