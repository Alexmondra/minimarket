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
use App\Models\ProductoSucursal;
use App\Models\UniMedida;
use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DetalleCompra extends Component
{
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
    public int $modalCantidadPorEmpaque = 1;
    public ?string $modalTipoPresentacion = null;
    public ?string $modalCodigoBarra = null;
    public bool $modalEsPesable = false;

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
        if (!$this->compraId) {
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
            ->with(['producto', 'unidadMedida'])
            ->whereHas('producto', function ($query) {
                $query->where('activo', true)
                    ->where('empresa_id', Auth::user()?->empresa_id);
            })
            ->where(function ($query) use ($term) {
                $query->where('codigo_barra', 'like', "%{$term}%")
                    ->orWhere('tipo_presentacion', 'like', "%{$term}%")
                    ->orWhereHas('producto', function ($productQuery) use ($term) {
                        $productQuery->where('nombre', 'like', "%{$term}%")
                            ->orWhere('codigo_interno', 'like', "%{$term}%");
                    });
            })
            ->limit(10)
            ->get()
            ->sortByDesc(fn (ProductoPresentacion $presentacion): bool => $presentacion->codigo_barra === $term)
            ->map(fn (ProductoPresentacion $presentacion): array => [
                'id' => $presentacion->id,
                'producto_id' => $presentacion->producto_id,
                'producto_nombre' => $presentacion->producto?->nombre,
                'codigo' => $presentacion->codigo_barra ?: $presentacion->producto?->codigo_interno,
                'label' => trim(($presentacion->producto?->nombre ?? 'Producto') . ' - ' . ($presentacion->tipo_presentacion ?: 'Presentación') . ' x ' . $presentacion->cantidad . ' ' . ($presentacion->unidadMedida?->abreviatura ?? 'und')),
            ])
            ->values()
            ->toArray();

        $this->showProductoDropdown = count($this->productosResultados) > 0;

        if (!$this->showProductoDropdown && strlen($term) >= 5 && $this->ultimaBusquedaSinResultado !== $term) {
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
        if (!$presentacion || !$presentacion->producto) {
            return;
        }

        $this->productoId = $presentacion->producto_id;
        $this->productoNombre = $presentacion->producto->nombre;
        $this->presentacionSeleccionadaId = $presentacion->id;
        $this->mostrarTodasPresentaciones = false;
        $this->searchProducto = trim($presentacion->producto->nombre . ' - ' . ($presentacion->tipo_presentacion ?: 'Presentación'));
        $this->showProductoDropdown = false;
        $this->cargarPresentaciones($presentacion->id);
    }

    public function setProducto(int $productoId): void
    {
        $producto = Producto::find($productoId);
        if (!$producto) {
            return;
        }

        $this->seleccionarProducto($producto->id, $producto->nombre);
    }

    protected function cargarPresentaciones(?int $soloPresentacionId = null): void
    {
        if (!$this->productoId) {
            $this->presentacionesDisponibles = [];
            $this->totalPresentacionesProducto = 0;
            return;
        }

        $actuales = collect($this->presentacionesDisponibles)->keyBy('id');

        $baseQuery = ProductoPresentacion::query()
            ->where('producto_id', $this->productoId);

        $this->totalPresentacionesProducto = (clone $baseQuery)->count();

        $query = (clone $baseQuery)
            ->with('unidadMedida')
            ->orderBy('id');

        if ($soloPresentacionId && !$this->mostrarTodasPresentaciones) {
            $query->where('id', $soloPresentacionId);
        }

        $this->presentacionesDisponibles = $query->get()
            ->map(function (ProductoPresentacion $presentacion) use ($actuales): array {
                $actual = $actuales->get($presentacion->id, []);
                $precioVenta = $this->obtenerPrecioVentaActual($presentacion->id);

                return [
                'id' => $presentacion->id,
                'label' => trim(($presentacion->tipo_presentacion ?: 'Presentación') . ' x ' . $presentacion->cantidad . ' ' . ($presentacion->unidadMedida?->abreviatura ?? 'und')),
                    'cantidad' => $actual['cantidad'] ?? null,
                    'precio_especial' => $actual['precio_especial'] ?? null,
                    'mostrar_precio_venta' => $actual['mostrar_precio_venta'] ?? false,
                    'precio_venta' => $actual['precio_venta'] ?? $precioVenta['precio'],
                    'precio_mayorista' => $actual['precio_mayorista'] ?? $precioVenta['precio_mayorista'],
                    'minimo_mayorista' => $actual['minimo_mayorista'] ?? $precioVenta['minimo_mayorista'],
                ];
            })
            ->toArray();
    }

    protected function obtenerPrecioVentaActual(int $presentacionId): array
    {
        $productoSucursal = ProductoSucursal::query()
            ->where('producto_id', $this->productoId)
            ->where('sucursal_id', $this->sucursalId)
            ->whereHas('lotePresentacion', fn ($query) => $query->where('producto_presentacion_id', $presentacionId))
            ->latest()
            ->first();

        return [
            'precio' => $productoSucursal?->precio !== null ? (float) $productoSucursal->precio : 0,
            'precio_mayorista' => $productoSucursal?->precio_mayorista !== null ? (float) $productoSucursal->precio_mayorista : null,
            'minimo_mayorista' => $productoSucursal?->minimo_mayorista ?? 2,
        ];
    }

    public function verMasPresentaciones(): void
    {
        $this->mostrarTodasPresentaciones = true;
        $this->cargarPresentaciones();
    }

    public function togglePrecioVenta(int $index): void
    {
        $this->presentacionesDisponibles[$index]['mostrar_precio_venta'] = ! ($this->presentacionesDisponibles[$index]['mostrar_precio_venta'] ?? false);
    }

    public function agregarLote(): void
    {
        $context = app(SucursalContext::class);
        $this->sucursalId = $context->resolveSucursalForWrite($this->sucursalId);

        if (!$this->sucursalId) {
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

        if (!$compraValida) {
            abort(403, 'No tienes acceso a esta compra.');
        }

        DB::transaction(function () use ($presentaciones) {
            $producto = Producto::query()
                ->where('empresa_id', Auth::user()?->empresa_id)
                ->findOrFail($this->productoId);

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

            foreach ($presentaciones as $item) {
                $lotePresentacion = LotePresentacion::create([
                    'lote_id' => $lote->id,
                    'producto_presentacion_id' => $item['id'],
                    'stock' => $item['cantidad'],
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
                    'motivo' => "Compra #{$this->compraId} - lote {$this->codigoLote}",
                    'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                    'user_id' => Auth::id(),
                    'stock_final' => $item['cantidad'],
                ]);
            }
        });

        $this->resetForm();
        $this->cargarDetalles();
        $this->dispatch('detalleAgregado');

        Notification::make()
            ->title('Lote agregado a la compra')
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
        ]);
        $this->mostrarTodasPresentaciones = true;
        $this->totalPresentacionesProducto = 0;
    }

    public function abrirCrearPresentacionModal(?string $codigoBarra = null): void
    {
        $codigoBarra = trim((string) ($codigoBarra ?: $this->searchProducto));
        $this->ultimaBusquedaSinResultado = $codigoBarra;
        $this->resetCrearPresentacionModal();
        $this->modalCodigoBarra = $codigoBarra;
        $this->modalSearchProducto = $this->productoNombre ?? '';
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
        ]);
        $this->modoProductoPresentacion = 'existente';
        $this->modalAfectoIgv = true;
        $this->modalCantidadPorEmpaque = 1;
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
    }

    public function crearPresentacionDesdeModal(): void
    {
        $rules = [
            'modalUnidadMedidaId' => 'required|exists:unidades_medida,id',
            'modalCantidadPorEmpaque' => 'required|integer|min:1',
            'modalTipoPresentacion' => 'required|string|max:255',
            'modalCodigoBarra' => 'nullable|string|max:255',
        ];

        if ($this->modoProductoPresentacion === 'nuevo') {
            $rules['modalNuevoProductoNombre'] = 'required|string|max:255';
            $rules['modalCodigoInterno'] = 'nullable|string|max:100';
            $rules['modalCategoriaId'] = 'nullable|exists:categorias,id';
            $rules['modalMarcaId'] = 'nullable|exists:marcas,id';
        } else {
            $rules['modalProductoId'] = 'required|exists:productos,id';
        }

        $this->validate($rules);

        $presentacion = DB::transaction(function (): ProductoPresentacion {
            $producto = $this->modoProductoPresentacion === 'nuevo'
                ? Producto::create([
                    'empresa_id' => Auth::user()?->empresa_id ?? 1,
                    'categoria_id' => $this->modalCategoriaId,
                    'marca_id' => $this->modalMarcaId,
                    'codigo_interno' => $this->modalCodigoInterno ?: 'PROD-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'nombre' => $this->modalNuevoProductoNombre,
                    'slug' => $this->generarSlugUnico($this->modalNuevoProductoNombre),
                    'afecto_igv' => $this->modalAfectoIgv,
                    'activo' => true,
                ])
                : Producto::query()
                    ->where('empresa_id', Auth::user()?->empresa_id)
                    ->findOrFail($this->modalProductoId);

            return ProductoPresentacion::create([
                'producto_id' => $producto->id,
                'unidad_medida_id' => $this->modalUnidadMedidaId,
                'cantidad' => $this->modalCantidadPorEmpaque,
                'tipo_presentacion' => $this->modalTipoPresentacion,
                'codigo_barra' => $this->modalCodigoBarra,
                'es_pesable' => $this->modalEsPesable,
            ]);
        });

        $this->cerrarCrearPresentacionModal();
        $this->seleccionarPresentacion($presentacion->id);

        Notification::make()
            ->title('Presentación creada y seleccionada')
            ->success()
            ->send();
    }

    protected function generarSlugUnico(string $nombre): string
    {
        $baseSlug = str($nombre)->slug()->toString();
        $slug = $baseSlug;
        $counter = 1;

        while (Producto::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
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
        return view('livewire.compras.components.detalle-compra');
    }
}
