<?php

namespace App\Livewire\Compras\Components;

use App\Models\Categoria;
use App\Models\Compra;
use App\Models\DetalleCompra as DetalleCompraModel;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentacionBarra;
use App\Models\ProductoSucursal;
use App\Models\UniMedida;
use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class DetalleCompra extends Component
{
    use WithFileUploads;
    public ?int $compraId = null;

    public ?int $sucursalId = null;

    public array $detalles = [];

    public string $searchProducto = '';

    public array $productosResultados = [];

    public bool $showProductoDropdown = false;

    public ?int $productoId = null;

    public ?string $productoNombre = null;

    public ?int $presentacionSeleccionadaId = null;

    public bool $mostrarTodasPresentaciones = true;

    public int $totalPresentacionesProducto = 0;

    public ?string $ultimaBusquedaSinResultado = null;

    public string $codigoLote = '';

    public ?string $fechaFabricacion = null;

    public ?string $fechaVencimiento = null;

    public ?string $ubicacion = null;

    public ?float $precioCompraTotal = null;

    public ?string $observaciones = null;

    public array $presentacionesDisponibles = [];

    public bool $showCrearPresentacionModal = false;

    public string $modoProductoPresentacion = 'existente';

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

    public array $lotesResultados = [];

    public bool $showLotesDropdown = false;

    public bool $showLoteExistenteModal = false;

    public array $modalLoteDetalles = [];

    public ?int $selectedLoteId = null;

    public ?int $editingLoteId = null;

    public ?int $editingDetalleId = null;

    public ?int $historialProductoId = null;

    public array $historialCompras = [];

    public bool $showHistorial = false;

    protected $listeners = [
        'compraCreada' => 'setCompra',
        'productoSeleccionado' => 'setProducto',
        'refreshDetalles' => 'cargarDetalles',
    ];

    public function mount(?int $compraId = null, ?int $sucursalId = null): void
    {
        $this->compraId = $compraId;
        $this->sucursalId = app(SucursalContext::class)->resolveSucursalForWrite($sucursalId);
        $this->cargarDetalles();
    }

    public function setCompra($compraId, $sucursalId): void
    {
        $this->compraId = $compraId;
        $this->sucursalId = app(SucursalContext::class)->resolveSucursalForWrite((int) $sucursalId);
        $this->cargarDetalles();
    }

    public function cargarDetalles(): void
    {
        if (! $this->compraId) {
            $this->detalles = [];

            return;
        }

        $context = app(SucursalContext::class);

        $this->detalles = DetalleCompraModel::query()
            ->where('compra_id', $this->compraId)
            ->whereHas('compra', fn ($query) => $context->applyToQuery($query))
            ->with([
                'lote.sucursal',
                'lote.lotePresentaciones.productoPresentacion.producto',
                'lote.lotePresentaciones.productoPresentacion.unidadMedida',
            ])
            ->latest()
            ->get()
            ->map(function (DetalleCompraModel $detalle): array {
                $lote = $detalle->lote;
                $presentaciones = $lote?->lotePresentaciones ?? collect();

                return [
                    'id' => $detalle->id,
                    'precio_compra' => (float) $detalle->precio_compra,
                    'total_stock' => (int) $presentaciones->sum('stock'),
                    'lote' => [
                        'id' => $lote?->id,
                        'codigo_lote' => $lote?->codigo_lote,
                        'producto_nombre' => $lote?->producto_nombre,
                        'fecha_vencimiento' => $lote?->fecha_vencimiento?->format('Y-m-d'),
                        'ubicacion' => $lote?->ubicacion,
                        'estado_lote' => $lote?->estado_lote,
                    ],
                    'presentaciones' => $presentaciones->map(function (LotePresentacion $lp): array {
                        $presentacion = $lp->productoPresentacion;

                        return [
                            'id' => $lp->id,
                            'stock' => (int) $lp->stock,
                            'precio_compra' => (float) $lp->precio_compra,
                            'precio_oferta' => $lp->precio_oferta !== null ? (float) $lp->precio_oferta : null,
                            'nombre' => $presentacion?->tipo_presentacion ?: 'Presentación',
                            'cantidad' => $presentacion?->cantidad ?? 1,
                            'unidad' => $presentacion?->unidadMedida?->abreviatura ?? 'und',
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();
    }

    public function updatedSearchProducto(): void
    {
        if (strlen($this->searchProducto) < 2) {
            $this->productosResultados = [];
            $this->showProductoDropdown = false;

            return;
        }

        $term = trim($this->searchProducto);

        $this->productosResultados = ProductoPresentacion::query()
            ->with(['producto', 'unidadMedida', 'barras'])
            ->whereHas('producto', function ($query) {
                $query->where('activo', true)
                    ->where('empresa_id', Auth::user()?->empresa_id);
            })
            ->where(function ($query) use ($term) {
                $query->whereHas('barras', fn ($b) => $b->where('codigo_barra', 'like', "%{$term}%"))
                    ->orWhere('tipo_presentacion', 'like', "%{$term}%")
                    ->orWhereHas('producto', function ($productQuery) use ($term) {
                        $productQuery->where('nombre', 'like', "%{$term}%")
                            ->orWhere('codigo_interno', 'like', "%{$term}%");
                    });
            })
            ->limit(10)
            ->get()
            ->sortByDesc(fn (ProductoPresentacion $presentacion): bool => $presentacion->barras->pluck('codigo_barra')->contains($term))
            ->map(fn (ProductoPresentacion $presentacion): array => [
                'id' => $presentacion->id,
                'producto_id' => $presentacion->producto_id,
                'producto_nombre' => $presentacion->producto?->nombre,
                'codigo' => $presentacion->barras->first()?->codigo_barra ?: $presentacion->producto?->codigo_interno,
                'label' => trim(($presentacion->producto?->nombre ?? 'Producto').' - '.($presentacion->tipo_presentacion ?: 'Presentación').' x '.$presentacion->cantidad.' '.($presentacion->unidadMedida?->abreviatura ?? 'und')),
            ])
            ->values()
            ->toArray();

        $this->showProductoDropdown = count($this->productosResultados) > 0;

        if (! $this->showProductoDropdown && ctype_digit($term) && strlen($term) >= 5 && $this->ultimaBusquedaSinResultado !== $term) {
            $this->abrirCrearPresentacionModal($term);
        }
    }

    public function seleccionarProducto(int $id, string $nombre): void
    {
        $this->productoId = $id;
        $this->productoNombre = $nombre;
        $this->searchProducto = $nombre;
        $this->showProductoDropdown = false;
        $this->presentacionSeleccionadaId = null;
        $this->mostrarTodasPresentaciones = true;
        $this->cargarPresentaciones();
    }

    public function seleccionarPresentacion(int $presentacionId): void
    {
        $presentacion = ProductoPresentacion::with('producto')->find($presentacionId);
        if (! $presentacion || ! $presentacion->producto) {
            return;
        }

        $this->productoId = $presentacion->producto_id;
        $this->productoNombre = $presentacion->producto->nombre;
        $this->presentacionSeleccionadaId = $presentacion->id;
        $this->mostrarTodasPresentaciones = false;
        $this->searchProducto = trim($presentacion->producto->nombre.' - '.($presentacion->tipo_presentacion ?: 'Presentación'));
        $this->showProductoDropdown = false;
        $this->cargarPresentaciones($presentacion->id);
    }

    public function setProducto(int $productoId): void
    {
        $producto = Producto::find($productoId);
        if (! $producto) {
            return;
        }

        $this->seleccionarProducto($producto->id, $producto->nombre);
    }

    protected function cargarPresentaciones(?int $soloPresentacionId = null): void
    {
        if (! $this->productoId) {
            $this->presentacionesDisponibles = [];
            $this->totalPresentacionesProducto = 0;

            return;
        }

        $lotePresMap = [];
        if ($this->editingLoteId) {
            $lotePresMap = LotePresentacion::where('lote_id', $this->editingLoteId)
                ->get()
                ->keyBy('producto_presentacion_id')
                ->toArray();
        }

        $actuales = collect($this->presentacionesDisponibles)->keyBy('id');

        $baseQuery = ProductoPresentacion::query()
            ->where('producto_id', $this->productoId);

        $this->totalPresentacionesProducto = (clone $baseQuery)->count();

        $query = (clone $baseQuery)
            ->with('unidadMedida')
            ->orderBy('id');

        if ($soloPresentacionId && ! $this->mostrarTodasPresentaciones) {
            $query->where('id', $soloPresentacionId);
        }

        $presentaciones = $query->get();
        $ids = $presentaciones->pluck('id')->toArray();

        // Batch-fetch all prices in a single query instead of N queries
        $preciosBatch = ProductoSucursal::query()
            ->where('producto_id', $this->productoId)
            ->where('sucursal_id', $this->sucursalId)
            ->whereHas('lotePresentacion', fn ($q) => $q->whereIn('producto_presentacion_id', $ids))
            ->latest('id')
            ->get()
            ->keyBy(fn ($ps) => $ps->lotePresentacion?->producto_presentacion_id);

        $this->presentacionesDisponibles = $presentaciones
            ->map(function (ProductoPresentacion $presentacion) use ($actuales, $lotePresMap, $preciosBatch): array {
                $actual = $actuales->get($presentacion->id, []);
                $productoSucursal = $preciosBatch->get($presentacion->id);

                $precioVenta = [
                    'precio' => $productoSucursal?->precio !== null ? (float) $productoSucursal->precio : 0,
                    'precio_mayorista' => $productoSucursal?->precio_mayorista !== null ? (float) $productoSucursal->precio_mayorista : null,
                    'minimo_mayorista' => $productoSucursal?->minimo_mayorista ?? 2,
                ];

                $lotePres = $lotePresMap[$presentacion->id] ?? null;
                $cantidad = null;
                $totalPagado = null;
                $precioCompra = 0;
                $precioEspecial = null;

                if ($lotePres) {
                    $cantidad = (int) $lotePres['stock_inicial'];
                    $precioCompra = (float) $lotePres['precio_compra'];
                    $totalPagado = round($precioCompra * $cantidad, 2);
                    $precioEspecial = $lotePres['precio_oferta'] !== null ? (float) $lotePres['precio_oferta'] : null;
                }

                return [
                    'id' => $presentacion->id,
                    'label' => trim(($presentacion->tipo_presentacion ?: 'Presentación').' x '.$presentacion->cantidad.' '.($presentacion->unidadMedida?->abreviatura ?? 'und')),
                    'cantidad' => $actual['cantidad'] ?? $cantidad,
                    'total_pagado' => $actual['total_pagado'] ?? $totalPagado,
                    'precio_compra' => $actual['precio_compra'] ?? $precioCompra,
                    'precio_especial' => $actual['precio_especial'] ?? $precioEspecial,
                    'mostrar_precio_venta' => $actual['mostrar_precio_venta'] ?? false,
                    'precio_venta' => $actual['precio_venta'] ?? $precioVenta['precio'],
                    'precio_mayorista' => $actual['precio_mayorista'] ?? $precioVenta['precio_mayorista'],
                    'minimo_mayorista' => $actual['minimo_mayorista'] ?? $precioVenta['minimo_mayorista'],
                ];
            })
            ->toArray();
    }

    public function verMasPresentaciones(): void
    {
        $this->mostrarTodasPresentaciones = true;
        $this->cargarPresentaciones();
    }

    public function updatedPresentacionesDisponibles($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if ($field === 'cantidad' || $field === 'total_pagado') {
                $qty = (int) ($this->presentacionesDisponibles[$index]['cantidad'] ?? 0);
                $totalPagado = (float) ($this->presentacionesDisponibles[$index]['total_pagado'] ?? 0);

                if ($qty > 0 && $totalPagado > 0) {
                    $this->presentacionesDisponibles[$index]['precio_compra'] = round($totalPagado / $qty, 4);
                } else {
                    $this->presentacionesDisponibles[$index]['precio_compra'] = 0;
                }
            }
        }

        // Recalcular total general del lote
        $total = 0;
        foreach ($this->presentacionesDisponibles as $pres) {
            $total += (float) ($pres['total_pagado'] ?? 0);
        }
        $this->precioCompraTotal = $total > 0 ? round($total, 2) : null;
    }

    public function togglePrecioVenta(int $index): void
    {
        $this->presentacionesDisponibles[$index]['mostrar_precio_venta'] = ! ($this->presentacionesDisponibles[$index]['mostrar_precio_venta'] ?? false);
    }

    public function agregarLote(): void
    {
        $context = app(SucursalContext::class);
        $this->sucursalId = $context->resolveSucursalForWrite($this->sucursalId);

        if (! $this->sucursalId) {
            Notification::make()
                ->title('Selecciona una sucursal para registrar el lote')
                ->warning()
                ->send();

            return;
        }

        $this->validate([
            'compraId' => 'required|exists:compras,id',
            'sucursalId' => ['required', 'integer', Rule::in($context->allowedSucursalIds()->all())],
            'productoId' => 'required|exists:productos,id',
            'codigoLote' => 'required|string|max:255',
            'fechaFabricacion' => 'nullable|date',
            'fechaVencimiento' => 'nullable|date|after_or_equal:fechaFabricacion',
            'ubicacion' => 'nullable|string|max:255',
            'precioCompraTotal' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $presentaciones = collect($this->presentacionesDisponibles)
            ->map(function (array $item): array {
                $item['cantidad'] = (int) ($item['cantidad'] ?? 0);
                $item['total_pagado'] = is_numeric($item['total_pagado'] ?? null) ? (float) $item['total_pagado'] : 0.00;
                $item['precio_compra'] = $item['cantidad'] > 0 ? round($item['total_pagado'] / $item['cantidad'], 4) : 0.00;
                $item['precio_especial'] = $item['precio_especial'] === '' ? null : $item['precio_especial'];
                $item['precio_especial'] = is_numeric($item['precio_especial']) ? (float) $item['precio_especial'] : null;
                $item['precio_venta'] = is_numeric($item['precio_venta'] ?? null) ? (float) $item['precio_venta'] : 0;
                $item['precio_mayorista'] = is_numeric($item['precio_mayorista'] ?? null) ? (float) $item['precio_mayorista'] : null;
                $item['minimo_mayorista'] = max(1, (int) ($item['minimo_mayorista'] ?? 2));

                return $item;
            })
            ->filter(fn (array $item): bool => $item['cantidad'] > 0)
            ->values();

        if ($presentaciones->isEmpty()) {
            Notification::make()
                ->title('Agrega al menos una presentación con cantidad mayor a cero')
                ->warning()
                ->send();

            return;
        }

        $compraValida = $context->applyToQuery(Compra::query())
            ->whereKey($this->compraId)
            ->where('sucursal_id', $this->sucursalId)
            ->exists();

        if (! $compraValida) {
            abort(403, 'No tienes acceso a esta compra.');
        }

        DB::transaction(function () use ($presentaciones) {
            $producto = Producto::query()
                ->where('empresa_id', Auth::user()?->empresa_id)
                ->findOrFail($this->productoId);

            if ($this->editingLoteId) {
                // UPDATE existing lot
                $lote = Lote::findOrFail($this->editingLoteId);
                $lote->update([
                    'codigo_lote' => $this->codigoLote,
                    'producto_nombre' => $producto->nombre,
                    'fecha_fabricacion' => $this->fechaFabricacion,
                    'fecha_vencimiento' => $this->fechaVencimiento,
                    'ubicacion' => $this->ubicacion,
                    'precio_compra' => $this->precioCompraTotal,
                    'observaciones' => $this->observaciones,
                ]);

                if ($this->editingDetalleId) {
                    $detalle = DetalleCompraModel::findOrFail($this->editingDetalleId);
                    $detalle->update([
                        'precio_compra' => $this->precioCompraTotal,
                    ]);
                } else {
                    DetalleCompraModel::create([
                        'compra_id' => $this->compraId,
                        'lote_id' => $lote->id,
                        'precio_compra' => $this->precioCompraTotal,
                    ]);
                }

                // Clean old presentations, sucursales, and movements
                foreach ($lote->lotePresentaciones as $lp) {
                    ProductoSucursal::where('lote_presentacion_id', $lp->id)->delete();
                    MovimientoInventario::where('referencia', "LotePresentacion:{$lp->id}")->delete();
                    $lp->delete();
                }
            } else {
                // CREATE new lot
                $lote = Lote::create([
                    'sucursal_id' => $this->sucursalId,
                    'codigo_lote' => $this->codigoLote,
                    'producto_nombre' => $producto->nombre,
                    'fecha_fabricacion' => $this->fechaFabricacion,
                    'fecha_vencimiento' => $this->fechaVencimiento,
                    'ubicacion' => $this->ubicacion,
                    'precio_compra' => $this->precioCompraTotal,
                    'observaciones' => $this->observaciones,
                    'estado_lote' => 'activo',
                ]);

                DetalleCompraModel::create([
                    'compra_id' => $this->compraId,
                    'lote_id' => $lote->id,
                    'precio_compra' => $this->precioCompraTotal,
                ]);
            }

            foreach ($presentaciones as $item) {
                $lotePresentacion = LotePresentacion::create([
                    'lote_id' => $lote->id,
                    'producto_presentacion_id' => $item['id'],
                    'stock_inicial' => $item['cantidad'],
                    'stock' => $item['cantidad'],
                    'precio_compra' => $item['precio_compra'],
                    'precio_oferta' => $item['precio_especial'],
                ]);

                ProductoSucursal::create([
                    'producto_id' => $this->productoId,
                    'sucursal_id' => $this->sucursalId,
                    'lote_presentacion_id' => $lotePresentacion->id,
                    'stock_minimo' => 0,
                    'precio' => $item['precio_venta'],
                    'minimo_mayorista' => $item['minimo_mayorista'],
                    'precio_mayorista' => $item['precio_mayorista'],
                    'activo' => true,
                ]);

                MovimientoInventario::create([
                    'empresa_id' => Auth::user()->empresa_id ?? $producto->empresa_id,
                    'sucursal_id' => $this->sucursalId,
                    'producto_nombre' => $producto->nombre,
                    'producto_presentacion_id' => $item['id'],
                    'tipo' => 'entrada_compra',
                    'cantidad' => $item['cantidad'],
                    'motivo' => "Compra #{$this->compraId} - lote {$this->codigoLote}" . ($this->editingLoteId ? " (Editado)" : ""),
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => $item['cantidad'],
                ]);
            }
        });

        $isEdit = (bool) $this->editingLoteId;

        $this->resetForm();
        $this->cargarDetalles();
        $this->dispatch('loteEditando', loteId: null);
        $this->dispatch('detalleAgregado');

        Notification::make()
            ->title($isEdit ? 'Lote actualizado correctamente' : 'Lote agregado a la compra')
            ->success()
            ->send();
    }

    public function eliminarDetalle(int $detalleId): void
    {
        DB::transaction(function () use ($detalleId) {
            $context = app(SucursalContext::class);

            $detalle = DetalleCompraModel::query()
                ->with('lote.lotePresentaciones.productoPresentacion.producto')
                ->where('compra_id', $this->compraId)
                ->whereHas('lote', fn ($query) => $context->applyToQuery($query))
                ->findOrFail($detalleId);

            foreach ($detalle->lote?->lotePresentaciones ?? [] as $lotePresentacion) {
                ProductoSucursal::where('lote_presentacion_id', $lotePresentacion->id)->delete();

                MovimientoInventario::create([
                    'empresa_id' => Auth::user()->empresa_id ?? 1,
                    'sucursal_id' => $detalle->lote->sucursal_id,
                    'producto_nombre' => $lotePresentacion->productoPresentacion?->producto?->nombre ?? $detalle->lote->producto_nombre,
                    'producto_presentacion_id' => $lotePresentacion->producto_presentacion_id,
                    'tipo' => 'anulacion_compra_lote',
                    'cantidad' => -$lotePresentacion->stock,
                    'motivo' => "Eliminación de lote de compra #{$this->compraId}",
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => 0,
                ]);

                $lotePresentacion->delete();
            }

            $detalle->lote?->delete();
            $detalle->delete();
        });

        $this->cargarDetalles();
        $this->dispatch('loteEditando', loteId: null);
        $this->dispatch('detalleEliminado');

        Notification::make()
            ->title('Lote eliminado de la compra')
            ->success()
            ->send();
    }

    public function verHistorial(int $productoId): void
    {
        $this->historialProductoId = $productoId;

        $this->historialCompras = DetalleCompraModel::query()
            ->whereHas('lote.lotePresentaciones.productoPresentacion', fn ($query) => $query->where('producto_id', $productoId))
            ->whereHas('lote', fn ($query) => app(SucursalContext::class)->applyToQuery($query))
            ->with(['compra.proveedor', 'lote.lotePresentaciones.productoPresentacion.unidadMedida'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (DetalleCompraModel $detalle): array => [
                'proveedor' => $detalle->compra?->proveedor?->nombre ?? 'N/A',
                'fecha' => $detalle->created_at?->format('d/m/Y'),
                'total' => (float) $detalle->precio_compra,
                'unidades' => (int) ($detalle->lote?->lotePresentaciones?->sum('stock') ?? 0),
            ])
            ->toArray();

        $this->showHistorial = true;
    }

    public function cerrarHistorial(): void
    {
        $this->showHistorial = false;
        $this->historialProductoId = null;
        $this->historialCompras = [];
    }

    protected function resetForm(): void
    {
        $this->reset([
            'searchProducto',
            'productosResultados',
            'showProductoDropdown',
            'productoId',
            'productoNombre',
            'presentacionSeleccionadaId',
            'mostrarTodasPresentaciones',
            'totalPresentacionesProducto',
            'codigoLote',
            'fechaFabricacion',
            'fechaVencimiento',
            'ubicacion',
            'precioCompraTotal',
            'observaciones',
            'presentacionesDisponibles',
            'editingLoteId',
            'editingDetalleId',
            'lotesResultados',
            'showLotesDropdown',
            'showLoteExistenteModal',
            'modalLoteDetalles',
            'selectedLoteId',
        ]);
        $this->mostrarTodasPresentaciones = true;
        $this->totalPresentacionesProducto = 0;
    }

    public function abrirCrearPresentacionModal(?string $codigoBarra = null): void
    {
        $codigoBarra = trim((string) ($codigoBarra ?: $this->searchProducto));
        $this->ultimaBusquedaSinResultado = $codigoBarra;

        $selectedProductId = $this->productoId;
        $selectedProductName = $this->productoNombre;

        $this->resetCrearPresentacionModal();

        if ($selectedProductId) {
            $this->modoProductoPresentacion = 'existente';
            $this->modalProductoId = $selectedProductId;
            $this->modalProductoNombre = $selectedProductName;
            $this->modalSearchProducto = $selectedProductName;
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

        $this->showCrearPresentacionModal = true;
    }

    public function cerrarCrearPresentacionModal(): void
    {
        $this->showCrearPresentacionModal = false;
        $this->resetCrearPresentacionModal();
    }

    protected function resetCrearPresentacionModal(): void
    {
        $this->reset([
            'modalSearchProducto',
            'modalProductosResultados',
            'modalShowProductoDropdown',
            'modalProductoId',
            'modalProductoNombre',
            'modalNuevoProductoNombre',
            'modalCodigoInterno',
            'modalCategoriaId',
            'modalMarcaId',
            'modalUnidadMedidaId',
            'modalTipoPresentacion',
            'modalCodigoBarra',
            'modalEsPesable',
            'modalPresentacionBaseId',
            'modalImagen',
            'modalEditingPresentationId',
            'showModalPresentacionDropdown',
            'modalPresentacionesResultados',
            'modalBarras',
            'modalNuevoCodigoBarra',
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

    public function updatedModalCantidadPorEmpaque(): void
    {
        if ($this->modalCantidadPorEmpaque <= 1) {
            $this->modalPresentacionBaseId = null;
        }
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

    public function crearPresentacionDesdeModal(): void
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
                'nullable',
                'string',
                'max:255',
                Rule::unique('producto_presentacion_barras', 'codigo_barra')
                    ->ignore($this->modalEditingPresentationId, 'producto_presentacion_id')
            ],
            'modalImagen' => 'nullable|image|max:2048',
        ];

        if ($this->modoProductoPresentacion === 'nuevo') {
            $rules['modalNuevoProductoNombre'] = 'required|string|max:255';
            $rules['modalCodigoInterno'] = 'nullable|string|max:100';
            $rules['modalCategoriaId'] = 'nullable|exists:categorias,id';
            $rules['modalMarcaId'] = 'nullable|exists:marcas,id';
            $rules['modalPresentacionBaseId'] = 'nullable';
        } else {
            $rules['modalProductoId'] = 'required|exists:productos,id';
            $rules['modalPresentacionBaseId'] = [
                'nullable',
                Rule::exists('producto_presentacion', 'id')->where(function ($query) {
                    $query->where('producto_id', $this->modalProductoId);
                })
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

        $this->cerrarCrearPresentacionModal();
        $this->seleccionarPresentacion($presentacion->id);

        Notification::make()
            ->title($isEdit ? 'Presentación actualizada y seleccionada' : 'Presentación creada y seleccionada')
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

    public function updatedCodigoLote(): void
    {
        if (strlen($this->codigoLote) < 2) {
            $this->lotesResultados = [];
            $this->showLotesDropdown = false;
            return;
        }

        $term = trim($this->codigoLote);

        $this->lotesResultados = Lote::query()
            ->where('codigo_lote', 'like', "%{$term}%")
            ->where('sucursal_id', $this->sucursalId)
            ->limit(8)
            ->get()
            ->map(fn (Lote $l): array => [
                'id' => $l->id,
                'codigo_lote' => $l->codigo_lote,
                'producto_nombre' => $l->producto_nombre,
                'fecha_vencimiento' => $l->fecha_vencimiento ? $l->fecha_vencimiento->format('d/m/Y') : 'Sin vencimiento',
            ])
            ->toArray();

        $this->showLotesDropdown = count($this->lotesResultados) > 0;
    }

    public function verLoteExistente(int $loteId): void
    {
        $lote = Lote::with(['lotePresentaciones.productoPresentacion.unidadMedida'])->findOrFail($loteId);

        $this->selectedLoteId = $lote->id;
        $this->modalLoteDetalles = [
            'id' => $lote->id,
            'codigo_lote' => $lote->codigo_lote,
            'producto_nombre' => $lote->producto_nombre,
            'fecha_fabricacion' => $lote->fecha_fabricacion ? $lote->fecha_fabricacion->format('d/m/Y') : '—',
            'fecha_vencimiento' => $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('d/m/Y') : '—',
            'ubicacion' => $lote->ubicacion ?? '—',
            'observaciones' => $lote->observaciones ?? '—',
            'presentaciones' => $lote->lotePresentaciones->map(fn ($lp) => [
                'nombre' => $lp->productoPresentacion?->tipo_presentacion ?? 'Presentación',
                'cantidad' => $lp->productoPresentacion?->cantidad ?? 1,
                'unidad' => $lp->productoPresentacion?->unidadMedida?->abreviatura ?? 'und',
                'stock' => (int) $lp->stock,
                'precio_compra' => (float) $lp->precio_compra,
                'precio_oferta' => $lp->precio_oferta !== null ? (float) $lp->precio_oferta : null,
            ])->toArray()
        ];

        $this->showLotesDropdown = false;
        $this->showLoteExistenteModal = true;
    }

    public function cargarLoteExistenteParaEditar(): void
    {
        $lote = Lote::with(['lotePresentaciones.productoPresentacion'])->findOrFail($this->selectedLoteId);

        $firstLp = $lote->lotePresentaciones->first();
        $this->productoId = $firstLp?->productoPresentacion?->producto_id;
        $this->productoNombre = $lote->producto_nombre;
        $this->searchProducto = $lote->producto_nombre;
        
        $this->codigoLote = $lote->codigo_lote;
        $this->fechaFabricacion = $lote->fecha_fabricacion ? $lote->fecha_fabricacion->format('Y-m-d') : null;
        $this->fechaVencimiento = $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : null;
        $this->ubicacion = $lote->ubicacion;
        $this->precioCompraTotal = (float) $lote->precio_compra;
        $this->observaciones = $lote->observaciones;

        $this->editingLoteId = $lote->id;
        
        $detalle = DetalleCompraModel::where('compra_id', $this->compraId)
            ->where('lote_id', $lote->id)
            ->first();
        $this->editingDetalleId = $detalle?->id ?? null;

        $this->presentacionesDisponibles = [];
        $this->cargarPresentaciones();
        
        $this->dispatch('loteEditando', loteId: $lote->id);
        
        $this->showLoteExistenteModal = false;
        $this->selectedLoteId = null;
        $this->modalLoteDetalles = [];
        
        Notification::make()
            ->title('Lote cargado para editar')
            ->info()
            ->send();
    }

    public function editarDetalle(int $detalleId): void
    {
        $detalle = DetalleCompraModel::with([
            'lote.lotePresentaciones.productoPresentacion.producto',
            'lote.lotePresentaciones.productoPresentacion.unidadMedida',
        ])
        ->where('compra_id', $this->compraId)
        ->findOrFail($detalleId);

        $lote = $detalle->lote;
        if (! $lote) {
            return;
        }

        $firstLp = $lote->lotePresentaciones->first();

        $this->editingDetalleId = $detalle->id;
        $this->editingLoteId = $lote->id;
        
        $this->productoId = $firstLp?->productoPresentacion?->producto_id;
        $this->productoNombre = $lote->producto_nombre;
        $this->searchProducto = $lote->producto_nombre;
        
        $this->codigoLote = $lote->codigo_lote;
        $this->fechaFabricacion = $lote->fecha_fabricacion ? $lote->fecha_fabricacion->format('Y-m-d') : null;
        $this->fechaVencimiento = $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : null;
        $this->ubicacion = $lote->ubicacion;
        $this->precioCompraTotal = (float) $detalle->precio_compra;
        $this->observaciones = $lote->observaciones;

        $this->presentacionesDisponibles = [];
        $this->cargarPresentaciones();

        $this->dispatch('loteEditando', loteId: $lote->id);

        Notification::make()
            ->title('Lote cargado para editar')
            ->info()
            ->send();
    }

    public function cancelarEdicion(): void
    {
        $this->resetForm();
        $this->editingLoteId = null;
        $this->editingDetalleId = null;
        $this->dispatch('loteEditando', loteId: null);
    }

    public function render()
    {
        return view('livewire.compras.components.detalle-compra');
    }
}
