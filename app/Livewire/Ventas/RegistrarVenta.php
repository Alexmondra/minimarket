<?php

namespace App\Livewire\Ventas;

use App\Models\Cliente;
use App\Models\ProductoSucursal;
use App\Support\SucursalContext;
use App\Support\Ventas\CajaService;
use App\Support\Ventas\PuntosService;
use App\Support\Ventas\RegistrarVenta as RegistrarVentaAction;
use App\Support\Ventas\VentaCalculator;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

trait RegistrarVentaBehavior
{
    public string $posTheme = 'light';

    public ?int $selectedCategoriaId = null;

    public array $categorias = [];

    public ?int $sucursalId = null;

    public string $tipoComprobante = 'TICKET';

    public string $medioPago = 'EFECTIVO';

    public string $tipoMoneda = 'PEN';

    public float $porcentajeIgv = 18.0;

    public ?float $montoRecibido = null;

    public ?string $referenciaPago = null;

    public ?string $observaciones = null;

    public string $searchProducto = '';

    public array $productosResultados = [];

    public bool $showProductoDropdown = false;

    public array $cartItems = [];

    public string $clienteTipoDocumento = 'DNI';

    public string $clienteDocumento = '';

    public ?string $clienteNombre = null;

    public ?string $clienteApellido = null;

    public ?string $clienteRazonSocial = null;

    public ?string $clienteTelefono = null;

    public ?string $clienteEmail = null;

    public ?string $clienteDireccion = null;

    public ?int $clienteId = null;

    public string $searchCliente = '';

    public array $clientesResultados = [];

    public bool $showClienteDropdown = false;

    public bool $usarPuntos = false;

    public int $puntosCanjear = 0;

    public int $puntosDisponibles = 0;

    public function mountRegistrarVenta(): void
    {
        $context = app(SucursalContext::class);
        $context->normalizeSession(Auth::user());

        $this->sucursalId = $context->resolveSucursalForWrite();
        $activeSucursal = $context->activeSucursal() ?: ($this->sucursalId ? \App\Models\Sucursal::with('ubigeoRel')->find($this->sucursalId) : null);
        if ($activeSucursal) {
            $activeSucursal->loadMissing('ubigeoRel');
            $exempt = ['LORETO', 'MADRE DE DIOS', 'UCAYALI', 'SAN MARTIN', 'AMAZONAS'];
            $departamento = $activeSucursal->ubigeoRel ? strtoupper(trim($activeSucursal->ubigeoRel->departamento)) : '';
            if (in_array($departamento, $exempt) || (float) $activeSucursal->impuesto_porcentaje === 0.0) {
                $this->porcentajeIgv = 0.0;
            } else {
                $this->porcentajeIgv = 18.0;
            }
        } else {
            $this->porcentajeIgv = 18.0;
        }
        $this->montoRecibido = 0;

        $this->categorias = \App\Models\Categoria::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('estado', true)
            ->get()
            ->toArray();
    }

    public function updatedTipoComprobante(string $value): void
    {
        if ($value === 'FACTURA') {
            $this->clienteTipoDocumento = 'RUC';
        } elseif ($this->clienteTipoDocumento === 'RUC') {
            $this->clienteTipoDocumento = 'DNI';
        }
    }

    public function cambiarTipoComprobante(string $tipo): void
    {
        $this->tipoComprobante = $tipo;
        $this->updatedTipoComprobante($tipo);
    }

    public function updatedMedioPago(string $value): void
    {
        if (! in_array($value, RegistrarVentaAction::MEDIOS_PAGO_CONTADO, true)) {
            $this->medioPago = 'EFECTIVO';

            return;
        }

        if ($value !== 'EFECTIVO') {
            $resumen = $this->getResumenProperty();
            $this->montoRecibido = round((float) $resumen['totales']['total_neto'], 2);
        } else {
            $this->montoRecibido = 0.0;
        }
    }

    public function cambiarMedioPago(string $medio): void
    {
        if (! in_array($medio, RegistrarVentaAction::MEDIOS_PAGO_CONTADO, true)) {
            return;
        }

        $this->medioPago = $medio;
        $this->updatedMedioPago($medio);
    }

    public function updatedSearchCliente(): void
    {
        $term = trim($this->searchCliente);

        if (strlen($term) < 2) {
            $this->clientesResultados = [];
            $this->showClienteDropdown = false;

            return;
        }

        $this->clientesResultados = Cliente::query()
            ->where(function ($query) use ($term): void {
                $query->where('documento', 'like', "%{$term}%")
                    ->orWhere('nombre', 'like', "%{$term}%")
                    ->orWhere('apellido', 'like', "%{$term}%")
                    ->orWhere('razon_social', 'like', "%{$term}%")
                    ->orWhere('telefono', 'like', "%{$term}%");
            })
            ->orderByRaw('documento = ? desc', [$term])
            ->limit(8)
            ->get()
            ->map(fn (Cliente $cliente): array => [
                'id' => $cliente->id,
                'tipo_documento' => $cliente->tipo_documento,
                'documento' => $cliente->documento,
                'nombre_completo' => $cliente->razon_social ?: trim(($cliente->nombre ?? '').' '.($cliente->apellido ?? '')),
                'telefono' => $cliente->telefono,
            ])
            ->all();

        $this->showClienteDropdown = count($this->clientesResultados) > 0;
    }

    public function seleccionarCliente(int $clienteId): void
    {
        $cliente = Cliente::query()->find($clienteId);

        if (! $cliente) {
            return;
        }

        $this->clienteId = $cliente->id;
        $this->clienteTipoDocumento = $cliente->tipo_documento ?: 'DNI';
        $this->clienteDocumento = $cliente->documento ?? '';
        $this->clienteNombre = $cliente->nombre;
        $this->clienteApellido = $cliente->apellido;
        $this->clienteRazonSocial = $cliente->razon_social;
        $this->clienteTelefono = $cliente->telefono;
        $this->clienteEmail = $cliente->email;
        $this->clienteDireccion = $cliente->direccion;
        $this->puntosDisponibles = app(PuntosService::class)->puntosDisponibles($cliente, Auth::user()->empresa_id);
        $this->searchCliente = '';
        $this->clientesResultados = [];
        $this->showClienteDropdown = false;
    }

    public function limpiarCliente(): void
    {
        $this->clienteDocumento = '';
        $this->searchCliente = '';
        $this->clientesResultados = [];
        $this->showClienteDropdown = false;
        $this->resetClienteData();
    }

    public function updatedClienteDocumento(): void
    {
        $documento = trim($this->clienteDocumento);

        if ($documento === '') {
            $this->resetClienteData();

            return;
        }

        if (strlen($documento) === 11) {
            $this->clienteTipoDocumento = 'RUC';
        } elseif (strlen($documento) === 8) {
            $this->clienteTipoDocumento = 'DNI';
        }

        $cliente = Cliente::query()
            ->where('documento', $documento)
            ->where('tipo_documento', $this->clienteTipoDocumento)
            ->first();

        if (! $cliente) {
            $this->clienteId = null;
            $this->clienteNombre = null;
            $this->clienteApellido = null;
            $this->clienteRazonSocial = null;
            $this->puntosDisponibles = 0;

            return;
        }

        $this->clienteId = $cliente->id;
        $this->clienteNombre = $cliente->nombre;
        $this->clienteApellido = $cliente->apellido;
        $this->clienteRazonSocial = $cliente->razon_social;
        $this->clienteTelefono = $cliente->telefono;
        $this->clienteEmail = $cliente->email;
        $this->clienteDireccion = $cliente->direccion;
        $this->puntosDisponibles = app(PuntosService::class)->puntosDisponibles($cliente, Auth::user()->empresa_id);
    }

    public function updatedPuntosCanjear($value): void
    {
        $this->puntosCanjear = max(min((int) $value, $this->puntosDisponibles), 0);
    }

    public function updatedSearchProducto(): void
    {
        $term = trim($this->searchProducto);

        if (strlen($term) < 2 || ! $this->sucursalId) {
            $this->productosResultados = [];
            $this->showProductoDropdown = false;

            return;
        }

        // Búsqueda de coincidencia exacta por código de barra o código interno
        if (strlen($term) >= 3) {
            $exactMatch = ProductoSucursal::query()
                ->where('sucursal_id', $this->sucursalId)
                ->where('activo', true)
                ->whereHas('producto', function ($query) {
                    $query->where('empresa_id', \Illuminate\Support\Facades\Auth::user()->empresa_id)
                        ->where('activo', true);
                })
                ->where(function ($query) use ($term) {
                    $query->whereHas('producto', function ($q) use ($term) {
                        $q->where('codigo_interno', $term);
                    })->orWhereHas('lotePresentacion.productoPresentacion', function ($q) use ($term) {
                        $q->where('codigo_barra', $term);
                    });
                })
                ->whereHas('lotePresentacion', fn ($q) => $q->where('stock', '>', 0))
                ->first();

            if ($exactMatch && $exactMatch->lotePresentacion?->producto_presentacion_id) {
                $this->agregarProducto($exactMatch->lotePresentacion->producto_presentacion_id);
                return;
            }
        }

        $rows = ProductoSucursal::query()
            ->with([
                'producto',
                'lotePresentacion.lote',
                'lotePresentacion.productoPresentacion.unidadMedida',
                'lotePresentacion.productoPresentacion.producto',
            ])
            ->where('sucursal_id', $this->sucursalId)
            ->where('activo', true)
            ->whereHas('producto', function ($query) {
                $query->where('empresa_id', Auth::user()->empresa_id)
                    ->where('activo', true);
            })
            ->whereHas('lotePresentacion', fn ($query) => $query->where('stock', '>', 0))
            ->where(function ($query) use ($term) {
                $query->whereHas('producto', function ($productQuery) use ($term) {
                    $productQuery->where('nombre', 'like', "%{$term}%")
                        ->orWhere('codigo_interno', 'like', "%{$term}%");
                })->orWhereHas('lotePresentacion.productoPresentacion', function ($presentationQuery) use ($term) {
                    $presentationQuery->where('codigo_barra', 'like', "%{$term}%")
                        ->orWhere('tipo_presentacion', 'like', "%{$term}%");
                });
            })
            ->limit(40)
            ->get()
            ->groupBy(fn (ProductoSucursal $row) => $row->lotePresentacion?->producto_presentacion_id);

        $this->productosResultados = $rows->map(function ($group): ?array {
            /** @var ProductoSucursal|null $first */
            $first = $group->sortByDesc('id')->first();
            $presentacion = $first?->lotePresentacion?->productoPresentacion;
            $producto = $first?->producto;

            if (! $first || ! $presentacion || ! $producto) {
                return null;
            }

            $stock = (float) $group->sum(fn (ProductoSucursal $item) => $item->stock);
            $precioOferta = $group->first(fn (ProductoSucursal $item) => $item->lotePresentacion?->precio_oferta !== null)?->lotePresentacion?->precio_oferta;
            $precio = $precioOferta !== null ? (float) $precioOferta : (float) $first->precio;

            return [
                'producto_presentacion_id' => $presentacion->id,
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $presentacion->codigo_barra ?: $producto->codigo_interno,
                'presentacion' => $presentacion->tipo_presentacion ?: 'Presentacion',
                'unidad' => $presentacion->unidadMedida?->abreviatura ?? 'und',
                'cantidad_presentacion' => $presentacion->cantidad ?? 1,
                'stock' => $stock,
                'precio' => $precio,
                'precio_regular' => (float) $first->precio,
                'precio_mayorista' => $first->precio_mayorista !== null ? (float) $first->precio_mayorista : null,
                'minimo_mayorista' => (int) $first->minimo_mayorista,
                'afecto_igv' => (bool) $producto->afecto_igv,
            ];
        })
            ->filter()
            ->sortByDesc(fn (array $item) => $item['codigo'] === $this->searchProducto)
            ->take(10)
            ->values()
            ->all();

        $this->showProductoDropdown = count($this->productosResultados) > 0;
    }

    protected function obtenerDetalleProducto(int $presentacionId): ?array
    {
        if (! $this->sucursalId) {
            return null;
        }

        $group = ProductoSucursal::query()
            ->with([
                'producto',
                'lotePresentacion.lote',
                'lotePresentacion.productoPresentacion.unidadMedida',
                'lotePresentacion.productoPresentacion.producto',
            ])
            ->where('sucursal_id', $this->sucursalId)
            ->where('activo', true)
            ->whereHas('producto', function ($query) {
                $query->where('empresa_id', Auth::user()->empresa_id)
                    ->where('activo', true);
            })
            ->whereHas('lotePresentacion', fn ($query) => $query->where('producto_presentacion_id', $presentacionId))
            ->whereHas('lotePresentacion', fn ($query) => $query->where('stock', '>', 0))
            ->get();

        if ($group->isEmpty()) {
            return null;
        }

        $first = $group->sortByDesc('id')->first();
        $presentacion = $first?->lotePresentacion?->productoPresentacion;
        $producto = $first?->producto;

        if (! $first || ! $presentacion || ! $producto) {
            return null;
        }

        $stock = (float) $group->sum(fn (ProductoSucursal $item) => $item->stock);
        $precioOferta = $group->first(fn (ProductoSucursal $item) => $item->lotePresentacion?->precio_oferta !== null)?->lotePresentacion?->precio_oferta;
        $precio = $precioOferta !== null ? (float) $precioOferta : (float) $first->precio;

        return [
            'producto_presentacion_id' => $presentacion->id,
            'producto_id' => $producto->id,
            'nombre' => $producto->nombre,
            'codigo' => $presentacion->codigo_barra ?: $producto->codigo_interno,
            'presentacion' => $presentacion->tipo_presentacion ?: 'Presentación',
            'unidad' => $presentacion->unidadMedida?->abreviatura ?? 'und',
            'cantidad_presentacion' => $presentacion->cantidad ?? 1,
            'stock' => $stock,
            'precio' => $precio,
            'precio_regular' => (float) $first->precio,
            'precio_mayorista' => $first->precio_mayorista !== null ? (float) $first->precio_mayorista : null,
            'minimo_mayorista' => (int) $first->minimo_mayorista,
            'afecto_igv' => (bool) $producto->afecto_igv,
        ];
    }

    public function agregarProducto(int $presentacionId): void
    {
        $producto = $this->obtenerDetalleProducto($presentacionId);

        if (! $producto) {
            Notification::make()->title('Producto sin stock disponible')->warning()->send();

            return;
        }

        $index = collect($this->cartItems)
            ->search(fn (array $item): bool => $item['producto_presentacion_id'] === $presentacionId);

        if ($index !== false) {
            $this->cartItems[$index]['cantidad'] = min(
                round((float) $this->cartItems[$index]['cantidad'] + 1, 3),
                (float) $this->cartItems[$index]['stock']
            );
            $this->recalcularPrecio($index);
        } else {
            $this->cartItems[] = [
                ...$producto,
                'cantidad' => 1,
            ];
            $this->recalcularPrecio(array_key_last($this->cartItems));
        }

        $this->searchProducto = '';
        $this->productosResultados = [];
        $this->showProductoDropdown = false;
    }

    public function actualizarCantidad(int $index, $cantidad): void
    {
        if (! isset($this->cartItems[$index])) {
            return;
        }

        $cantidad = max(min((float) $cantidad, (float) $this->cartItems[$index]['stock']), 0.001);
        $this->cartItems[$index]['cantidad'] = round($cantidad, 3);
        $this->recalcularPrecio($index);
    }

    public function quitarItem(int $index): void
    {
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);
    }

    public function incrementarCantidad(int $index): void
    {
        if (! isset($this->cartItems[$index])) {
            return;
        }

        $nuevaCantidad = (float) $this->cartItems[$index]['cantidad'] + 1;
        $this->actualizarCantidad($index, $nuevaCantidad);
    }

    public function decrementarCantidad(int $index): void
    {
        if (! isset($this->cartItems[$index])) {
            return;
        }

        $nuevaCantidad = (float) $this->cartItems[$index]['cantidad'] - 1;
        if ($nuevaCantidad <= 0) {
            $this->quitarItem($index);
        } else {
            $this->actualizarCantidad($index, $nuevaCantidad);
        }
    }

    public function agregarMontoEfectivo(float $monto): void
    {
        $this->montoRecibido = round(((float) $this->montoRecibido) + $monto, 2);
    }

    public function establecerPagoExacto(): void
    {
        $resumen = $this->getResumenProperty();
        $this->montoRecibido = round((float) $resumen['totales']['total_neto'], 2);
    }

    public function guardarVenta(): void
    {
        if (! $this->sucursalId) {
            Notification::make()->title('Selecciona una sucursal')->warning()->send();

            return;
        }

        if (empty($this->cartItems)) {
            Notification::make()->title('Agrega al menos un producto')->warning()->send();

            return;
        }

        if (! $this->cajaAbierta) {
            Notification::make()->title('No hay una caja abierta para esta sucursal')->danger()->send();

            return;
        }

        if ($this->tipoComprobante === 'FACTURA' && strlen(trim($this->clienteDocumento)) !== 11) {
            Notification::make()->title('La factura requiere RUC del cliente')->danger()->send();

            return;
        }

        if ($this->usarPuntos && $this->puntosCanjear > $this->puntosDisponibles) {
            Notification::make()->title('Los puntos canjeados superan el saldo disponible')->danger()->send();

            return;
        }

        $resumen = $this->getResumenProperty();

        if ($this->medioPago !== 'EFECTIVO') {
            $this->montoRecibido = round((float) $resumen['totales']['total_neto'], 2);
        } elseif ((float) $this->montoRecibido < (float) $resumen['totales']['total_neto']) {
            Notification::make()->title('El monto recibido no cubre el total de la venta')->danger()->send();

            return;
        }

        try {
            $documento = app(RegistrarVentaAction::class)->ejecutar(Auth::user(), [
                'sucursal_id' => $this->sucursalId,
                'tipo_comprobante' => $this->tipoComprobante,
                'medio_pago' => $this->medioPago,
                'tipo_moneda' => $this->tipoMoneda,
                'monto_recibido' => $this->montoRecibido,
                'referencia_pago' => $this->referenciaPago,
                'observaciones' => $this->observaciones,
                'porcentaje_igv' => $this->porcentajeIgv,
                'puntos_canjeados' => $this->usarPuntos ? $this->puntosCanjear : 0,
                'cliente' => [
                    'tipo_documento' => $this->clienteTipoDocumento,
                    'documento' => $this->clienteDocumento,
                    'nombre' => $this->clienteNombre,
                    'apellido' => $this->clienteApellido,
                    'razon_social' => $this->clienteRazonSocial,
                    'telefono' => $this->clienteTelefono,
                    'email' => $this->clienteEmail,
                    'direccion' => $this->clienteDireccion,
                ],
                'items' => collect($this->cartItems)->map(fn (array $item) => [
                    'producto_presentacion_id' => $item['producto_presentacion_id'],
                    'cantidad' => (float) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio'],
                ])->values()->all(),
            ]);

            Notification::make()
                ->title("Venta {$documento->serie}-{$documento->numero} registrada")
                ->success()
                ->send();

            $this->redirect(route('filament.admin.resources.documentos.view', ['record' => $documento]));
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title($e->getMessage() ?: 'No se pudo registrar la venta.')
                ->danger()
                ->send();
        }
    }

    public function getCajaAbiertaProperty()
    {
        if (! $this->sucursalId) {
            return null;
        }

        return app(CajaService::class)->cajaAbierta(Auth::id(), $this->sucursalId);
    }

    public function getCanSaveProperty(): bool
    {
        if (! $this->sucursalId) {
            return false;
        }

        if (empty($this->cartItems)) {
            return false;
        }

        if (! $this->cajaAbierta) {
            return false;
        }

        if ($this->tipoComprobante === 'FACTURA' && strlen(trim($this->clienteDocumento)) !== 11) {
            return false;
        }

        $resumen = $this->resumen;
        $totalNeto = (float) ($resumen['totales']['total_neto'] ?? 0.0);

        if ($this->medioPago === 'EFECTIVO' && ((float) $this->montoRecibido) < $totalNeto) {
            return false;
        }

        return true;
    }

    public function getPreciosIncluyenImpuestoProperty(): bool
    {
        return (bool) (Auth::user()?->empresa?->incluido_tributo ?? false);
    }

    public function getResumenProperty(): array
    {
        return app(VentaCalculator::class)->calcular(
            collect($this->cartItems)->map(fn (array $item) => [
                'cantidad' => (float) $item['cantidad'],
                'precio_unitario' => (float) $item['precio'],
                'afecto_igv' => (bool) $item['afecto_igv'],
            ])->all(),
            $this->preciosIncluyenImpuesto,
            $this->porcentajeIgv,
            $this->usarPuntos ? app(PuntosService::class)->descuentoPorPuntos($this->puntosCanjear) : 0
        );
    }

    protected function recalcularPrecio(int $index): void
    {
        if (! isset($this->cartItems[$index])) {
            return;
        }

        $item = $this->cartItems[$index];
        $precio = $item['precio_regular'];

        if ($item['precio_mayorista'] !== null && $item['cantidad'] >= $item['minimo_mayorista']) {
            $precio = $item['precio_mayorista'];
        }

        $this->cartItems[$index]['precio'] = round((float) $precio, 2);
    }

    protected function resetClienteData(): void
    {
        $this->clienteId = null;
        $this->clienteNombre = null;
        $this->clienteApellido = null;
        $this->clienteRazonSocial = null;
        $this->clienteTelefono = null;
        $this->clienteEmail = null;
        $this->clienteDireccion = null;
        $this->puntosDisponibles = 0;
        $this->puntosCanjear = 0;
        $this->usarPuntos = false;
    }

    public function toggleTheme(): void
    {
        $this->posTheme = $this->posTheme === 'light' ? 'dark' : 'light';
    }

    public function seleccionarCategoria(?int $categoriaId): void
    {
        $this->selectedCategoriaId = $categoriaId;
    }

    public function vaciarCarrito(): void
    {
        $this->cartItems = [];
        $this->montoRecibido = 0;
        Notification::make()->title('Carrito vaciado')->info()->send();
    }

    public function cancelarVenta(): void
    {
        $this->cartItems = [];
        $this->resetClienteData();
        $this->montoRecibido = 0;
        $this->clienteDocumento = '';
        $this->searchProducto = '';
        $this->selectedCategoriaId = null;
        Notification::make()->title('Venta cancelada')->warning()->send();
    }

    public function agregarCliente(): void
    {
        $documento = trim($this->clienteDocumento);
        if ($documento === '') {
            Notification::make()->title('Ingresa el número de documento')->warning()->send();
            return;
        }

        try {
            $cliente = Cliente::updateOrCreate(
                [
                    'tipo_documento' => $this->clienteTipoDocumento,
                    'documento' => $documento,
                ],
                [
                    'nombre' => $this->clienteTipoDocumento === 'RUC'
                        ? ($this->clienteRazonSocial ?: 'Cliente')
                        : ($this->clienteNombre ?: 'Cliente'),
                    'apellido' => $this->clienteTipoDocumento === 'RUC' ? null : $this->clienteApellido,
                    'razon_social' => $this->clienteTipoDocumento === 'RUC' ? $this->clienteRazonSocial : null,
                    'telefono' => $this->clienteTelefono,
                    'email' => $this->clienteEmail,
                    'direccion' => $this->clienteDireccion,
                ]
            );

            $this->clienteId = $cliente->id;
            $this->puntosDisponibles = app(PuntosService::class)->puntosDisponibles($cliente, Auth::user()->empresa_id);
            
            Notification::make()->title('Cliente registrado con éxito')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Error al guardar cliente: ' . $e->getMessage())->danger()->send();
        }
    }

    public function getProductosCategoriaProperty(): array
    {
        if (! $this->sucursalId) {
            return [];
        }

        $query = ProductoSucursal::query()
            ->with([
                'producto',
                'lotePresentacion.lote',
                'lotePresentacion.productoPresentacion.unidadMedida',
                'lotePresentacion.productoPresentacion.producto',
            ])
            ->where('sucursal_id', $this->sucursalId)
            ->where('activo', true)
            ->whereHas('lotePresentacion', fn ($query) => $query->where('stock', '>', 0))
            ->whereHas('producto', function ($q) {
                $q->where('empresa_id', Auth::user()->empresa_id)
                    ->where('activo', true);
            });

        if ($this->selectedCategoriaId) {
            $query->whereHas('producto', function ($q) {
                $q->where('categoria_id', $this->selectedCategoriaId);
            });
        }

        $rows = $query->get()->groupBy(fn (ProductoSucursal $row) => $row->lotePresentacion?->producto_presentacion_id);

        return $rows->map(function ($group): ?array {
            /** @var ProductoSucursal|null $first */
            $first = $group->sortByDesc('id')->first();
            $presentacion = $first?->lotePresentacion?->productoPresentacion;
            $producto = $first?->producto;

            if (! $first || ! $presentacion || ! $producto) {
                return null;
            }

            $stock = (float) $group->sum(fn (ProductoSucursal $item) => $item->stock);
            $precioOferta = $group->first(fn (ProductoSucursal $item) => $item->lotePresentacion?->precio_oferta !== null)?->lotePresentacion?->precio_oferta;
            $precio = $precioOferta !== null ? (float) $precioOferta : (float) $first->precio;

            return [
                'producto_presentacion_id' => $presentacion->id,
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo' => $presentacion->codigo_barra ?: $producto->codigo_interno,
                'presentacion' => $presentacion->tipo_presentacion ?: 'Presentación',
                'unidad' => $presentacion->unidadMedida?->abreviatura ?? 'und',
                'cantidad_presentacion' => $presentacion->cantidad ?? 1,
                'stock' => $stock,
                'precio' => $precio,
                'precio_regular' => (float) $first->precio,
                'precio_mayorista' => $first->precio_mayorista !== null ? (float) $first->precio_mayorista : null,
                'minimo_mayorista' => (int) $first->minimo_mayorista,
                'afecto_igv' => (bool) $producto->afecto_igv,
            ];
        })
        ->filter()
        ->values()
        ->all();
    }

}

class RegistrarVenta extends Component
{
    use RegistrarVentaBehavior;

    public function mount(): void
    {
        $this->mountRegistrarVenta();
    }

    public function render()
    {
        return view('livewire.ventas.registrar-venta');
    }
}
