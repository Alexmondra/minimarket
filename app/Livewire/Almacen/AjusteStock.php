<?php

namespace App\Livewire\Almacen;

use App\Models\LotePresentacion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AjusteStock extends Component
{
    public bool $showModal = false;

    public string $tipoAjuste = 'entrada'; // 'entrada' o 'salida'

    // Datos del formulario
    public ?int $sucursalId = null;

    public ?int $productoId = null;

    public ?int $presentacionId = null;

    public ?int $lotePresentacionId = null;

    public int $cantidad = 1;

    public string $motivo = '';

    // Búsqueda de producto
    public string $searchProducto = '';

    public array $productosResultados = [];

    public bool $showProductoDropdown = false;

    // Presentaciones del producto seleccionado
    public array $presentaciones = [];

    public array $lotesDisponibles = [];

    protected function rules()
    {
        return [
            'sucursalId' => 'required|exists:sucursales,id',
            'productoId' => 'required|exists:productos,id',
            'presentacionId' => 'required|exists:producto_presentacion,id',
            'lotePresentacionId' => 'required|exists:lote_presentacion,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:500',
        ];
    }

    protected $listeners = [
        'abrirAjusteEntrada' => 'abrirEntrada',
        'abrirAjusteSalida' => 'abrirSalida',
    ];

    public function abrirEntrada(): void
    {
        $this->resetForm();
        $this->tipoAjuste = 'entrada';
        $this->showModal = true;
    }

    public function abrirSalida(): void
    {
        $this->resetForm();
        $this->tipoAjuste = 'salida';
        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'sucursalId', 'productoId', 'presentacionId', 'lotePresentacionId',
            'cantidad', 'motivo', 'searchProducto', 'productosResultados',
            'showProductoDropdown', 'presentaciones', 'lotesDisponibles',
        ]);
        $this->cantidad = 1;
    }

    public function updatedSucursalId(): void
    {
        $this->cargarLotesDisponibles();
    }

    public function getSucursalesProperty()
    {
        return Sucursal::where('activo', true)->get();
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
            ->toArray();

        $this->showProductoDropdown = count($this->productosResultados) > 0;
    }

    public function seleccionarProducto(int $id, string $nombre): void
    {
        $this->productoId = $id;
        $this->searchProducto = $nombre;
        $this->showProductoDropdown = false;
        $this->presentacionId = null;
        $this->lotePresentacionId = null;
        $this->lotesDisponibles = [];

        // Cargar presentaciones del producto
        $this->presentaciones = ProductoPresentacion::where('producto_id', $id)
            ->with('unidadMedida')
            ->get()
            ->toArray();
    }

    public function updatedPresentacionId(): void
    {
        $this->lotePresentacionId = null;
        $this->cargarLotesDisponibles();
    }

    protected function cargarLotesDisponibles(): void
    {
        if (! $this->sucursalId || ! $this->presentacionId) {
            $this->lotesDisponibles = [];

            return;
        }

        $this->lotesDisponibles = LotePresentacion::query()
            ->where('producto_presentacion_id', $this->presentacionId)
            ->whereHas('lote', fn ($query) => $query->where('sucursal_id', $this->sucursalId))
            ->with('lote')
            ->get()
            ->map(fn (LotePresentacion $lp): array => [
                'id' => $lp->id,
                'codigo_lote' => $lp->lote?->codigo_lote ?? 'Sin código',
                'stock' => $lp->stock,
                'vencimiento' => $lp->lote?->fecha_vencimiento?->format('d/m/Y'),
            ])
            ->toArray();
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            $lotePresentacion = LotePresentacion::with('lote', 'productoPresentacion.producto')
                ->find($this->lotePresentacionId);

            if (! $lotePresentacion || $lotePresentacion->lote?->sucursal_id !== $this->sucursalId) {
                Notification::make()
                    ->title('Error: El lote seleccionado no pertenece a esta sucursal')
                    ->danger()
                    ->send();

                return;
            }

            if ($this->tipoAjuste === 'salida') {
                if ($lotePresentacion->stock < $this->cantidad) {
                    Notification::make()
                        ->title("Error: Stock insuficiente en el lote {$lotePresentacion->lote?->codigo_lote}. Stock actual: {$lotePresentacion->stock}")
                        ->danger()
                        ->send();

                    return;
                }

                $nuevoStock = $lotePresentacion->stock - $this->cantidad;
                $lotePresentacion->update(['stock' => $nuevoStock]);

                // Registrar movimiento
                MovimientoInventario::create([
                    'empresa_id' => Auth::user()->empresa_id ?? 1,
                    'sucursal_id' => $this->sucursalId,
                    'producto_nombre' => $lotePresentacion->productoPresentacion?->producto?->nombre ?? $this->searchProducto,
                    'producto_presentacion_id' => $this->presentacionId,
                    'tipo' => 'ajuste_salida',
                    'cantidad' => $this->cantidad,
                    'motivo' => $this->motivo,
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => $nuevoStock,
                ]);
            } else {
                $nuevoStock = $lotePresentacion->stock + $this->cantidad;
                $lotePresentacion->update(['stock' => $nuevoStock]);

                // Registrar movimiento
                MovimientoInventario::create([
                    'empresa_id' => Auth::user()->empresa_id ?? 1,
                    'sucursal_id' => $this->sucursalId,
                    'producto_nombre' => $lotePresentacion->productoPresentacion?->producto?->nombre ?? $this->searchProducto,
                    'producto_presentacion_id' => $this->presentacionId,
                    'tipo' => 'ajuste_entrada',
                    'cantidad' => $this->cantidad,
                    'motivo' => $this->motivo,
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => $nuevoStock,
                ]);
            }
        });

        Notification::make()
            ->title('Ajuste de '.($this->tipoAjuste === 'entrada' ? 'entrada' : 'salida').' registrado correctamente')
            ->success()
            ->send();

        $this->cerrarModal();
        $this->dispatch('ajusteGuardado');
    }

    public function render()
    {
        return view('livewire.almacen.ajuste-stock');
    }
}
