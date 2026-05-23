<?php

namespace App\Livewire\Compras\Components;

use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ModalCrearLote extends Component
{
    public bool $showModal = false;

    public ?int $sucursalId = null;
    public ?int $productoId = null;
    public ?int $productoPresentacionId = null;
    public string $codigoLote = '';
    public ?string $fechaFabricacion = null;
    public ?string $fechaVencimiento = null;
    public ?string $ubicacion = null;
    public ?float $precioCompra = null;
    public ?string $observaciones = null;

    public string $searchProducto = '';
    public array $productosResultados = [];
    public bool $showProductoDropdown = false;
    public ?string $productoNombre = null;

    protected $listeners = ['abrirModalCrearLote' => 'abrir'];

    public function abrir(?int $sucursalId = null): void
    {
        $this->resetForm();
        $this->sucursalId = $sucursalId;
        $this->showModal = true;
    }

    public function cerrar(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'productoId', 'productoPresentacionId', 'codigoLote',
            'fechaFabricacion', 'fechaVencimiento', 'ubicacion',
            'precioCompra', 'observaciones', 'searchProducto',
            'productosResultados', 'showProductoDropdown', 'productoNombre',
        ]);
    }

    public function updatedSearchProducto(): void
    {
        if (strlen($this->searchProducto) < 2) {
            $this->productosResultados = [];
            $this->showProductoDropdown = false;
            return;
        }

        $this->productosResultados = Producto::where('activo', true)
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->searchProducto}%")
                  ->orWhere('codigo_interno', 'like', "%{$this->searchProducto}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'codigo' => $p->codigo_interno,
                    'presentaciones' => $p->presentaciones->map(fn($pr) => [
                        'id' => $pr->id,
                        'nombre' => "{$pr->tipo_presentacion} x {$pr->cantidad} {$pr->unidadMedida?->abreviatura}",
                    ])->toArray(),
                ];
            })->toArray();

        $this->showProductoDropdown = count($this->productosResultados) > 0;
    }

    public function seleccionarProducto(int $id, string $nombre): void
    {
        $this->productoId = $id;
        $this->productoNombre = $nombre;
        $this->searchProducto = $nombre;
        $this->showProductoDropdown = false;
    }

    public function actualizarPresentacion(int $presentacionId): void
    {
        $this->productoPresentacionId = $presentacionId;
    }

    public function crearLote(): void
    {
        $this->validate([
            'sucursalId' => 'required',
            'productoId' => 'required|exists:productos,id',
            'productoPresentacionId' => 'required|exists:producto_presentacion,id',
            'codigoLote' => 'required|string|max:255',
            'fechaFabricacion' => 'nullable|date',
            'fechaVencimiento' => 'nullable|date|after:fechaFabricacion',
            'ubicacion' => 'nullable|string|max:255',
            'precioCompra' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $producto = Producto::find($this->productoId);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursalId,
            'producto_nombre' => $producto->nombre,
            'codigo_lote' => $this->codigoLote,
            'fecha_fabricacion' => $this->fechaFabricacion,
            'fecha_vencimiento' => $this->fechaVencimiento,
            'ubicacion' => $this->ubicacion,
            'precio_compra' => $this->precioCompra,
            'observaciones' => $this->observaciones,
            'estado_lote' => 'activo',
        ]);

        LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $this->productoPresentacionId,
            'stock' => 0,
        ]);

        Notification::make()
            ->title("Lote {$this->codigoLote} creado correctamente")
            ->success()
            ->send();

        $this->dispatch('loteCreado', loteId: $lote->id);
        $this->cerrar();
    }

    public function render()
    {
        return view('livewire.compras.components.modal-crear-lote');
    }
}
