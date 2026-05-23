<?php

namespace App\Livewire\Compras\Components;

use App\Models\Proveedor;
use App\Models\Sucursal;
use Livewire\Component;

class CabeceraCompra extends Component
{
    public ?int $proveedorId = null;
    public ?int $sucursalId = null;
    public string $tipoComprobante = 'factura';
    public ?string $numeroFactura = null;
    public string $fechaRecepcion;
    public ?string $observaciones = null;

    // Búsqueda de proveedor
    public string $searchProveedor = '';
    public array $proveedoresResultados = [];
    public bool $showProveedorDropdown = false;
    public ?string $proveedorNombre = null;

    public function mount(): void
    {
        $this->fechaRecepcion = now()->format('Y-m-d');
    }

    public function updatedSearchProveedor(): void
    {
        if (strlen($this->searchProveedor) < 2) {
            $this->proveedoresResultados = [];
            $this->showProveedorDropdown = false;
            return;
        }

        $this->proveedoresResultados = Proveedor::where('estado', true)
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->searchProveedor}%")
                  ->orWhere('numero_documento', 'like', "%{$this->searchProveedor}%")
                  ->orWhere('razon_social', 'like', "%{$this->searchProveedor}%");
            })
            ->limit(10)
            ->get()
            ->toArray();

        $this->showProveedorDropdown = count($this->proveedoresResultados) > 0;
    }

    public function seleccionarProveedor(int $id, string $nombre): void
    {
        $this->proveedorId = $id;
        $this->proveedorNombre = $nombre;
        $this->searchProveedor = $nombre;
        $this->showProveedorDropdown = false;
        $this->dispatch('proveedorSeleccionado', proveedorId: $id);
    }

    public function getSucursalesProperty()
    {
        return Sucursal::where('activo', true)->get();
    }

    public function render()
    {
        return view('livewire.compras.components.cabecera-compra');
    }
}
