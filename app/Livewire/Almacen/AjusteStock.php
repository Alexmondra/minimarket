<?php

namespace App\Livewire\Almacen;

use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\LotePresentacionMerma;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AjusteStock extends Component
{
    public bool $showModal = false;

    public string $tipoAjuste = 'entrada'; // 'entrada' o 'salida'

    // Form inputs
    public ?int $sucursalId = null;

    public ?int $productoId = null;

    public ?int $presentacionId = null;

    public int $cantidad = 1;

    public string $motivo = '';

    // Search and autocomplete
    public string $searchProducto = '';

    public array $productosResultados = [];

    public bool $showProductoDropdown = false;

    public array $presentaciones = [];

    // Output specific inputs
    public string $lotePresentacionId = 'fifo'; // 'fifo' or a specific ID

    public string $tipoMerma = 'roto'; // 'vencido', 'roto', 'robo', 'otro'

    public string $observacion = '';

    public array $lotesDisponibles = [];

    // Entry specific inputs
    public string $loteCodigo = '';

    public ?string $fechaVencimiento = null;

    public ?float $costo = null;

    public ?int $proveedorId = null;

    // Wizard and confirmation step
    public bool $showConfirmStep = false;

    public array $provisionalDistribution = [];

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
            'sucursalId', 'productoId', 'presentacionId', 'cantidad', 'motivo',
            'searchProducto', 'productosResultados', 'showProductoDropdown',
            'presentaciones', 'lotePresentacionId', 'tipoMerma', 'observacion',
            'lotesDisponibles', 'loteCodigo', 'fechaVencimiento', 'costo',
            'proveedorId', 'showConfirmStep', 'provisionalDistribution'
        ]);
        $this->cantidad = 1;
        $this->lotePresentacionId = 'fifo';
        $this->tipoMerma = 'roto';
    }

    public function updatedSucursalId(): void
    {
        $this->cargarLotesDisponibles();
    }

    public function getSucursalesProperty()
    {
        return Sucursal::where('activo', true)->get();
    }

    public function getProveedoresProperty()
    {
        return Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();
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
        $this->lotePresentacionId = 'fifo';
        $this->lotesDisponibles = [];

        // Load presentations
        $this->presentaciones = ProductoPresentacion::where('producto_id', $id)
            ->with('unidadMedida')
            ->get()
            ->toArray();
    }

    public function updatedPresentacionId(): void
    {
        $this->lotePresentacionId = 'fifo';
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

    public function calcularSalida(): void
    {
        $this->validate([
            'sucursalId' => 'required|exists:sucursales,id',
            'productoId' => 'required|exists:productos,id',
            'presentacionId' => 'required|exists:producto_presentacion,id',
            'cantidad' => 'required|integer|min:1',
            'tipoMerma' => 'required|string',
            'motivo' => 'required|string|max:500',
        ]);

        $requestedQty = $this->cantidad;
        $distribution = [];
        $remainingQty = $requestedQty;

        // 1. If manual lot is selected
        if ($this->lotePresentacionId && $this->lotePresentacionId !== 'fifo') {
            $selectedLot = LotePresentacion::with('lote')->find($this->lotePresentacionId);
            if ($selectedLot && $selectedLot->stock > 0) {
                $taken = min($selectedLot->stock, $remainingQty);
                $distribution[] = [
                    'lote_presentacion_id' => $selectedLot->id,
                    'codigo_lote' => $selectedLot->lote?->codigo_lote ?? 'Sin código',
                    'stock_actual' => $selectedLot->stock,
                    'cantidad_retirar' => $taken,
                    'is_manual' => true,
                ];
                $remainingQty -= $taken;
            }
        }

        // 2. Fallback to FIFO for remaining quantity
        if ($remainingQty > 0) {
            $fifoLotsQuery = LotePresentacion::query()
                ->join('lotes', 'lote_presentacion.lote_id', '=', 'lotes.id')
                ->where('lote_presentacion.producto_presentacion_id', $this->presentacionId)
                ->where('lotes.sucursal_id', $this->sucursalId)
                ->where('lote_presentacion.stock', '>', 0);

            if ($this->lotePresentacionId && $this->lotePresentacionId !== 'fifo') {
                $fifoLotsQuery->where('lote_presentacion.id', '!=', $this->lotePresentacionId);
            }

            $fifoLots = $fifoLotsQuery->orderByRaw('CASE WHEN lotes.fecha_vencimiento IS NULL THEN 1 ELSE 0 END, lotes.fecha_vencimiento ASC')
                ->orderBy('lotes.created_at', 'asc')
                ->select('lote_presentacion.*')
                ->with('lote')
                ->get();

            foreach ($fifoLots as $lot) {
                if ($remainingQty <= 0) {
                    break;
                }
                $taken = min($lot->stock, $remainingQty);
                $distribution[] = [
                    'lote_presentacion_id' => $lot->id,
                    'codigo_lote' => $lot->lote?->codigo_lote ?? 'Sin código',
                    'stock_actual' => $lot->stock,
                    'cantidad_retirar' => $taken,
                    'is_manual' => false,
                ];
                $remainingQty -= $taken;
            }
        }

        // Check if there was enough total stock
        if ($remainingQty > 0) {
            $totalStock = LotePresentacion::query()
                ->join('lotes', 'lote_presentacion.lote_id', '=', 'lotes.id')
                ->where('lote_presentacion.producto_presentacion_id', $this->presentacionId)
                ->where('lotes.sucursal_id', $this->sucursalId)
                ->sum('lote_presentacion.stock');

            $this->addError('cantidad', "La cantidad solicitada supera el stock disponible en los lotes (disponible total: {$totalStock} uds).");

            return;
        }

        $this->provisionalDistribution = $distribution;
        $this->showConfirmStep = true;
    }

    public function calcularEntrada(): void
    {
        $this->validate([
            'sucursalId' => 'required|exists:sucursales,id',
            'productoId' => 'required|exists:productos,id',
            'presentacionId' => 'required|exists:producto_presentacion,id',
            'cantidad' => 'required|integer|min:1',
            'loteCodigo' => 'required|string|max:100',
            'fechaVencimiento' => 'nullable|date',
            'costo' => 'nullable|numeric|min:0',
            'proveedorId' => 'nullable|exists:proveedores,id',
            'observacion' => 'nullable|string|max:500',
        ]);

        $this->showConfirmStep = true;
    }

    public function guardar(): void
    {
        // Guardar is called to trigger confirmation calculation or final save
        if ($this->tipoAjuste === 'salida') {
            if (! $this->showConfirmStep) {
                $this->calcularSalida();
            } else {
                $this->confirmarSalida();
            }
        } else {
            if (! $this->showConfirmStep) {
                $this->calcularEntrada();
            } else {
                $this->confirmarEntrada();
            }
        }
    }

    public function volverAtras(): void
    {
        $this->showConfirmStep = false;
    }

    public function confirmarSalida(): void
    {
        DB::transaction(function () {
            foreach ($this->provisionalDistribution as $item) {
                $lotePresentacion = LotePresentacion::with('lote', 'productoPresentacion.producto')
                    ->find($item['lote_presentacion_id']);

                if (! $lotePresentacion) {
                    continue;
                }

                $nuevoStock = $lotePresentacion->stock - $item['cantidad_retirar'];
                $lotePresentacion->update(['stock' => $nuevoStock]);

                // Register Movement
                MovimientoInventario::create([
                    'empresa_id' => Auth::user()->empresa_id ?? 1,
                    'sucursal_id' => $this->sucursalId,
                    'producto_nombre' => $lotePresentacion->productoPresentacion?->producto?->nombre ?? $this->searchProducto,
                    'producto_presentacion_id' => $this->presentacionId,
                    'tipo' => 'ajuste_salida',
                    'cantidad' => $item['cantidad_retirar'],
                    'motivo' => "Merma ({$this->tipoMerma}): " . $this->motivo,
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => $nuevoStock,
                ]);

                // Register Merma
                LotePresentacionMerma::create([
                    'lote_presentacion_id' => $lotePresentacion->id,
                    'cantidad' => $item['cantidad_retirar'],
                    'tipo_merma' => $this->tipoMerma,
                    'motivo' => $this->motivo . ($this->observacion ? ' - Obs: ' . $this->observacion : ''),
                    'user_id' => Auth::id(),
                ]);
            }
        });

        Notification::make()
            ->title('Ajuste de salida (Merma) registrado correctamente')
            ->success()
            ->send();

        $this->cerrarModal();
        $this->dispatch('ajusteGuardado');
    }

    public function confirmarEntrada(): void
    {
        DB::transaction(function () {
            $producto = Producto::find($this->productoId);
            $supplierNote = '';
            if ($this->proveedorId) {
                $supplier = Proveedor::find($this->proveedorId);
                if ($supplier) {
                    $supplierNote = ' - Proveedor: ' . $supplier->nombre;
                }
            }

            // 1. Find or create Lote
            $lote = Lote::where('sucursal_id', $this->sucursalId)
                ->where('codigo_lote', $this->loteCodigo)
                ->first();

            if (! $lote) {
                $lote = Lote::create([
                    'sucursal_id' => $this->sucursalId,
                    'codigo_lote' => $this->loteCodigo,
                    'producto_nombre' => $producto->nombre,
                    'fecha_fabricacion' => null,
                    'fecha_vencimiento' => $this->fechaVencimiento ?: null,
                    'precio_compra' => $this->costo ?: 0.00,
                    'observaciones' => $this->observacion . $supplierNote,
                    'estado_lote' => 'activo',
                ]);
            }

            // 2. Find or create LotePresentacion
            $lotePresentacion = LotePresentacion::where('lote_id', $lote->id)
                ->where('producto_presentacion_id', $this->presentacionId)
                ->first();

            if (! $lotePresentacion) {
                $lotePresentacion = LotePresentacion::create([
                    'lote_id' => $lote->id,
                    'producto_presentacion_id' => $this->presentacionId,
                    'stock_inicial' => $this->cantidad,
                    'stock' => $this->cantidad,
                    'precio_compra' => $this->costo ?: 0.00,
                    'estado' => LotePresentacion::ESTADO_ACTIVO,
                ]);
            } else {
                $lotePresentacion->increment('stock', $this->cantidad);
            }

            // 3. Find or create ProductoSucursal
            $productoSucursal = ProductoSucursal::where('sucursal_id', $this->sucursalId)
                ->where('lote_presentacion_id', $lotePresentacion->id)
                ->first();

            if (! $productoSucursal) {
                $precioVenta = $this->costo ? round($this->costo * 1.3, 2) : 1.00;
                $precioMayorista = $this->costo ? round($this->costo * 1.2, 2) : 0.90;

                ProductoSucursal::create([
                    'producto_id' => $this->productoId,
                    'sucursal_id' => $this->sucursalId,
                    'lote_presentacion_id' => $lotePresentacion->id,
                    'stock_minimo' => 5,
                    'precio' => $precioVenta,
                    'minimo_mayorista' => 12,
                    'precio_mayorista' => $precioMayorista,
                    'activo' => true,
                ]);
            }

            // 4. Register Movement
            MovimientoInventario::create([
                'empresa_id' => Auth::user()->empresa_id ?? 1,
                'sucursal_id' => $this->sucursalId,
                'producto_nombre' => $producto->nombre,
                'producto_presentacion_id' => $this->presentacionId,
                'tipo' => 'ajuste_entrada',
                'cantidad' => $this->cantidad,
                'motivo' => 'Ingreso manual' . ($this->observacion ? ': ' . $this->observacion : '') . $supplierNote,
                'referencia' => 'LotePresentacion:' . $lotePresentacion->id,
                'user_id' => Auth::id(),
                'stock_final' => $lotePresentacion->stock,
            ]);
        });

        Notification::make()
            ->title('Ajuste de entrada registrado con éxito')
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
