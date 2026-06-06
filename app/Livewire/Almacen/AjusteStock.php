<?php

namespace App\Livewire\Almacen;

use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\LotePresentacionMerma;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Proveedor;
use App\Models\ProductoPresentacionBarra;
use App\Models\Sucursal;
use App\Models\UniMedida;
use Filament\Notifications\Notification;
use App\Support\SucursalContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class AjusteStock extends Component
{
    use WithFileUploads;
    public bool $showModal = false;

    public string $tipoAjuste = 'entrada'; // 'entrada' o 'salida'

    // Form inputs
    public ?int $sucursalId = null;

    public ?int $productoId = null;

    public ?int $presentacionId = null;

    public $cantidad = 1;

    public string $motivo = '';

    // Search and autocomplete
    public string $searchProducto = '';

    public array $productosResultados = [];

    public bool $showProductoDropdown = false;

    public array $presentaciones = [];

    public ?string $ultimaBusquedaSinResultado = null;

    // Product creation modal
    public bool $showCrearProductoModal = false;

    public string $modoProductoPresentacion = 'nuevo';

    public string $modalSearchProducto = '';

    public array $modalProductosResultados = [];

    public bool $modalShowProductoDropdown = false;

    public ?int $modalProductoId = null;

    public ?string $modalProductoNombre = null;

    public ?string $modalNuevoProductoNombre = null;

    public ?string $modalCodigoInterno = null;

    public ?int $modalCategoriaId = null;

    public ?int $modalMarcaId = null;

    public bool $modalAfectoIgv = true;

    public ?int $modalUnidadMedidaId = null;

    public $modalCantidadPorEmpaque = 1;

    public ?string $modalTipoPresentacion = null;

    public ?string $modalCodigoBarra = null;

    public bool $modalEsPesable = false;

    public ?int $modalPresentacionBaseId = null;

    public $modalImagen = null;

    public ?int $modalEditingPresentationId = null;

    public bool $showModalPresentacionDropdown = false;

    public array $modalPresentacionesResultados = [];

    public array $modalBarras = [];

    public ?string $modalNuevoCodigoBarra = null;

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
        'productoCreado' => 'setProducto',
    ];

    public function updatedModalCantidadPorEmpaque(): void
    {
        if ($this->modalCantidadPorEmpaque <= 1) {
            $this->modalPresentacionBaseId = null;
        }
    }

    public function abrirEntrada(?int $sucursalId = null): void
    {
        $this->resetForm();
        $this->tipoAjuste = 'entrada';
        $this->aplicarLogicaSucursal($sucursalId);
        $this->showModal = true;
    }

    public function abrirSalida(?int $sucursalId = null): void
    {
        $this->resetForm();
        $this->tipoAjuste = 'salida';
        $this->aplicarLogicaSucursal($sucursalId);
        $this->showModal = true;
    }

    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->showCrearProductoModal = false;
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

    public function aplicarLogicaSucursal(?int $passedSucursalId = null): void
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
            if ($passedSucursalId && $context->canAccessSucursal($passedSucursalId, $user)) {
                $this->sucursalId = $passedSucursalId;
            } else {
                $this->sucursalId = null;
            }
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

        $this->productosResultados = ProductoPresentacion::query()
            ->with('unidadMedida', 'barras')
            ->whereHas('producto', fn ($q) => $q->where('activo', true)->where('empresa_id', auth()->user()->empresa_id))
            ->where(function ($q) use ($termRaw) {
                $q->whereHas('barras', fn ($b) => $b->where('codigo_barra', 'like', "%{$termRaw}%"))
                    ->orWhere('tipo_presentacion', 'like', "%{$termRaw}%")
                    ->orWhereHas('producto', function ($pq) use ($termRaw) {
                        $pq->where('nombre', 'like', "%{$termRaw}%")
                            ->orWhere('codigo_interno', 'like', "%{$termRaw}%");
                    });
            })
            ->limit(10)
            ->get()
            ->sortByDesc(fn (ProductoPresentacion $p) => $p->barras->pluck('codigo_barra')->contains($termRaw))
            ->map(fn (ProductoPresentacion $p) => [
                'presentacion_id' => $p->id,
                'producto_id' => $p->producto_id,
                'producto_nombre' => $p->producto?->nombre ?? '',
                'tipo_presentacion' => $p->tipo_presentacion ?? '',
                'unidad_medida_abr' => $p->unidadMedida?->abreviatura ?? '',
                'codigo_interno' => $p->producto?->codigo_interno ?? '',
            ])
            ->values()
            ->toArray();

        $this->showProductoDropdown = count($this->productosResultados) > 0;

        if (! $this->showProductoDropdown && ctype_digit($termRaw) && strlen($termRaw) >= 5 && $this->ultimaBusquedaSinResultado !== $termRaw) {
            $this->abrirCrearProductoModal($termRaw);
        }
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

    public function setProducto(int $productoId): void
    {
        $producto = Producto::find($productoId);
        if (! $producto) {
            return;
        }
        $this->productoId = $producto->id;
        $presentacion = $producto->presentaciones()->first();
        if ($presentacion) {
            $this->presentacionId = $presentacion->id;
            $this->searchProducto = $producto->nombre . ' — ' . ($presentacion->tipo_presentacion ?? 'Presentación');
            $this->cargarLotesDisponibles();
        }
        $this->showProductoDropdown = false;
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
            'cantidad' => 'required|integer|min:1|max:999999999',
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
            'cantidad' => 'required|integer|min:1|max:999999999',
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

    public function abrirCrearProductoModal(?string $codigoBarra = null): void
    {
        $codigoBarra = trim((string) ($codigoBarra ?: $this->searchProducto));
        $this->ultimaBusquedaSinResultado = $codigoBarra;

        $selectedProductId = $this->productoId;

        $this->resetCrearProductoModal();

        if ($selectedProductId) {
            $this->modoProductoPresentacion = 'existente';
            $this->modalProductoId = $selectedProductId;
            $this->modalSearchProducto = $this->searchProducto;
            $this->modalCodigoBarra = ctype_digit($codigoBarra) ? $codigoBarra : null;
        } else {
            if (ctype_digit($codigoBarra)) {
                $this->modalCodigoBarra = $codigoBarra;
                $this->modalSearchProducto = '';
                $this->modoProductoPresentacion = 'nuevo';
            } else {
                $this->modalCodigoBarra = null;
                $this->modalSearchProducto = $codigoBarra;
                $this->modalNuevoProductoNombre = $codigoBarra;
                $this->modoProductoPresentacion = 'nuevo';
            }
        }

        $this->showCrearProductoModal = true;
    }

    public function cerrarCrearProductoModal(): void
    {
        $this->showCrearProductoModal = false;
        $this->resetCrearProductoModal();
    }

    protected function resetCrearProductoModal(): void
    {
        $this->reset([
            'modalSearchProducto', 'modalProductosResultados', 'modalShowProductoDropdown',
            'modalProductoId', 'modalProductoNombre', 'modalNuevoProductoNombre',
            'modalCodigoInterno', 'modalCategoriaId', 'modalMarcaId',
            'modalUnidadMedidaId', 'modalTipoPresentacion', 'modalCodigoBarra',
            'modalEsPesable', 'modalPresentacionBaseId', 'modalImagen',
            'modalEditingPresentationId', 'showModalPresentacionDropdown',
            'modalPresentacionesResultados', 'modalBarras', 'modalNuevoCodigoBarra',
        ]);
        $this->modoProductoPresentacion = 'existente';
        $this->modalAfectoIgv = true;
        $this->modalCantidadPorEmpaque = 1;
        $this->modalUnidadMedidaId = UniMedida::where('abreviatura', 'und')->value('id');
    }

    public function updatedModoProductoPresentacion(): void
    {
        $this->modalPresentacionBaseId = null;
    }

    public function updatedModalTipoPresentacion(): void
    {
        if ($this->modoProductoPresentacion === 'nuevo' || !$this->modalProductoId) {
            $this->modalPresentacionesResultados = [];
            $this->showModalPresentacionDropdown = false;
            return;
        }

        if ($this->modalEditingPresentationId) {
            $p = ProductoPresentacion::find($this->modalEditingPresentationId);
            if ($p && $p->tipo_presentacion !== $this->modalTipoPresentacion) {
                $this->modalEditingPresentationId = null;
            }
        }

        $term = trim((string) $this->modalTipoPresentacion);
        if (strlen($term) < 1) {
            $this->modalPresentacionesResultados = [];
            $this->showModalPresentacionDropdown = false;
            return;
        }

        $this->modalPresentacionesResultados = ProductoPresentacion::query()
            ->with('unidadMedida')
            ->where('producto_id', $this->modalProductoId)
            ->where('tipo_presentacion', 'like', "%{$term}%")
            ->limit(8)
            ->get()
            ->map(fn ($p): array => [
                'id' => $p->id,
                'tipo_presentacion' => $p->tipo_presentacion,
                'cantidad' => $p->cantidad,
                'unidad_medida_id' => $p->unidad_medida_id,
                'unidad_medida_nombre' => $p->unidadMedida?->nombre,
                'unidad_medida_abreviatura' => $p->unidadMedida?->abreviatura,
                'es_pesable' => (bool) $p->es_pesable,
                'presentacion_base_id' => $p->presentacion_base_id,
            ])
            ->toArray();

        $this->showModalPresentacionDropdown = count($this->modalPresentacionesResultados) > 0;
    }

    public function seleccionarPresentacionDesdeModal(int $id): void
    {
        $p = ProductoPresentacion::findOrFail($id);
        $this->modalEditingPresentationId = $p->id;
        $this->modalTipoPresentacion = $p->tipo_presentacion;
        $this->modalUnidadMedidaId = $p->unidad_medida_id;
        $this->modalCantidadPorEmpaque = $p->cantidad;
        $this->modalEsPesable = (bool) $p->es_pesable;
        $this->modalPresentacionBaseId = $p->presentacion_base_id;
        $this->showModalPresentacionDropdown = false;
    }

    public function agregarCodigoBarraDesdeModal(): void
    {
        $this->modalNuevoCodigoBarra = trim((string) $this->modalNuevoCodigoBarra);
        if (blank($this->modalNuevoCodigoBarra)) {
            return;
        }

        if (in_array($this->modalNuevoCodigoBarra, $this->modalBarras) || $this->modalNuevoCodigoBarra === $this->modalCodigoBarra) {
            $this->addError('modalNuevoCodigoBarra', 'Este código ya está agregado.');
            return;
        }

        $exists = ProductoPresentacionBarra::query()
            ->where('codigo_barra', $this->modalNuevoCodigoBarra)
            ->when($this->modalEditingPresentationId, function ($query) {
                $query->where('producto_presentacion_id', '!=', $this->modalEditingPresentationId);
            })
            ->exists();

        if ($exists) {
            $this->addError('modalNuevoCodigoBarra', 'Este código de barra ya está asignado a otra presentación.');
            return;
        }

        $this->modalBarras[] = $this->modalNuevoCodigoBarra;
        $this->modalNuevoCodigoBarra = '';
        $this->resetErrorBag('modalNuevoCodigoBarra');
    }

    public function removerCodigoBarraDesdeModal(int $index): void
    {
        if (isset($this->modalBarras[$index])) {
            unset($this->modalBarras[$index]);
            $this->modalBarras = array_values($this->modalBarras);
        }
    }

    public function getBasePresentacionesProperty()
    {
        if ($this->modoProductoPresentacion === 'nuevo' || !$this->modalProductoId) {
            return collect();
        }

        return ProductoPresentacion::query()
            ->with('unidadMedida')
            ->where('producto_id', $this->modalProductoId)
            ->get();
    }

    public function updatedModalSearchProducto(): void
    {
        if (strlen($this->modalSearchProducto) < 2) {
            $this->modalProductosResultados = [];
            $this->modalShowProductoDropdown = false;
            return;
        }

        $this->modalProductosResultados = Producto::query()
            ->where('activo', true)
            ->where('empresa_id', Auth::user()?->empresa_id)
            ->where(function ($query) {
                $query->where('nombre', 'like', "%{$this->modalSearchProducto}%")
                    ->orWhere('codigo_interno', 'like', "%{$this->modalSearchProducto}%");
            })
            ->limit(8)
            ->get()
            ->map(fn (Producto $producto): array => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $producto->codigo_interno,
            ])
            ->toArray();

        $this->modalShowProductoDropdown = count($this->modalProductosResultados) > 0;
    }

    public function seleccionarProductoParaPresentacion(int $id, string $nombre): void
    {
        $this->modalProductoId = $id;
        $this->modalProductoNombre = $nombre;
        $this->modalSearchProducto = $nombre;
        $this->modalShowProductoDropdown = false;
        $this->modalPresentacionBaseId = null;
    }

    public function crearProductoYPresentacion(): void
    {
        if (filled($this->modalNuevoCodigoBarra)) {
            $this->agregarCodigoBarraDesdeModal();
            if ($this->getErrorBag()->has('modalNuevoCodigoBarra')) {
                return;
            }
        }

        if ($this->modalCantidadPorEmpaque <= 1 || $this->modoProductoPresentacion === 'nuevo') {
            $this->modalPresentacionBaseId = null;
        }

        $rules = [
            'modalUnidadMedidaId' => 'required|exists:unidades_medida,id',
            'modalCantidadPorEmpaque' => 'required|integer|min:1',
            'modalTipoPresentacion' => 'required|string|max:255',
            'modalCodigoBarra' => [
                'nullable', 'string', 'max:255',
                Rule::unique('producto_presentacion_barras', 'codigo_barra')
                    ->ignore($this->modalEditingPresentationId, 'producto_presentacion_id'),
            ],
            'modalImagen' => 'nullable|image|max:2048',
        ];

        if ($this->modoProductoPresentacion === 'nuevo') {
            $rules['modalNuevoProductoNombre'] = 'required|string|max:255';
            $rules['modalCodigoInterno'] = 'nullable|string|max:100';
            $rules['modalCategoriaId'] = 'nullable|exists:categorias,id';
            $rules['modalMarcaId'] = 'nullable|exists:marcas,id';
        } else {
            $rules['modalProductoId'] = 'required|exists:productos,id';
            $rules['modalPresentacionBaseId'] = [
                'nullable',
                Rule::exists('producto_presentacion', 'id')->where(function ($query) {
                    $query->where('producto_id', $this->modalProductoId);
                }),
            ];
        }

        $this->validate($rules, [
            'modalCodigoBarra.unique' => 'Este código de barra ya está registrado en otra presentación.',
            'modalPresentacionBaseId.exists' => 'La presentación base seleccionada no es válida para este producto.',
            'modalImagen.image' => 'El archivo debe ser una imagen válida.',
            'modalImagen.max' => 'La imagen no debe pesar más de 2MB.',
        ]);

        $isEdit = (bool) $this->modalEditingPresentationId;

        $presentacion = DB::transaction(function () use ($isEdit): ProductoPresentacion {
            $producto = $this->modoProductoPresentacion === 'nuevo'
                ? Producto::create([
                    'empresa_id' => Auth::user()?->empresa_id ?? 1,
                    'categoria_id' => $this->modalCategoriaId,
                    'marca_id' => $this->modalMarcaId,
                    'codigo_interno' => $this->modalCodigoInterno ?: 'PROD-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                    'nombre' => $this->modalNuevoProductoNombre,
                    'slug' => $this->generarSlugUnico($this->modalNuevoProductoNombre),
                    'afecto_igv' => $this->modalAfectoIgv,
                    'activo' => true,
                ])
                : Producto::query()
                    ->where('empresa_id', Auth::user()?->empresa_id)
                    ->findOrFail($this->modalProductoId);

            if ($isEdit) {
                $pres = ProductoPresentacion::findOrFail($this->modalEditingPresentationId);
                $pres->update([
                    'unidad_medida_id' => $this->modalUnidadMedidaId,
                    'cantidad' => $this->modalCantidadPorEmpaque,
                    'tipo_presentacion' => $this->modalTipoPresentacion,
                    'es_pesable' => $this->modalEsPesable,
                    'presentacion_base_id' => $this->modalPresentacionBaseId,
                ]);
            } else {
                $pres = ProductoPresentacion::create([
                    'producto_id' => $producto->id,
                    'unidad_medida_id' => $this->modalUnidadMedidaId,
                    'cantidad' => $this->modalCantidadPorEmpaque,
                    'tipo_presentacion' => $this->modalTipoPresentacion,
                    'es_pesable' => $this->modalEsPesable,
                    'presentacion_base_id' => $this->modalPresentacionBaseId,
                ]);
            }

            if (filled($this->modalCodigoBarra)) {
                $pres->barras()->firstOrCreate([
                    'codigo_barra' => trim($this->modalCodigoBarra),
                ]);
            }

            foreach ($this->modalBarras as $code) {
                $pres->barras()->firstOrCreate([
                    'codigo_barra' => $code,
                ]);
            }

            if ($this->modalImagen) {
                $path = $this->modalImagen->store('productos/presentaciones', 'public');
                $pres->update(['imagen' => $path]);
            }

            return $pres;
        });

        $this->cerrarCrearProductoModal();

        $this->productoId = $presentacion->producto_id;
        $this->presentacionId = $presentacion->id;
        $producto = $presentacion->producto;
        $this->searchProducto = ($producto?->nombre ?? '') . ' — ' . ($presentacion->tipo_presentacion ?? '');
        $this->cargarLotesDisponibles();

        Notification::make()
            ->title($isEdit ? 'Presentación actualizada y seleccionada' : 'Producto y presentación creados y seleccionados')
            ->success()
            ->send();
    }

    protected function generarSlugUnico(string $nombre): string
    {
        $baseSlug = str($nombre)->slug()->toString();
        $slug = $baseSlug;
        $counter = 1;

        while (Producto::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('empresa_id', Auth::user()?->empresa_id)->orderBy('nombre')->get();
    }

    public function getMarcasProperty()
    {
        return Marca::where('empresa_id', Auth::user()?->empresa_id)->orderBy('nombre')->get();
    }

    public function getUnidadesMedidaProperty()
    {
        return UniMedida::where('activo', true)->orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.almacen.ajuste-stock');
    }
}
