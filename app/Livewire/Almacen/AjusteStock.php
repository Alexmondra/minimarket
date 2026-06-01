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
use App\Support\SucursalContext;
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

    // Branch lock flag
    public bool $isSucursalLocked = false;

    public ?float $totalPagado = null;

    public int $totalStockDisponible = 0;

    protected $listeners = [
        'abrirAjusteEntrada' => 'abrirEntrada',
        'abrirAjusteSalida' => 'abrirSalida',
    ];

    public function abrirEntrada(): void
    {
        $this->resetForm();
        $this->tipoAjuste = 'entrada';
        $this->aplicarLogicaSucursal();
        $this->showModal = true;
    }

    public function abrirSalida(): void
    {
        $this->resetForm();
        $this->tipoAjuste = 'salida';
        $this->aplicarLogicaSucursal();
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
            'proveedorId', 'showConfirmStep', 'provisionalDistribution', 'isSucursalLocked',
            'totalPagado', 'totalStockDisponible'
        ]);
        $this->cantidad = 1;
        $this->lotePresentacionId = 'fifo';
        $this->tipoMerma = 'roto';
        $this->resetValidation();
        if (method_exists($this, 'resetErrorBag')) {
            $this->resetErrorBag();
        }
    }

    public function aplicarLogicaSucursal(): void
    {
        $context = app(SucursalContext::class);
        $user = auth()->user();
        $allowed = $context->allowedSucursales($user);
        $activeId = $context->activeSucursalId();

        if ($activeId) {
            $this->sucursalId = $activeId;
            $this->isSucursalLocked = true;
        } elseif ($allowed->count() === 1) {
            $this->sucursalId = $allowed->first()->id;
            $this->isSucursalLocked = true;
        } else {
            $this->sucursalId = null;
            $this->isSucursalLocked = false;
        }
    }

    public function updatedSucursalId(): void
    {
        $this->cargarLotesDisponibles();
    }

    public function updatedTotalPagado(): void
    {
        $this->calcularPrecioUnitario();
    }

    public function updatedCantidad(): void
    {
        if ($this->tipoAjuste === 'entrada') {
            $this->calcularPrecioUnitario();
        }
    }

    protected function calcularPrecioUnitario(): void
    {
        if ($this->cantidad > 0 && $this->totalPagado !== null && $this->totalPagado !== '') {
            $this->costo = round((float) $this->totalPagado / $this->cantidad, 4);
        } else {
            $this->costo = null;
        }
    }

    public function getSucursalesProperty()
    {
        $context = app(SucursalContext::class);
        return $context->allowedSucursales(auth()->user());
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
        $this->productoId = null;
        $this->presentacionId = null;
        $this->lotePresentacionId = 'fifo';
        $this->lotesDisponibles = [];

        if (strlen($this->searchProducto) < 2) {
            $this->productosResultados = [];
            $this->showProductoDropdown = false;
            return;
        }

        $termRaw = trim($this->searchProducto);
        $words = explode(' ', $termRaw);

        $this->productosResultados = ProductoPresentacion::query()
            ->join('productos', 'producto_presentacion.producto_id', '=', 'productos.id')
            ->leftJoin('unidades_medida', 'producto_presentacion.unidad_medida_id', '=', 'unidades_medida.id')
            ->where('productos.activo', true)
            ->where('productos.empresa_id', auth()->user()->empresa_id)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $word = '%' . $word . '%';
                    $q->where(function ($sub) use ($word) {
                        $sub->where('productos.nombre', 'like', $word)
                            ->orWhere('producto_presentacion.tipo_presentacion', 'like', $word)
                            ->orWhere('productos.codigo_interno', 'like', $word);
                    });
                }
            })
            ->select(
                'producto_presentacion.id as presentacion_id',
                'productos.id as producto_id',
                'productos.nombre as producto_nombre',
                'producto_presentacion.tipo_presentacion',
                'unidades_medida.abreviatura as unidad_medida_abr',
                'productos.codigo_interno'
            )
            ->limit(15)
            ->get()
            ->map(fn ($p) => [
                'presentacion_id' => $p->presentacion_id,
                'producto_id' => $p->producto_id,
                'producto_nombre' => $p->producto_nombre,
                'tipo_presentacion' => $p->tipo_presentacion,
                'unidad_medida_abr' => $p->unidad_medida_abr,
                'codigo_interno' => $p->codigo_interno,
            ])
            ->toArray();

        $this->showProductoDropdown = count($this->productosResultados) > 0;
    }

    public function seleccionarPresentacion(array $result): void
    {
        $this->productoId = $result['producto_id'];
        $this->presentacionId = $result['presentacion_id'];
        $this->searchProducto = $result['producto_nombre'] . ' — ' . $result['tipo_presentacion'] . ($result['unidad_medida_abr'] ? ' (' . $result['unidad_medida_abr'] . ')' : '');
        $this->showProductoDropdown = false;

        $this->lotePresentacionId = 'fifo';
        $this->cargarLotesDisponibles();
    }

    protected function cargarLotesDisponibles(): void
    {
        if (! $this->sucursalId || ! $this->presentacionId) {
            $this->lotesDisponibles = [];
            $this->totalStockDisponible = 0;

            return;
        }

        $lotes = LotePresentacion::query()
            ->where('producto_presentacion_id', $this->presentacionId)
            ->whereHas('lote', fn ($query) => $query->where('sucursal_id', $this->sucursalId))
            ->with('lote')
            ->get();

        $this->lotesDisponibles = $lotes->map(fn (LotePresentacion $lp): array => [
                'id' => $lp->id,
                'codigo_lote' => $lp->lote?->codigo_lote ?? 'Sin código',
                'stock' => $lp->stock,
                'vencimiento' => $lp->lote?->fecha_vencimiento?->format('d/m/Y'),
            ])
            ->toArray();

        $this->totalStockDisponible = $lotes->sum('stock');
    }

    public function calcularSalida(): void
    {
        $this->validate([
            'sucursalId' => 'required|exists:sucursales,id',
            'productoId' => 'required|exists:productos,id',
            'presentacionId' => 'required|exists:producto_presentacion,id',
            'cantidad' => 'required|integer|min:1',
            'tipoMerma' => 'required|string',
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($this->totalStockDisponible <= 0) {
            $this->addError('cantidad', 'Esta presentación no tiene stock disponible en esta sucursal.');
            return;
        }

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
            'totalPagado' => 'nullable|numeric|min:0',
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

                $motivoAjuste = filled($this->motivo) ? $this->motivo : 'Ajuste rápido de inventario';

                // Register Movement
                MovimientoInventario::create([
                    'empresa_id' => Auth::user()->empresa_id ?? 1,
                    'sucursal_id' => $this->sucursalId,
                    'producto_nombre' => $lotePresentacion->productoPresentacion?->producto?->nombre ?? $this->searchProducto,
                    'producto_presentacion_id' => $this->presentacionId,
                    'tipo' => 'ajuste_salida',
                    'cantidad' => $item['cantidad_retirar'],
                    'motivo' => "Merma ({$this->tipoMerma}): " . $motivoAjuste,
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => $nuevoStock,
                ]);

                // Register Merma
                LotePresentacionMerma::create([
                    'lote_presentacion_id' => $lotePresentacion->id,
                    'cantidad' => $item['cantidad_retirar'],
                    'tipo_merma' => $this->tipoMerma,
                    'motivo' => $motivoAjuste . ($this->observacion ? ' - Obs: ' . $this->observacion : ''),
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
                    'observaciones' => ($this->observacion ?: 'Ajuste de entrada manual') . $supplierNote,
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
                'motivo' => ($this->observacion ?: 'Ingreso manual') . $supplierNote,
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

    protected function messages(): array
    {
        return [
            'sucursalId.required' => 'La sucursal es obligatoria.',
            'sucursalId.exists' => 'La sucursal seleccionada no es válida.',
            'productoId.required' => 'Debe buscar y seleccionar un producto con su presentación.',
            'presentacionId.required' => 'Debe buscar y seleccionar un producto con su presentación.',
            'cantidad.required' => 'La cantidad es obligatoria.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser mayor o igual a 1.',
            'lotePresentacionId.required' => 'El lote es obligatorio.',
            'tipoMerma.required' => 'El tipo de merma es obligatorio.',
            'loteCodigo.required' => 'El código de lote es obligatorio.',
            'fechaVencimiento.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            'costo.numeric' => 'El costo debe ser un valor numérico.',
            'costo.min' => 'El costo no puede ser menor a 0.',
            'totalPagado.numeric' => 'El total pagado debe ser un valor numérico.',
            'totalPagado.min' => 'El total pagado no puede ser menor a 0.',
            'proveedorId.exists' => 'El proveedor seleccionado no es válido.',
        ];
    }

    public function render()
    {
        return view('livewire.almacen.ajuste-stock');
    }
}
