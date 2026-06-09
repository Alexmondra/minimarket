<?php

namespace App\Livewire\Productos;

use App\Filament\Clusters\Ventas\Resources\Documentos\DocumentoResource;
use App\Models\Categoria;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class IngresoRapidoProducto extends Component
{
    public string $busqueda = '';

    public ?string $codigoBarra = null;

    public string $productoSearchTerm = '';

    public array $productoSearchResults = [];

    public int $highlightedIndex = -1;

    public ?string $nombre = null;

    public ?int $categoriaId = null;

    public ?string $nuevaCategoria = null;

    public ?int $marcaId = null;

    public ?string $nuevaMarca = null;

    public ?int $unidadMedidaId = null;

    public string $tipoPresentacion = 'Unidad';

    public int $cantidadPresentacion = 1;

    public ?int $presentacionBaseId = null;

    public bool $afectoIgv = true;

    public int $cantidadIngreso = 1;

    public ?string $codigoLote = null;

    public ?string $fechaVencimiento = null;

    public ?string $ubicacion = null;

    public int $stockMinimo = 5;

    public ?float $precioCompra = null;

    public ?float $totalPagado = null;

    public ?float $precioVenta = null;

    public ?float $precioOferta = null;

    public ?float $precioMayorista = null;

    public int $minimoMayorista = 12;

    public ?int $productoExistenteId = null;

    public ?int $presentacionExistenteId = null;

    public ?string $productoExistenteNombre = null;

    public bool $crearNuevaPresentacion = true;

    public array $presentacionesDisponibles = [];

    public ?string $ultimoProducto = null;

    public ?string $ultimoPresentacion = null;

    public ?string $ultimoCodigo = null;

    public ?float $ultimoPrecio = null;

    public bool $showProductoModal = false;

    public bool $showPresentacionInfoModal = false;

    public ?array $presentacionInfoData = null;

    public bool $editandoProductoModal = false;

    public ?string $modalNombre = null;

    public ?int $modalCategoriaId = null;

    public ?int $modalMarcaId = null;

    public ?string $modalDescripcion = null;

    public bool $modalAfectoIgv = true;

    public function mount(): void
    {
        abort_unless($this->puedeIngresar(), 403, 'No tienes permiso para ingresar productos.');
        abort_unless($this->sucursalId(), 403, 'Selecciona una sucursal para ingresar productos.');

        $this->unidadMedidaId = $this->unidadDefaultId();
        $this->codigoLote = $this->generarCodigoLote();
    }

    public function updatedCodigoBarra(): void
    {
        $this->buscarProductoPorBarra();
    }

    public function updatedBusqueda(): void
    {
        $this->highlightedIndex = -1;

        $term = trim($this->busqueda);
        $this->productoSearchTerm = $term;

        if ($term === '') {
            $this->productoSearchResults = [];
            $this->limpiarFormulario();
            return;
        }

        if ($this->seleccionarPorCodigoBarraExacto($term)) {
            return;
        }

        $this->buscarProductos();

        if (! $this->productoExistenteId && mb_strlen($term) >= 2 && $this->productoSearchResults === []) {
            $this->nombre = $term;
        }
    }

    public function updatedProductoSearchTerm(): void
    {
        $this->buscarProductos();
    }

    public function updatedPrecioCompra(): void
    {
        if (! $this->precioVenta && $this->precioCompra) {
            $this->precioVenta = round($this->precioCompra * 1.3, 2);
        }
    }

    public function updatedTotalPagado(): void
    {
        $this->precioCompra = $this->costoUnitarioCalculado();

        if (! $this->precioVenta && $this->precioCompra) {
            $this->precioVenta = round($this->precioCompra * 1.3, 2);
        }
    }

    public function updatedCantidadIngreso(): void
    {
        $this->precioCompra = $this->costoUnitarioCalculado();
    }

    public function updatedCrearNuevaPresentacion(): void
    {
        if ($this->crearNuevaPresentacion) {
            $this->presentacionExistenteId = null;
            $this->unidadMedidaId = $this->unidadDefaultId();
            $this->tipoPresentacion = 'Unidad';
            $this->cantidadPresentacion = 1;
            $this->presentacionBaseId = null;
            return;
        }

        $this->presentacionExistenteId = $this->presentacionesDisponibles[0]['id'] ?? null;
        $this->cargarPresentacionSeleccionada();
    }

    public function updatedPresentacionExistenteId(): void
    {
        $this->cargarPresentacionSeleccionada();
    }

    public function getCategoriasProperty()
    {
        return Categoria::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->orderBy('nombre')
            ->get();
    }

    public function getMarcasProperty()
    {
        return Marca::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->orderBy('nombre')
            ->get();
    }

    public function getUnidadesProperty()
    {
        return UniMedida::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function getSucursalNombreProperty(): string
    {
        $sucursal = app(SucursalContext::class)
            ->allowedSucursales(Auth::user())
            ->firstWhere('id', $this->sucursalId());

        return $sucursal?->nombre_sucursal ?? 'Sucursal activa';
    }

    public function getMargenProperty(): ?float
    {
        if (! is_numeric($this->precioCompra) || ! is_numeric($this->precioVenta)) {
            return null;
        }

        return round((float) $this->precioVenta - (float) $this->precioCompra, 2);
    }

    public function getMargenPorcentajeProperty(): ?float
    {
        if (! is_numeric($this->precioCompra) || (float) $this->precioCompra <= 0 || ! is_numeric($this->precioVenta)) {
            return null;
        }

        return round((((float) $this->precioVenta - (float) $this->precioCompra) / (float) $this->precioCompra) * 100, 1);
    }

    public function getMargenTotalProperty(): ?float
    {
        if (! is_numeric($this->precioCompra) || ! is_numeric($this->precioVenta) || ! is_numeric($this->cantidadIngreso)) {
            return null;
        }

        return round(((float) $this->precioVenta - (float) $this->precioCompra) * (int) $this->cantidadIngreso, 2);
    }

    public function getMargenTotalMayoristaProperty(): ?float
    {
        if (! is_numeric($this->precioCompra) || ! is_numeric($this->precioMayorista) || ! is_numeric($this->cantidadIngreso)) {
            return null;
        }

        return round(((float) $this->precioMayorista - (float) $this->precioCompra) * (int) $this->cantidadIngreso, 2);
    }

    public function getMargenPorcentajeMayoristaProperty(): ?float
    {
        if (! is_numeric($this->precioCompra) || (float) $this->precioCompra <= 0 || ! is_numeric($this->precioMayorista)) {
            return null;
        }

        return round((((float) $this->precioMayorista - (float) $this->precioCompra) / (float) $this->precioCompra) * 100, 1);
    }

    public function buscarProductos(): void
    {
        $term = trim($this->productoSearchTerm);

        if (mb_strlen($term) < 2) {
            $this->productoSearchResults = [];
            return;
        }

        $empresaId = Auth::user()->empresa_id;
        $like = '%'.$term.'%';

        $this->productoSearchResults = Producto::query()
            ->where('empresa_id', $empresaId)
            ->where(function (Builder $query) use ($like): void {
                $query->where('nombre', 'like', $like)
                    ->orWhere('codigo_interno', 'like', $like)
                    ->orWhereHas('presentaciones.barras', fn (Builder $query) => $query->where('codigo_barra', 'like', $like));
            })
            ->withCount('presentaciones')
            ->orderBy('nombre')
            ->limit(8)
            ->get(['id', 'nombre', 'codigo_interno', 'categoria_id', 'marca_id', 'afecto_igv'])
            ->map(fn (Producto $producto): array => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo_interno' => $producto->codigo_interno,
                'presentaciones_count' => $producto->presentaciones_count,
            ])
            ->all();
    }

    public function seleccionarProducto(int $productoId): void
    {
        $producto = Producto::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->findOrFail($productoId);

        $this->hidratarProductoExistente($producto);
        $this->productoSearchTerm = $producto->nombre;
        $this->busqueda = $producto->nombre;
        $this->productoSearchResults = [];
        $this->highlightedIndex = -1;
    }

    public function highlightDown(): void
    {
        $count = count($this->productoSearchResults);
        if ($count === 0) return;
        $this->highlightedIndex = min($this->highlightedIndex + 1, $count - 1);
    }

    public function highlightUp(): void
    {
        if ($this->highlightedIndex <= 0) {
            $this->highlightedIndex = -1;
            return;
        }
        $this->highlightedIndex--;
    }

    public function highlightEnter(): void
    {
        if ($this->highlightedIndex >= 0 && isset($this->productoSearchResults[$this->highlightedIndex])) {
            $this->seleccionarProducto($this->productoSearchResults[$this->highlightedIndex]['id']);
        }
    }

    public function limpiarProductoSeleccionado(): void
    {
        $codigoBarra = $this->codigoBarra;
        $precioCompra = $this->precioCompra;
        $totalPagado = $this->totalPagado;
        $precioVenta = $this->precioVenta;

        $this->busqueda = '';
        $this->productoExistenteId = null;
        $this->presentacionExistenteId = null;
        $this->productoExistenteNombre = null;
        $this->productoSearchTerm = '';
        $this->productoSearchResults = [];
        $this->presentacionesDisponibles = [];
        $this->crearNuevaPresentacion = true;
        $this->nombre = null;
        $this->categoriaId = null;
        $this->marcaId = null;
        $this->nuevaCategoria = null;
        $this->nuevaMarca = null;
        $this->unidadMedidaId = $this->unidadDefaultId();
        $this->tipoPresentacion = 'Unidad';
        $this->cantidadPresentacion = 1;
        $this->presentacionBaseId = null;
        $this->afectoIgv = true;
        $this->codigoBarra = $codigoBarra;
        $this->precioCompra = $precioCompra;
        $this->totalPagado = $totalPagado;
        $this->precioVenta = $precioVenta;
    }

    public function buscarProductoPorBarra(): void
    {
        $codigo = trim((string) $this->codigoBarra);

        if ($codigo === '') {
            return;
        }

        $barra = ProductoPresentacionBarra::query()
            ->with('productoPresentacion.producto')
            ->where('codigo_barra', $codigo)
            ->first();

        $presentacion = $barra?->productoPresentacion;
        $producto = $presentacion?->producto;

        if (! $producto || $producto->empresa_id !== Auth::user()->empresa_id) {
            return;
        }

        $this->hidratarProductoExistente($producto);
        $this->crearNuevaPresentacion = false;
        $this->presentacionExistenteId = $presentacion->id;
        $this->cargarPresentacionSeleccionada();
        $this->productoSearchTerm = $producto->nombre;
        $this->busqueda = $codigo;
        $this->productoSearchResults = [];
    }

    public function verInfoPresentacion(): void
    {
        if (! $this->productoExistenteId || ! $this->presentacionExistenteId) {
            return;
        }

        $presentacion = ProductoPresentacion::query()
            ->with(['unidadMedida', 'barras'])
            ->where('producto_id', $this->productoExistenteId)
            ->find($this->presentacionExistenteId);

        if (! $presentacion) {
            return;
        }

        $this->presentacionInfoData = [
            'tipo' => $presentacion->tipo_presentacion ?: 'Presentacion',
            'unidad' => $presentacion->unidadMedida?->nombre.' ('.$presentacion->unidadMedida?->abreviatura.')',
            'cantidad' => (int) $presentacion->cantidad,
            'barras' => $presentacion->barras->pluck('codigo_barra')->values()->all(),
            'precio' => (float) $this->precioVenta,
            'precio_mayorista' => $this->precioMayorista,
            'stock_minimo' => $this->stockMinimo,
        ];

        $this->showPresentacionInfoModal = true;
    }

    public function cerrarPresentacionInfoModal(): void
    {
        $this->showPresentacionInfoModal = false;
        $this->presentacionInfoData = null;
    }

    public function abrirDetalleProducto(): void
    {
        if (! $this->productoExistenteId) {
            return;
        }

        $producto = Producto::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->findOrFail($this->productoExistenteId);

        $this->modalNombre = $producto->nombre;
        $this->modalCategoriaId = $producto->categoria_id;
        $this->modalMarcaId = $producto->marca_id;
        $this->modalDescripcion = $producto->descripcion;
        $this->modalAfectoIgv = (bool) $producto->afecto_igv;
        $this->editandoProductoModal = false;
        $this->showProductoModal = true;
    }

    public function editarProductoModal(): void
    {
        $this->editandoProductoModal = true;
    }

    public function cerrarProductoModal(): void
    {
        $this->showProductoModal = false;
        $this->editandoProductoModal = false;
    }

    public function guardarProductoModal(): void
    {
        if (! $this->productoExistenteId) {
            return;
        }

        $this->validate([
            'modalNombre' => 'required|string|max:255',
            'modalCategoriaId' => 'nullable|exists:categorias,id',
            'modalMarcaId' => 'nullable|exists:marcas,id',
            'modalDescripcion' => 'nullable|string|max:65535',
            'modalAfectoIgv' => 'boolean',
        ]);

        $producto = Producto::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->findOrFail($this->productoExistenteId);

        $producto->update([
            'nombre' => trim((string) $this->modalNombre),
            'categoria_id' => $this->modalCategoriaId,
            'marca_id' => $this->modalMarcaId,
            'descripcion' => $this->modalDescripcion,
            'afecto_igv' => $this->modalAfectoIgv,
        ]);

        $this->hidratarProductoExistente($producto->fresh());
        $this->busqueda = $producto->nombre;
        $this->productoSearchTerm = $producto->nombre;
        $this->cerrarProductoModal();

        Notification::make()
            ->title('Producto actualizado')
            ->success()
            ->send();
    }

    public function cargarPresentacionSeleccionada(): void
    {
        if (! $this->productoExistenteId || ! $this->presentacionExistenteId) {
            return;
        }

        $presentacion = ProductoPresentacion::query()
            ->where('producto_id', $this->productoExistenteId)
            ->find($this->presentacionExistenteId);

        if (! $presentacion) {
            return;
        }

        $this->unidadMedidaId = $presentacion->unidad_medida_id;
        $this->tipoPresentacion = $presentacion->tipo_presentacion ?: 'Unidad';
        $this->cantidadPresentacion = (int) $presentacion->cantidad;
        $this->presentacionBaseId = $presentacion->presentacion_base_id;

        $productoSucursal = ProductoSucursal::query()
            ->where('producto_id', $this->productoExistenteId)
            ->where('sucursal_id', $this->sucursalId())
            ->whereHas('lotePresentacion', fn (Builder $query) => $query->where('producto_presentacion_id', $presentacion->id))
            ->latest()
            ->first();

        if ($productoSucursal) {
            $this->precioVenta = (float) $productoSucursal->precio;
            $this->precioMayorista = $productoSucursal->precio_mayorista !== null ? (float) $productoSucursal->precio_mayorista : null;
            $this->minimoMayorista = (int) $productoSucursal->minimo_mayorista;
            $this->stockMinimo = (int) $productoSucursal->stock_minimo;
        }
    }

    public function guardar(): void
    {
        abort_unless($this->puedeIngresar(), 403, 'No tienes permiso para ingresar productos.');

        $this->validate($this->rules(), $this->messages());

        $producto = null;
        $presentacion = null;
        $sucursalId = $this->sucursalId();
        $empresaId = Auth::user()->empresa_id;

        abort_unless($sucursalId, 403, 'Selecciona una sucursal para ingresar productos.');

        DB::transaction(function () use (&$producto, &$presentacion, $empresaId, $sucursalId): void {
            if ($this->productoExistenteId) {
                $producto = Producto::query()
                    ->where('empresa_id', $empresaId)
                    ->findOrFail($this->productoExistenteId);
            } else {
                $producto = Producto::create([
                    'empresa_id' => $empresaId,
                    'categoria_id' => $this->resolverCategoria($empresaId),
                    'marca_id' => $this->resolverMarca($empresaId),
                    'codigo_interno' => $this->generarCodigoInterno(),
                    'nombre' => trim((string) $this->nombre),
                    'slug' => $this->generarSlug(trim((string) $this->nombre)),
                    'afecto_igv' => $this->afectoIgv,
                    'activo' => true,
                ]);
            }

            if ($this->productoExistenteId && ! $this->crearNuevaPresentacion) {
                $presentacion = ProductoPresentacion::query()
                    ->where('producto_id', $producto->id)
                    ->findOrFail($this->presentacionExistenteId);
            } else {
                $presentacion = ProductoPresentacion::create([
                    'producto_id' => $producto->id,
                    'presentacion_base_id' => $this->presentacionBaseId,
                    'unidad_medida_id' => $this->unidadMedidaId,
                    'cantidad' => $this->cantidadPresentacion,
                    'tipo_presentacion' => trim($this->tipoPresentacion) ?: 'Unidad',
                    'es_pesable' => false,
                ]);
            }

            $this->registrarCodigoBarraSiCorresponde($presentacion);

            $lote = Lote::create([
                'sucursal_id' => $sucursalId,
                'codigo_lote' => filled($this->codigoLote) ? trim((string) $this->codigoLote) : $this->generarCodigoLote(),
                'producto_nombre' => $producto->nombre,
                'fecha_vencimiento' => $this->fechaVencimiento ?: null,
                'ubicacion' => filled($this->ubicacion) ? trim((string) $this->ubicacion) : null,
                'precio_compra' => round($this->costoUnitarioCalculado() * (int) $this->cantidadIngreso, 2),
                'observaciones' => 'Ingreso rapido de productos',
                'estado_lote' => 'activo',
            ]);

            $lotePresentacion = LotePresentacion::create([
                'lote_id' => $lote->id,
                'producto_presentacion_id' => $presentacion->id,
                'stock_inicial' => $this->cantidadIngreso,
                'stock' => $this->cantidadIngreso,
                'precio_compra' => $this->costoUnitarioCalculado(),
                'precio_oferta' => $this->precioOferta,
                'estado' => LotePresentacion::ESTADO_ACTIVO,
            ]);

            ProductoSucursal::create([
                'producto_id' => $producto->id,
                'sucursal_id' => $sucursalId,
                'lote_presentacion_id' => $lotePresentacion->id,
                'stock_minimo' => $this->stockMinimo,
                'precio' => $this->precioVenta,
                'minimo_mayorista' => $this->minimoMayorista,
                'precio_mayorista' => $this->precioMayorista,
                'activo' => true,
            ]);

            MovimientoInventario::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'producto_nombre' => $producto->nombre,
                'producto_presentacion_id' => $presentacion->id,
                'tipo' => $this->productoExistenteId ? 'entrada_reposicion_rapida' : 'entrada_producto_rapido',
                'cantidad' => $this->cantidadIngreso,
                'motivo' => 'Ingreso rapido de productos - lote '.$lote->codigo_lote,
                'referencia' => 'LotePresentacion:'.$lotePresentacion->id,
                'user_id' => Auth::id(),
                'stock_final' => $this->cantidadIngreso,
            ]);
        });

        $this->ultimoProducto = $producto?->nombre;
        $this->ultimoPresentacion = $presentacion?->tipo_presentacion;
        $this->ultimoCodigo = $this->codigoBarra;
        $this->ultimoPrecio = $this->precioVenta;

        Notification::make()
            ->title('Producto listo para vender')
            ->body(($producto?->nombre ?? 'Producto').' - '.($presentacion?->tipo_presentacion ?? 'Presentacion').' ya tiene stock y precio en '.$this->sucursalNombre.'.')
            ->success()
            ->send();

        $this->limpiarFormulario();
    }

    public function limpiarFormulario(): void
    {
        $ultimoProducto = $this->ultimoProducto;
        $ultimoPresentacion = $this->ultimoPresentacion;
        $ultimoCodigo = $this->ultimoCodigo;
        $ultimoPrecio = $this->ultimoPrecio;

        $this->reset();

        $this->ultimoProducto = $ultimoProducto;
        $this->ultimoPresentacion = $ultimoPresentacion;
        $this->ultimoCodigo = $ultimoCodigo;
        $this->ultimoPrecio = $ultimoPrecio;
        $this->unidadMedidaId = $this->unidadDefaultId();
        $this->tipoPresentacion = 'Unidad';
        $this->cantidadPresentacion = 1;
        $this->afectoIgv = true;
        $this->cantidadIngreso = 1;
        $this->codigoLote = $this->generarCodigoLote();
        $this->stockMinimo = 5;
        $this->minimoMayorista = 12;
        $this->crearNuevaPresentacion = true;
        $this->productoSearchResults = [];
        $this->presentacionesDisponibles = [];
        $this->highlightedIndex = -1;
    }

    public function getUrlVentaProperty(): string
    {
        return DocumentoResource::getUrl('registrar');
    }

    protected function rules(): array
    {
        $barcodeRule = 'nullable|string|max:100';
        $existingBar = null;

        if (filled($this->codigoBarra)) {
            $existingBar = ProductoPresentacionBarra::where('codigo_barra', trim((string) $this->codigoBarra))->first();
        }

        if ($existingBar && (int) $existingBar->producto_presentacion_id !== (int) $this->presentacionExistenteId) {
            $barcodeRule .= '|unique:producto_presentacion_barras,codigo_barra';
        }

        return [
            'codigoBarra' => $barcodeRule,
            'nombre' => 'required|string|max:255',
            'categoriaId' => 'nullable|exists:categorias,id',
            'nuevaCategoria' => 'nullable|string|max:255',
            'marcaId' => 'nullable|exists:marcas,id',
            'nuevaMarca' => 'nullable|string|max:255',
            'unidadMedidaId' => 'required|exists:unidades_medida,id',
            'tipoPresentacion' => 'required|string|max:255',
            'cantidadPresentacion' => 'required|integer|min:1|max:100000',
            'presentacionBaseId' => 'nullable|exists:producto_presentacion,id',
            'presentacionExistenteId' => $this->productoExistenteId && ! $this->crearNuevaPresentacion ? 'required|exists:producto_presentacion,id' : 'nullable',
            'cantidadIngreso' => 'required|integer|min:1|max:1000000',
            'codigoLote' => 'nullable|string|max:100',
            'fechaVencimiento' => 'nullable|date',
            'ubicacion' => 'nullable|string|max:255',
            'stockMinimo' => 'required|integer|min:0|max:1000000',
            'totalPagado' => 'required|numeric|min:0|max:999999999.99',
            'precioVenta' => 'required|numeric|min:0.01|max:999999.99',
            'precioOferta' => 'nullable|numeric|min:0.01|max:999999.99|lte:precioVenta',
            'precioMayorista' => 'nullable|numeric|min:0.01|max:999999.99',
            'minimoMayorista' => 'required|integer|min:1|max:1000000',
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'Escribe o selecciona el producto.',
            'unidadMedidaId.required' => 'Selecciona una unidad.',
            'tipoPresentacion.required' => 'Indica como se vendera.',
            'presentacionExistenteId.required' => 'Selecciona la presentacion que recibira el stock.',
            'cantidadIngreso.required' => 'Indica cuantas unidades ingresan.',
            'totalPagado.required' => 'Coloca el total pagado por este ingreso.',
            'precioVenta.required' => 'Coloca el precio de venta.',
            'precioVenta.min' => 'El precio de venta debe ser mayor a cero.',
            'codigoBarra.unique' => 'Ese codigo ya pertenece a otra presentacion.',
            'precioOferta.lte' => 'La oferta no debe superar el precio de venta.',
        ];
    }

    protected function hidratarProductoExistente(Producto $producto): void
    {
        $producto->load(['presentaciones.unidadMedida', 'presentaciones.barras']);

        $this->productoExistenteId = $producto->id;
        $this->productoExistenteNombre = $producto->nombre;
        $this->nombre = $producto->nombre;
        $this->categoriaId = $producto->categoria_id;
        $this->marcaId = $producto->marca_id;
        $this->nuevaCategoria = null;
        $this->nuevaMarca = null;
        $this->afectoIgv = (bool) $producto->afecto_igv;
        $this->presentacionesDisponibles = $producto->presentaciones
            ->map(fn (ProductoPresentacion $presentacion): array => [
                'id' => $presentacion->id,
                'nombre' => $this->nombrePresentacion($presentacion),
                'cantidad' => (int) $presentacion->cantidad,
                'unidad' => $presentacion->unidadMedida?->abreviatura,
                'barras' => $presentacion->barras->pluck('codigo_barra')->values()->all(),
            ])
            ->values()
            ->all();

        if ($this->presentacionesDisponibles === []) {
            $this->crearNuevaPresentacion = true;
            $this->presentacionExistenteId = null;
            return;
        }

        $this->crearNuevaPresentacion = false;
        $this->presentacionExistenteId = $this->presentacionExistenteId ?: $this->presentacionesDisponibles[0]['id'];
        $this->cargarPresentacionSeleccionada();
    }

    protected function registrarCodigoBarraSiCorresponde(ProductoPresentacion $presentacion): void
    {
        if (blank($this->codigoBarra)) {
            return;
        }

        ProductoPresentacionBarra::firstOrCreate([
            'codigo_barra' => trim((string) $this->codigoBarra),
        ], [
            'producto_presentacion_id' => $presentacion->id,
        ]);
    }

    protected function seleccionarPorCodigoBarraExacto(string $codigo): bool
    {
        $barra = ProductoPresentacionBarra::query()
            ->with('productoPresentacion.producto')
            ->where('codigo_barra', $codigo)
            ->first();

        $presentacion = $barra?->productoPresentacion;
        $producto = $presentacion?->producto;

        if (! $producto || $producto->empresa_id !== Auth::user()->empresa_id) {
            return false;
        }

        $this->codigoBarra = $codigo;
        $this->hidratarProductoExistente($producto);
        $this->crearNuevaPresentacion = false;
        $this->presentacionExistenteId = $presentacion->id;
        $this->cargarPresentacionSeleccionada();
        $this->productoSearchResults = [];

        return true;
    }

    protected function costoUnitarioCalculado(): float
    {
        if (! is_numeric($this->totalPagado) || (int) $this->cantidadIngreso <= 0) {
            return is_numeric($this->precioCompra) ? (float) $this->precioCompra : 0.0;
        }

        return round((float) $this->totalPagado / max(1, (int) $this->cantidadIngreso), 4);
    }

    protected function resolverCategoria(int $empresaId): ?int
    {
        if (filled($this->nuevaCategoria)) {
            return Categoria::firstOrCreate([
                'empresa_id' => $empresaId,
                'nombre' => trim((string) $this->nuevaCategoria),
            ], [
                'estado' => true,
            ])->id;
        }

        return $this->categoriaId;
    }

    protected function resolverMarca(int $empresaId): ?int
    {
        if (filled($this->nuevaMarca)) {
            return Marca::firstOrCreate([
                'empresa_id' => $empresaId,
                'nombre' => trim((string) $this->nuevaMarca),
            ])->id;
        }

        return $this->marcaId;
    }

    protected function generarSlug(string $nombre): string
    {
        $base = Str::slug($nombre) ?: 'producto';
        $slug = $base;
        $counter = 1;

        while (Producto::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function generarCodigoInterno(): string
    {
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', (string) $this->nombre) ?: 'PROD';
        $base = strtoupper(substr($cleanName, 0, 8));
        $codigo = $base.str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
        $counter = 1;

        while (Producto::where('codigo_interno', $codigo)->exists()) {
            $codigo = $base.str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT).'-'.$counter;
            $counter++;
        }

        return $codigo;
    }

    protected function generarCodigoLote(): string
    {
        $sucursalId = $this->sucursalId() ?: 0;
        $base = 'FOOD-'.now()->format('Ymd').'-';
        $next = str_pad((string) (Lote::where('sucursal_id', $sucursalId)->where('codigo_lote', 'like', $base.'%')->count() + 1), 4, '0', STR_PAD_LEFT);
        $codigo = $base.$next;

        while (Lote::where('sucursal_id', $sucursalId)->where('codigo_lote', $codigo)->exists()) {
            $next = str_pad((string) ((int) $next + 1), 4, '0', STR_PAD_LEFT);
            $codigo = $base.$next;
        }

        return $codigo;
    }

    protected function nombrePresentacion(ProductoPresentacion $presentacion): string
    {
        $unidad = $presentacion->unidadMedida?->abreviatura;
        $cantidad = (int) $presentacion->cantidad;

        return trim(($presentacion->tipo_presentacion ?: 'Presentacion').' · '.$cantidad.($unidad ? ' '.$unidad : ''));
    }

    protected function unidadDefaultId(): ?int
    {
        return UniMedida::query()
            ->where('activo', true)
            ->where('abreviatura', 'und')
            ->value('id') ?? UniMedida::query()->where('activo', true)->value('id');
    }

    protected function sucursalId(): ?int
    {
        return app(SucursalContext::class)->resolveSucursalForWrite();
    }

    protected function puedeIngresar(): bool
    {
        $user = Auth::user();

        return $user?->can('productos.crear')
            || $user?->can('compras.crear')
            || $user?->can('ventas.crear')
            || false;
    }

    public function render()
    {
        return view('livewire.productos.ingreso-rapido-producto');
    }
}
