<?php

namespace App\Livewire\Ventas;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\ProductoSucursal;
use App\Models\Sucursal;
use App\Support\SucursalContext;
use App\Support\Ventas\AnulacionService;
use App\Support\Ventas\CajaService;
use App\Support\Ventas\PuntosService;
use App\Support\Ventas\RegistrarVenta as RegistrarVentaAction;
use App\Support\Ventas\VentaCalculator;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

trait RegistrarVentaBehavior
{
    public string $posTheme = 'light';

    public ?int $createdDocumentoId = null;

    public bool $showSuccessModal = false;

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

    public bool $showRegistrarClienteModal = false;

    public bool $showEditarClienteModal = false;

    public bool $usarPuntos = false;

    public int $puntosCanjear = 0;

    public int $puntosDisponibles = 0;

    public bool $showVencidoWarningModal = false;

    public ?int $pendingPresentacionId = null;

    public string $vencidoWarningMessage = '';

    public ?int $cajaActivaId = null;

    public bool $showCerrarCajaModal = false;

    public ?float $cerrarCajaSaldoReal = null;

    public string $cerrarCajaObservaciones = '';

    public bool $showBuscarVentaModal = false;

    public string $searchVentaQuery = '';

    public array $ventasResultados = [];

    public ?int $selectedVentaId = null;

    public ?array $selectedVentaDetalles = null;

    public bool $showAnularVentaModal = false;

    public ?int $anularVentaId = null;

    public ?string $anularVentaComprobante = null;

    public string $anularMotivoCodigo = '01';

    public function mountRegistrarVenta(): void
    {
        $context = app(SucursalContext::class);
        $context->normalizeSession(Auth::user());

        $this->sucursalId = $context->resolveSucursalForWrite();

        $caja = \App\Models\SessioneCaja::query()
            ->where('user_id', Auth::id())
            ->where('sucursal_id', $this->sucursalId)
            ->where('estado', true)
            ->whereNull('fecha_cierre')
            ->first();
        if ($caja) {
            $this->cajaActivaId = $caja->id;
        }
        $activeSucursal = $context->activeSucursal() ?: ($this->sucursalId ? Sucursal::with('ubigeoRel')->find($this->sucursalId) : null);
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

        $this->categorias = Categoria::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('estado', true)
            ->get()
            ->toArray();

        // Cargar estado guardado en sesión
        if (session()->has('pos_cart_items')) {
            $this->cartItems = session()->get('pos_cart_items', []);
        }
        if (session()->has('pos_medio_pago')) {
            $this->medioPago = session()->get('pos_medio_pago', 'EFECTIVO');
        }
        if (session()->has('pos_tipo_comprobante')) {
            $this->tipoComprobante = session()->get('pos_tipo_comprobante', 'TICKET');
        }
        if (session()->has('pos_cliente_id')) {
            $clienteId = session()->get('pos_cliente_id');
            if ($clienteId) {
                $this->seleccionarCliente($clienteId);
            }
        }
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

    public function buscarCliente(): void
    {
        $term = trim($this->searchCliente);
        if ($term === '') {
            return;
        }

        if (! ctype_digit($term) || (strlen($term) !== 8 && strlen($term) !== 11)) {
            Notification::make()
                ->title('Error en la digitación: El documento debe tener 8 dígitos (DNI) o 11 dígitos (RUC).')
                ->danger()
                ->send();

            return;
        }

        $tipoDoc = strlen($term) === 8 ? 'DNI' : 'RUC';
        $cliente = Cliente::query()
            ->where('documento', $term)
            ->where('tipo_documento', $tipoDoc)
            ->first();

        if ($cliente) {
            $this->seleccionarCliente($cliente->id);
            Notification::make()
                ->title('Cliente seleccionado')
                ->success()
                ->send();

            return;
        }

        // If not found in local DB, consult external API
        $key = config('services.datos.key');
        $dniUrl = config('services.datos.dni_url');
        $rucUrl = config('services.datos.ruc_url');

        if (empty($key) || ($tipoDoc === 'DNI' && empty($dniUrl)) || ($tipoDoc === 'RUC' && empty($rucUrl))) {
            Notification::make()
                ->title('Error: Configuración de API externa no encontrada.')
                ->danger()
                ->send();

            return;
        }

        $url = $tipoDoc === 'DNI' ? ($dniUrl.$term) : ($rucUrl.$term);

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders([
                    'X-API-KEY' => $key,
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                $responseData = $response->json();
                $data = $responseData['data'] ?? $responseData;

                $nombre = $data['nombres'] ?? $data['nombre'] ?? null;
                $apellidoPaterno = $data['apellido_paterno'] ?? $data['apellidoPaterno'] ?? '';
                $apellidoMaterno = $data['apellido_materno'] ?? $data['apellidoMaterno'] ?? '';
                $apellido = trim($apellidoPaterno.' '.$apellidoMaterno);
                if (empty($apellido) && isset($data['apellidos'])) {
                    $apellido = $data['apellidos'];
                }

                $razonSocial = $data['razon_social'] ?? $data['razonSocial'] ?? $data['nombre_o_razon_social'] ?? null;
                if ($tipoDoc === 'RUC' && empty($razonSocial)) {
                    $razonSocial = $data['nombre'] ?? $data['nombres'] ?? null;
                }

                $direccion = $data['direccion'] ?? $data['domicilio_fiscal'] ?? $data['direccion_completa'] ?? null;

                $hasName = ($tipoDoc === 'DNI' && ! empty($nombre)) || ($tipoDoc === 'RUC' && ! empty($razonSocial));

                if ($hasName) {
                    $cliente = Cliente::create([
                        'tipo_documento' => $tipoDoc,
                        'documento' => $term,
                        'nombre' => $tipoDoc === 'RUC' ? $razonSocial : $nombre,
                        'apellido' => $tipoDoc === 'RUC' ? null : (empty($apellido) ? null : $apellido),
                        'razon_social' => $tipoDoc === 'RUC' ? $razonSocial : null,
                        'direccion' => $direccion,
                        'telefono' => $data['telefono'] ?? null,
                        'email' => $data['correo'] ?? $data['email'] ?? null,
                    ]);

                    $this->seleccionarCliente($cliente->id);

                    Notification::make()
                        ->title('Cliente encontrado y guardado localmente.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('No se encontraron datos para el documento ingresado.')
                        ->danger()
                        ->send();
                }
            } else {
                Notification::make()
                    ->title('Error al consultar el servicio externo de datos.')
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Error al conectar con la API externa: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function abrirRegistroManual(): void
    {
        $term = trim($this->searchCliente);
        $this->resetClienteData();
        $this->clienteDocumento = $term;
        if (strlen($term) === 11) {
            $this->clienteTipoDocumento = 'RUC';
        } else {
            $this->clienteTipoDocumento = 'DNI';
        }
        $this->showRegistrarClienteModal = true;
    }

    public function registrarClienteManual(): void
    {
        $documento = trim($this->clienteDocumento);
        if ($documento === '') {
            Notification::make()->title('Ingresa el número de documento')->warning()->send();

            return;
        }

        if (! ctype_digit($documento) || (strlen($documento) !== 8 && strlen($documento) !== 11)) {
            Notification::make()
                ->title('Error en la digitación: El documento debe tener 8 dígitos (DNI) o 11 dígitos (RUC).')
                ->danger()
                ->send();

            return;
        }

        try {
            $cliente = Cliente::updateOrCreate(
                [
                    'tipo_documento' => $this->clienteTipoDocumento,
                    'documento' => $documento,
                ],
                [
                    'nombre' => $this->clienteTipoDocumento === 'RUC' ? ($this->clienteRazonSocial ?: 'Cliente') : ($this->clienteNombre ?: 'Cliente'),
                    'apellido' => $this->clienteTipoDocumento === 'RUC' ? null : $this->clienteApellido,
                    'razon_social' => $this->clienteTipoDocumento === 'RUC' ? ($this->clienteRazonSocial ?: 'Cliente') : null,
                    'telefono' => $this->clienteTelefono,
                    'email' => $this->clienteEmail,
                    'direccion' => $this->clienteDireccion,
                ]
            );

            $this->seleccionarCliente($cliente->id);
            $this->showRegistrarClienteModal = false;

            Notification::make()
                ->title('Cliente registrado con éxito')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al guardar cliente: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function abrirEdicionCliente(): void
    {
        if (! $this->clienteId) {
            return;
        }

        $cliente = Cliente::find($this->clienteId);
        if ($cliente) {
            $this->clienteTelefono = $cliente->telefono;
            $this->clienteEmail = $cliente->email;
            $this->clienteDireccion = $cliente->direccion;
        }

        $this->showEditarClienteModal = true;
    }

    public function guardarEdicionCliente(): void
    {
        if (! $this->clienteId) {
            return;
        }

        try {
            $cliente = Cliente::find($this->clienteId);
            if ($cliente) {
                $cliente->update([
                    'telefono' => $this->clienteTelefono,
                    'email' => $this->clienteEmail,
                    'direccion' => $this->clienteDireccion,
                ]);

                $this->clienteTelefono = $cliente->telefono;
                $this->clienteEmail = $cliente->email;
                $this->clienteDireccion = $cliente->direccion;

                $this->showEditarClienteModal = false;

                Notification::make()
                    ->title('Cliente actualizado con éxito')
                    ->success()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al actualizar cliente: '.$e->getMessage())
                ->danger()
                ->send();
        }
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
                    $query->where('empresa_id', Auth::user()->empresa_id)
                        ->where('activo', true);
                })
                ->where(function ($query) use ($term) {
                    $query->whereHas('producto', function ($q) use ($term) {
                        $q->where('codigo_interno', $term);
                    })->orWhereHas('lotePresentacion.productoPresentacion', function ($q) use ($term) {
                        $q->whereHas('barras', fn ($b) => $b->where('codigo_barra', $term));
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
                'lotePresentacion.productoPresentacion.barras',
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
                    $presentationQuery->whereHas('barras', fn ($b) => $b->where('codigo_barra', 'like', "%{$term}%"))
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
                'codigo' => $presentacion->barras->first()?->codigo_barra ?: $producto->codigo_interno,
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
                'lotePresentacion.productoPresentacion.barras',
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
            'codigo' => $presentacion->barras->first()?->codigo_barra ?: $producto->codigo_interno,
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
        // Buscar si existe algún lote de la presentación que esté vencido y en estado pendiente de confirmar
        $lotePendiente = \App\Models\LotePresentacion::query()
            ->where('producto_presentacion_id', $presentacionId)
            ->where('estado', \App\Models\LotePresentacion::ESTADO_PENDIENTE)
            ->whereHas('lote', function ($q) {
                $q->where('sucursal_id', $this->sucursalId);
            })
            ->with(['lote', 'productoPresentacion.producto'])
            ->first();

        if ($lotePendiente) {
            $this->pendingPresentacionId = $presentacionId;
            $this->showVencidoWarningModal = true;
            
            $productoNombre = $lotePendiente->productoPresentacion?->producto?->nombre ?? 'Producto';
            $presentacionNombre = $lotePendiente->productoPresentacion?->tipo_presentacion ?? 'Presentación';
            
            $daysExpired = 0;
            if ($lotePendiente->lote?->fecha_vencimiento) {
                $daysExpired = $lotePendiente->lote->fecha_vencimiento->startOfDay()->diffInDays(now()->startOfDay());
            }

            $this->vencidoWarningMessage = "Hay un lote de este mismo producto <strong>{$productoNombre} ({$presentacionNombre})</strong> que se ha vencido hace <strong>{$daysExpired}</strong> días, por favor verifique la fecha de vencimiento antes de vender - si ya verificaron por favor ir a lotes y confirmar el vencido (confirmar merma).";
            return;
        }

        $this->agregarProductoDirecto($presentacionId);
    }

    public function confirmarAgregarProducto(): void
    {
        if ($this->pendingPresentacionId) {
            $this->agregarProductoDirecto($this->pendingPresentacionId);
            $this->pendingPresentacionId = null;
        }
        $this->showVencidoWarningModal = false;
    }

    public function agregarProductoDirecto(int $presentacionId): void
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

            $this->createdDocumentoId = $documento->id;
            $this->showSuccessModal = true;
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
                'cantidad' => max((float) ($item['cantidad'] ?? 1.0), 0.0),
                'precio_unitario' => max((float) ($item['precio'] ?? 0.0), 0.0),
                'afecto_igv' => (bool) ($item['afecto_igv'] ?? true),
            ])->all(),
            $this->preciosIncluyenImpuesto,
            $this->porcentajeIgv,
            $this->usarPuntos ? app(PuntosService::class)->descuentoPorPuntos($this->puntosCanjear) : 0
        );
    }

    public function actualizarPrecio(int $index, $precio): void
    {
        if (! isset($this->cartItems[$index])) {
            return;
        }

        $precio = max((float) $precio, 0.0);
        $this->cartItems[$index]['precio'] = round($precio, 2);
        $this->cartItems[$index]['precio_manual'] = true;
    }

    public function procesarEnterBuscador(): void
    {
        $term = trim($this->searchProducto);
        if ($term === '') {
            return;
        }

        if ($this->sucursalId) {
            $exactMatch = ProductoSucursal::query()
                ->where('sucursal_id', $this->sucursalId)
                ->where('activo', true)
                ->whereHas('producto', function ($query) {
                    $query->where('empresa_id', Auth::user()->empresa_id)
                        ->where('activo', true);
                })
                ->where(function ($query) use ($term) {
                    $query->whereHas('producto', function ($q) use ($term) {
                        $q->where('codigo_interno', $term);
                    })->orWhereHas('lotePresentacion.productoPresentacion', function ($q) use ($term) {
                        $q->whereHas('barras', fn ($b) => $b->where('codigo_barra', $term));
                    });
                })
                ->whereHas('lotePresentacion', fn ($q) => $q->where('stock', '>', 0))
                ->first();

            if ($exactMatch && $exactMatch->lotePresentacion?->producto_presentacion_id) {
                $this->agregarProducto($exactMatch->lotePresentacion->producto_presentacion_id);
                $this->searchProducto = '';
                $this->productosResultados = [];
                $this->showProductoDropdown = false;
                return;
            }
        }

        if (! empty($this->productosResultados)) {
            $first = $this->productosResultados[0];
            $this->agregarProducto($first['producto_presentacion_id']);
            $this->searchProducto = '';
            $this->productosResultados = [];
            $this->showProductoDropdown = false;
        }
    }

    public function updatedCartItems($value, $key): void
    {
        if (str_contains($key, '.precio')) {
            $parts = explode('.', $key);
            $index = (int) $parts[0];
            if (isset($this->cartItems[$index])) {
                $this->cartItems[$index]['precio_manual'] = true;
                $val = $this->cartItems[$index]['precio'];
                $this->cartItems[$index]['precio'] = max((float) $val, 0.0);
            }
        }
    }

    public function rendering(): void
    {
        if ($this->showSuccessModal) {
            $this->limpiarSesionPOS();
        } else {
            $this->guardarSesionPOS();
        }
    }

    protected function guardarSesionPOS(): void
    {
        if (! empty($this->cartItems)) {
            session()->put('pos_cart_items', $this->cartItems);
            session()->put('pos_cliente_id', $this->clienteId);
            session()->put('pos_medio_pago', $this->medioPago);
            session()->put('pos_tipo_comprobante', $this->tipoComprobante);
        } else {
            $this->limpiarSesionPOS();
        }
    }

    protected function limpiarSesionPOS(): void
    {
        session()->forget([
            'pos_cart_items',
            'pos_cliente_id',
            'pos_medio_pago',
            'pos_tipo_comprobante',
        ]);
    }

    protected function recalcularPrecio(int $index): void
    {
        if (! isset($this->cartItems[$index])) {
            return;
        }

        $item = $this->cartItems[$index];

        if (isset($item['precio_manual']) && $item['precio_manual']) {
            return;
        }

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
            Notification::make()->title('Error al guardar cliente: '.$e->getMessage())->danger()->send();
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
                'lotePresentacion.productoPresentacion.barras',
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
                'codigo' => $presentacion->barras->first()?->codigo_barra ?: $producto->codigo_interno,
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

    public function toggleMedioPagoShortcut(): void
    {
        $medios = RegistrarVentaAction::MEDIOS_PAGO_CONTADO;
        $currentIdx = array_search($this->medioPago, $medios, true);
        if ($currentIdx === false) {
            $nextIdx = 0;
        } else {
            $nextIdx = ($currentIdx + 1) % count($medios);
        }
        $this->cambiarMedioPago($medios[$nextIdx]);

        Notification::make()
            ->title('Medio de pago cambiado a: '.$medios[$nextIdx])
            ->info()
            ->duration(1500)
            ->send();
    }

    public function cerrarSuccessModal(): void
    {
        $this->cartItems = [];
        $this->resetClienteData();
        $this->montoRecibido = 0;
        $this->clienteDocumento = '';
        $this->searchProducto = '';
        $this->selectedCategoriaId = null;
        $this->createdDocumentoId = null;
        $this->showSuccessModal = false;
    }

    public function getExpectedCajaBalanceProperty(): float
    {
        if (! $this->cajaActivaId) {
            return 0.0;
        }
        $caja = \App\Models\SessioneCaja::find($this->cajaActivaId);
        if (! $caja) {
            return 0.0;
        }
        return (float) app(CajaService::class)->saldoTeorico($caja);
    }

    public function getInitialCajaBalanceProperty(): float
    {
        if (! $this->cajaActivaId) {
            return 0.0;
        }
        $caja = \App\Models\SessioneCaja::find($this->cajaActivaId);
        return $caja ? (float) $caja->saldo_inicial : 0.0;
    }

    public function getCerrarCajaDiferenciaProperty(): float
    {
        $real = (float) $this->cerrarCajaSaldoReal;
        $teorico = $this->expectedCajaBalance;
        return round($real - $teorico, 2);
    }

    public function openCerrarCajaModal(): void
    {
        $this->cerrarCajaSaldoReal = null;
        $this->cerrarCajaObservaciones = '';
        $this->showCerrarCajaModal = true;
    }

    public function closeCerrarCaja(): void
    {
        $this->validate([
            'cerrarCajaSaldoReal' => 'required|numeric|min:0',
            'cerrarCajaObservaciones' => 'nullable|string|max:500',
        ]);

        if (! $this->cajaActivaId) {
            Notification::make()
                ->title('No hay una sesión de caja activa')
                ->danger()
                ->send();
            return;
        }

        $caja = \App\Models\SessioneCaja::find($this->cajaActivaId);
        if (! $caja || ! $caja->estado) {
            Notification::make()
                ->title('La caja ya se encuentra cerrada')
                ->danger()
                ->send();
            return;
        }

        $teorico = (float) app(CajaService::class)->saldoTeorico($caja);
        $real = round((float) $this->cerrarCajaSaldoReal, 2);

        $caja->update([
            'fecha_cierre' => now(),
            'saldo_teorico' => $teorico,
            'saldo_real' => $real,
            'diferencia' => round($real - $teorico, 2),
            'estado' => false,
            'observaciones' => $this->cerrarCajaObservaciones ?: null,
        ]);

        Notification::make()
            ->title('Caja cerrada con éxito')
            ->success()
            ->send();

        $this->redirect('/admin/punto-venta');
    }

    /**
     * Abre el modal de búsqueda de ventas y restablece los resultados y estados de selección.
     */
    public function openBuscarVentaModal(): void
    {
        $this->searchVentaQuery = '';
        $this->ventasResultados = [];
        $this->selectedVentaId = null;
        $this->selectedVentaDetalles = null;
        $this->showBuscarVentaModal = true;
    }

    /**
     * Cierra el modal de búsqueda de ventas y limpia la selección.
     */
    public function cerrarBuscarVentaModal(): void
    {
        $this->showBuscarVentaModal = false;
        $this->selectedVentaId = null;
        $this->selectedVentaDetalles = null;
    }

    /**
     * Realiza la búsqueda de ventas en la sucursal activa cuando cambia el texto de búsqueda.
     */
    public function updatedSearchVentaQuery(): void
    {
        $term = trim($this->searchVentaQuery);

        if ($term === '') {
            $this->ventasResultados = [];
            return;
        }

        $this->ventasResultados = \App\Models\Documento::query()
            ->with(['cliente'])
            ->where('sucursal_id', $this->sucursalId)
            ->where(function ($query) use ($term) {
                $query->where('serie', 'like', "%{$term}%")
                    ->orWhere('numero', 'like', "%{$term}%")
                    ->orWhereHas('cliente', function ($q) use ($term) {
                        $q->where('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%")
                            ->orWhere('razon_social', 'like', "%{$term}%")
                            ->orWhere('documento', 'like', "%{$term}%");
                    });
            })
            ->latest('fecha_emision')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (\App\Models\Documento $doc): array => [
                'id' => $doc->id,
                'comprobante' => "{$doc->tipo_comprobante} {$doc->serie}-{$doc->numero}",
                'cliente' => $doc->cliente ? ($doc->cliente->razon_social ?: trim(($doc->cliente->nombre ?? '') . ' ' . ($doc->cliente->apellido ?? ''))) : 'PÚBLICO EN GENERAL',
                'cliente_documento' => $doc->cliente ? "{$doc->cliente->tipo_documento} {$doc->cliente->documento}" : null,
                'fecha' => $doc->fecha_emision ? $doc->fecha_emision->format('d/m/Y') : $doc->created_at->format('d/m/Y'),
                'total' => (float) $doc->total_neto,
                'estado' => $doc->estado,
                'tipo_comprobante' => $doc->tipo_comprobante,
            ])
            ->all();
    }

    /**
     * Carga el detalle de una venta específica para mostrarlo en el panel lateral.
     *
     * @param int $id ID de la venta/documento.
     */
    public function verDetalleVenta(int $id): void
    {
        $venta = \App\Models\Documento::query()
            ->with(['cliente', 'detalles.presentacion.unidadMedida', 'detalles.producto'])
            ->where('sucursal_id', $this->sucursalId)
            ->find($id);

        if (! $venta) {
            Notification::make()
                ->title('No se encontró la venta o no pertenece a esta sucursal.')
                ->danger()
                ->send();
            return;
        }

        $this->selectedVentaId = $venta->id;
        $this->selectedVentaDetalles = [
            'id' => $venta->id,
            'estado' => $venta->estado,
            'comprobante' => "{$venta->tipo_comprobante} {$venta->serie}-{$venta->numero}",
            'cliente' => $venta->cliente ? ($venta->cliente->razon_social ?: trim(($venta->cliente->nombre ?? '') . ' ' . ($venta->cliente->apellido ?? ''))) : 'PÚBLICO EN GENERAL',
            'cliente_documento' => $venta->cliente ? "{$venta->cliente->tipo_documento} {$venta->cliente->documento}" : null,
            'cliente_direccion' => $venta->cliente?->direccion,
            'fecha' => $venta->fecha_emision ? $venta->fecha_emision->format('d/m/Y') : $venta->created_at->format('d/m/Y'),
            'hora' => $venta->created_at->format('H:i A'),
            'subtotal' => (float) $venta->subtotal,
            'total_igv' => (float) $venta->total_igv,
            'total_descuento' => (float) $venta->total_descuento,
            'total' => (float) $venta->total_neto,
            'medio_pago' => $venta->medio_pago,
            'monto_recibido' => (float) $venta->monto_recibido,
            'vuelto' => (float) $venta->vuelto,
            'referencia_pago' => $venta->referencia_pago,
            'items' => $venta->detalles->map(fn (\App\Models\DetalleDocumento $det): array => [
                'producto_nombre' => $det->producto_nombre,
                'presentacion' => $det->presentacion?->tipo_presentacion ?: 'Unidad',
                'unidad' => $det->presentacion?->unidadMedida?->abreviatura ?? 'und',
                'cantidad' => (float) $det->cantidad,
                'precio_unitario' => (float) $det->precio_unitario,
                'subtotal' => (float) $det->total_linea,
            ])->all(),
        ];
    }

    /**
     * Abre el modal de confirmación para anular una venta.
     */
    public function confirmarAnularVenta(int $id): void
    {
        if (! Auth::user()?->can('ventas.anular')) {
            Notification::make()
                ->title('No tienes permisos para anular ventas.')
                ->danger()
                ->send();
            return;
        }

        $documento = \App\Models\Documento::query()
            ->where('sucursal_id', $this->sucursalId)
            ->find($id);

        if (! $documento) {
            Notification::make()
                ->title('No se encontró la venta o no pertenece a esta sucursal.')
                ->danger()
                ->send();
            return;
        }

        if ($documento->estado === false) {
            Notification::make()
                ->title('Este documento ya se encuentra anulado.')
                ->warning()
                ->send();
            return;
        }

        if (! in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA', 'TICKET'], true)) {
            Notification::make()
                ->title('Solo se pueden anular Facturas, Boletas y Tickets.')
                ->warning()
                ->send();
            return;
        }

        $this->anularVentaId = $documento->id;
        $this->anularVentaComprobante = "{$documento->tipo_comprobante} {$documento->serie}-{$documento->numero}";
        $this->anularMotivoCodigo = '01';
        $this->showAnularVentaModal = true;
    }

    /**
     * Ejecuta la anulación de la venta seleccionada.
     */
    public function anularVenta(): void
    {
        if (! $this->anularVentaId) {
            return;
        }

        if (! Auth::user()?->can('ventas.anular')) {
            Notification::make()
                ->title('No tienes permisos para anular ventas.')
                ->danger()
                ->send();
            return;
        }

        $documento = \App\Models\Documento::query()
            ->with(['detalles', 'empresa', 'sucursal', 'cliente'])
            ->where('sucursal_id', $this->sucursalId)
            ->find($this->anularVentaId);

        if (! $documento) {
            Notification::make()
                ->title('No se encontró la venta.')
                ->danger()
                ->send();
            $this->cerrarAnularVentaModal();
            return;
        }

        $motivoCodigo = $this->anularMotivoCodigo;
        $motivoDescripcion = AnulacionService::MOTIVOS[$motivoCodigo] ?? 'Anulación de la operación';

        try {
            $notaCredito = app(AnulacionService::class)->anular(
                user: Auth::user(),
                documento: $documento,
                motivoCodigo: $motivoCodigo,
                motivoDescripcion: $motivoDescripcion,
            );

            if ($documento->tipo_comprobante === 'TICKET') {
                Notification::make()
                    ->title('Ticket anulado con éxito')
                    ->body("Se restauró el stock. El ticket {$documento->serie}-{$documento->numero} ha sido anulado.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Venta anulada con éxito')
                    ->body("Se generó la Nota de Crédito {$notaCredito->serie}-{$notaCredito->numero} y se encoló el envío a SUNAT.")
                    ->success()
                    ->send();
            }

            // Recargar resultados y detalle
            $this->updatedSearchVentaQuery();
            if ($this->selectedVentaId === $this->anularVentaId) {
                $this->selectedVentaId = null;
                $this->selectedVentaDetalles = null;
            }
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('Error al anular')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->cerrarAnularVentaModal();
    }

    /**
     * Cierra el modal de anulación y limpia el estado.
     */
    public function cerrarAnularVentaModal(): void
    {
        $this->showAnularVentaModal = false;
        $this->anularVentaId = null;
        $this->anularVentaComprobante = null;
        $this->anularMotivoCodigo = '01';
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
