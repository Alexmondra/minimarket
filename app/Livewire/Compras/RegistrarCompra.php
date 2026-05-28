<?php

namespace App\Livewire\Compras;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Proveedor;
use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegistrarCompra extends Component
{
    use WithFileUploads;

    // Paso actual del wizard
    public int $paso = 1;

    // Datos de la compra (cabecera)
    public ?int $compraId = null;

    public ?int $proveedorId = null;

    public ?int $sucursalId = null;

    public string $tipoComprobante = 'factura';

    public ?string $numeroFactura = null;

    public string $fechaRecepcion;

    public ?string $observaciones = null;

    // Archivo comprobante
    public $archivoComprobante = null;

    public ?string $archivoComprobanteNombre = null;

    // Búsqueda de proveedor
    public string $searchProveedor = '';

    public array $proveedoresResultados = [];

    public bool $showProveedorDropdown = false;

    // Modal de registrar nuevo proveedor
    public bool $showRegistrarProveedorModal = false;

    public string $nuevoProveedorTipoDocumento = 'RUC';

    public string $nuevoProveedorDocumento = '';

    public string $nuevoProveedorNombre = '';

    public string $nuevoProveedorRazonSocial = '';

    public string $nuevoProveedorDireccion = '';

    public string $nuevoProveedorTelefono = '';

    public string $nuevoProveedorEmail = '';

    public string $nuevoProveedorContactoPrincipal = '';

    public string $nuevoProveedorTelefonoContacto = '';

    public string $nuevoProveedorRubro = '';

    public string $nuevoProveedorObservaciones = '';

    // Detalles agregados (para mostrar en resumen)
    public array $detalles = [];

    // Resumen
    public float $totalUnidades = 0;

    public float $subtotalCompra = 0;

    public float $impuestoPorcentaje = 0;

    public float $totalImpuesto = 0;

    public float $totalFinal = 0;

    protected function rules()
    {
        $allowedSucursalIds = app(SucursalContext::class)->allowedSucursalIds()->all();

        $rules = [
            'proveedorId' => 'required|exists:proveedores,id',
            'sucursalId' => ['required', 'integer', Rule::in($allowedSucursalIds)],
            'tipoComprobante' => 'required|string|in:factura,boleta,nota_credito,nota_debito',
            'numeroFactura' => 'nullable|string|max:255',
            'fechaRecepcion' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
            'archivoComprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif|max:10240', // 10MB máx
        ];

        return $rules;
    }

    protected $listeners = [
        'loteCreado' => 'onLoteCreado',
        'productoCreado' => 'onProductoCreado',
        'detalleAgregado' => 'actualizarResumen',
        'detalleEliminado' => 'actualizarResumen',
    ];

    public function mount(): void
    {
        $this->fechaRecepcion = now()->format('Y-m-d');

        $context = app(SucursalContext::class);
        $context->normalizeSession(Auth::user());
        $this->sucursalId = $context->resolveSucursalForWrite();

        // Si hay un compra_id en la URL, cargar esa compra
        if ($compraId = request('compra_id')) {
            $this->cargarCompra((int) $compraId);
        }

        $this->actualizarResumen();
    }

    public function cargarCompra(int $id): void
    {
        $compra = app(SucursalContext::class)
            ->applyToQuery(Compra::with('detalle'))
            ->findOrFail($id);

        $this->compraId = $compra->id;
        $this->proveedorId = $compra->proveedor_id;
        $this->sucursalId = $compra->sucursal_id;
        $this->tipoComprobante = $compra->tipo_comprobante;
        $this->numeroFactura = $compra->numero_factura_proveedor;
        $this->fechaRecepcion = $compra->fecha_recepcion->format('Y-m-d');
        $this->observaciones = $compra->observaciones;
        $this->archivoComprobanteNombre = $compra->archivo_comprobante;
        $this->paso = 2;
        $this->impuestoPorcentaje = $compra->sucursal?->impuesto_porcentaje ?? 0;

        $this->cargarDetalles();
        $this->actualizarResumen();
    }

    public function guardarCabecera(): void
    {
        $context = app(SucursalContext::class);
        $this->sucursalId = $context->resolveSucursalForWrite($this->sucursalId);

        if (! $this->sucursalId) {
            $this->addError('sucursalId', 'Selecciona una sucursal para registrar esta compra.');

            Notification::make()
                ->title('Selecciona una sucursal')
                ->warning()
                ->send();

            return;
        }

        $this->validate();

        $proveedorValido = Proveedor::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->whereKey($this->proveedorId)
            ->exists();

        if (! $proveedorValido) {
            $this->addError('proveedorId', 'El proveedor seleccionado no es válido o no pertenece a la empresa.');

            return;
        }

        // Subir archivo comprobante si se seleccionó uno nuevo
        $archivoRuta = null;
        if ($this->archivoComprobante) {
            // Eliminar archivo anterior si existe
            if ($this->compraId) {
                $compraExistente = Compra::find($this->compraId);
                if ($compraExistente && $compraExistente->archivo_comprobante) {
                    Storage::disk('local')->delete($compraExistente->archivo_comprobante);
                }
            }
            $archivoRuta = $this->archivoComprobante->store('comprobantes', 'local');
            $this->archivoComprobanteNombre = $archivoRuta;
        }

        $data = [
            'proveedor_id' => $this->proveedorId,
            'sucursal_id' => $this->sucursalId,
            'tipo_comprobante' => $this->tipoComprobante,
            'numero_factura_proveedor' => $this->numeroFactura,
            'fecha_recepcion' => $this->fechaRecepcion,
            'observaciones' => $this->observaciones,
        ];

        if ($archivoRuta) {
            $data['archivo_comprobante'] = $archivoRuta;
        }

        if ($this->compraId) {
            $compra = $context->applyToQuery(Compra::query())->findOrFail($this->compraId);
            $compra->update($data);
        } else {
            $data['user_id'] = Auth::id();
            $data['costo_total_factura'] = 0;
            $data['estado'] = false; // borrador

            $compra = Compra::create($data);
            $this->compraId = $compra->id;
        }

        // Actualizar impuesto desde sucursal
        $this->impuestoPorcentaje = $compra->sucursal->impuesto_porcentaje ?? 0;

        // Ir al paso 2
        $this->paso = 2;

        // Disparar evento para que el componente DetalleCompra se refresque
        $this->dispatch('compraCreada', compraId: $this->compraId, sucursalId: $this->sucursalId);

        Notification::make()
            ->title('Cabecera guardada correctamente')
            ->success()
            ->send();
    }

    public function finalizarCompra(): void
    {
        if (! $this->compraId) {
            return;
        }

        $totales = $this->calcularTotales();

        DB::transaction(function () use ($totales) {
            $compra = app(SucursalContext::class)
                ->applyToQuery(Compra::query())
                ->findOrFail($this->compraId);

            $compra->update([
                'costo_total_factura' => $totales['totalFinal'],
                'estado' => true, // finalizada
            ]);
        });

        Notification::make()
            ->title('Compra finalizada exitosamente')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.resources.compras.index'));
    }

    public function actualizarResumen(): void
    {
        $this->cargarDetalles();
        $totales = $this->calcularTotales();
        $this->totalUnidades = $totales['totalUnidades'];
        $this->subtotalCompra = $totales['subtotal'];
        $this->totalImpuesto = $totales['impuesto'];
        $this->totalFinal = $totales['totalFinal'];
    }

    protected function cargarDetalles(): void
    {
        if (! $this->compraId) {
            $this->detalles = [];

            return;
        }

        $this->detalles = DetalleCompra::where('compra_id', $this->compraId)
            ->with([
                'lote',
                'lote.lotePresentaciones.productoPresentacion',
            ])
            ->get()
            ->toArray();
    }

    protected function calcularTotales(): array
    {
        $detalles = DetalleCompra::where('compra_id', $this->compraId)->get();

        $detalles->loadMissing('lote.lotePresentaciones');

        $totalUnidades = $detalles->sum(function ($detalle) {
            return $detalle->lote?->lotePresentaciones?->sum('stock') ?? 0;
        });

        $subtotal = $detalles->sum('precio_compra');
        $impuesto = 0;
        $totalFinal = $subtotal;

        return [
            'totalUnidades' => (float) $totalUnidades,
            'subtotal' => (float) $subtotal,
            'impuesto' => (float) $impuesto,
            'totalFinal' => (float) $totalFinal,
        ];
    }

    public function onLoteCreado($loteId): void
    {
        $this->dispatch('loteSeleccionado', loteId: $loteId);
    }

    public function onProductoCreado($productoId): void
    {
        $this->dispatch('productoSeleccionado', productoId: $productoId);
    }

    public function irPaso1(): void
    {
        $this->paso = 1;
    }

    public function updatedSearchProveedor(): void
    {
        if (strlen($this->searchProveedor) < 2) {
            $this->proveedoresResultados = [];
            $this->showProveedorDropdown = false;

            return;
        }

        $this->proveedoresResultados = Proveedor::query()
            ->where('estado', true)
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->searchProveedor}%")
                    ->orWhere('numero_documento', 'like', "%{$this->searchProveedor}%")
                    ->orWhere('razon_social', 'like', "%{$this->searchProveedor}%");
            })
            ->limit(10)
            ->get()
            ->toArray();

        $this->showProveedorDropdown = count($this->proveedoresResultados) > 0;
    }

    /**
     * Abre el modal para registrar un nuevo proveedor, inicializando las variables.
     */
    public function abrirRegistrarProveedorModal(): void
    {
        $this->nuevoProveedorTipoDocumento = 'RUC';
        $this->nuevoProveedorDocumento = '';
        $this->nuevoProveedorNombre = '';
        $this->nuevoProveedorRazonSocial = '';
        $this->nuevoProveedorDireccion = '';
        $this->nuevoProveedorTelefono = '';
        $this->nuevoProveedorEmail = '';
        $this->nuevoProveedorContactoPrincipal = '';
        $this->nuevoProveedorTelefonoContacto = '';
        $this->nuevoProveedorRubro = '';
        $this->nuevoProveedorObservaciones = '';

        $term = trim($this->searchProveedor);
        if (is_numeric($term)) {
            $this->nuevoProveedorDocumento = $term;
            if (strlen($term) === 8) {
                $this->nuevoProveedorTipoDocumento = 'DNI';
            } elseif (strlen($term) === 11) {
                $this->nuevoProveedorTipoDocumento = 'RUC';
            }
        }

        $this->showRegistrarProveedorModal = true;
    }

    /**
     * Consulta el número de documento del nuevo proveedor contra la API externa (RENIEC/SUNAT).
     */
    public function buscarNuevoProveedor(): void
    {
        $term = trim($this->nuevoProveedorDocumento);
        if ($term === '') {
            Notification::make()
                ->title('Debe ingresar un número de documento.')
                ->warning()
                ->send();

            return;
        }

        if (! ctype_digit($term) || ($this->nuevoProveedorTipoDocumento === 'DNI' && strlen($term) !== 8) || ($this->nuevoProveedorTipoDocumento === 'RUC' && strlen($term) !== 11)) {
            Notification::make()
                ->title('Error en la digitación: El documento debe tener 8 dígitos para DNI o 11 dígitos para RUC.')
                ->danger()
                ->send();

            return;
        }

        // Primero verificar si el proveedor ya existe en la base de datos local para esta empresa
        $proveedorExistente = Proveedor::query()
            ->where('empresa_id', Auth::user()->empresa_id)
            ->where('numero_documento', $term)
            ->first();

        if ($proveedorExistente) {
            $this->seleccionarProveedor($proveedorExistente->id, $proveedorExistente->nombre);
            $this->showRegistrarProveedorModal = false;
            Notification::make()
                ->title('El proveedor ya existe y ha sido seleccionado.')
                ->success()
                ->send();

            return;
        }

        $key = config('services.datos.key');
        $dniUrl = config('services.datos.dni_url');
        $rucUrl = config('services.datos.ruc_url');

        if (empty($key) || ($this->nuevoProveedorTipoDocumento === 'DNI' && empty($dniUrl)) || ($this->nuevoProveedorTipoDocumento === 'RUC' && empty($rucUrl))) {
            Notification::make()
                ->title('Error: Configuración de API externa no encontrada.')
                ->danger()
                ->send();

            return;
        }

        $url = $this->nuevoProveedorTipoDocumento === 'DNI' ? ($dniUrl.$term) : ($rucUrl.$term);

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
                if ($this->nuevoProveedorTipoDocumento === 'RUC' && empty($razonSocial)) {
                    $razonSocial = $data['nombre'] ?? $data['nombres'] ?? null;
                }

                $direccion = $data['direccion'] ?? $data['domicilio_fiscal'] ?? $data['direccion_completa'] ?? null;

                $hasName = ($this->nuevoProveedorTipoDocumento === 'DNI' && ! empty($nombre)) || ($this->nuevoProveedorTipoDocumento === 'RUC' && ! empty($razonSocial));

                if ($hasName) {
                    if ($this->nuevoProveedorTipoDocumento === 'RUC') {
                        $this->nuevoProveedorNombre = $razonSocial;
                        $this->nuevoProveedorRazonSocial = $razonSocial;
                    } else {
                        $this->nuevoProveedorNombre = trim($nombre.' '.$apellido);
                        $this->nuevoProveedorRazonSocial = '';
                    }
                    $this->nuevoProveedorDireccion = $direccion ?? '';
                    $this->nuevoProveedorTelefono = $data['telefono'] ?? '';
                    $this->nuevoProveedorEmail = $data['correo'] ?? $data['email'] ?? '';

                    Notification::make()
                        ->title('Datos obtenidos del servicio externo con éxito.')
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

    /**
     * Registra manualmente el proveedor ingresado en el modal y lo selecciona.
     */
    public function registrarProveedorManual(): void
    {
        $this->validate([
            'nuevoProveedorNombre' => 'required|string|max:255',
            'nuevoProveedorTipoDocumento' => 'required|string|in:RUC,DNI,CE,OTRO',
            'nuevoProveedorDocumento' => [
                'required',
                'string',
                'max:20',
                Rule::unique('proveedores', 'numero_documento')
                    ->where('empresa_id', Auth::user()->empresa_id)
                    ->whereNull('deleted_at'),
            ],
            'nuevoProveedorRazonSocial' => 'nullable|string|max:255',
            'nuevoProveedorDireccion' => 'nullable|string|max:255',
            'nuevoProveedorTelefono' => 'nullable|string|max:20',
            'nuevoProveedorEmail' => 'nullable|email|max:255',
            'nuevoProveedorContactoPrincipal' => 'nullable|string|max:255',
            'nuevoProveedorTelefonoContacto' => 'nullable|string|max:20',
            'nuevoProveedorRubro' => 'nullable|string|max:255',
            'nuevoProveedorObservaciones' => 'nullable|string|max:1000',
        ], [
            'nuevoProveedorNombre.required' => 'El nombre del proveedor es obligatorio.',
            'nuevoProveedorDocumento.required' => 'El número de documento es obligatorio.',
            'nuevoProveedorDocumento.unique' => 'Ya existe un proveedor registrado con este número de documento.',
            'nuevoProveedorEmail.email' => 'El formato del correo electrónico es inválido.',
        ]);

        DB::transaction(function () {
            $proveedor = Proveedor::create([
                'empresa_id' => Auth::user()->empresa_id,
                'sucursal_id' => $this->sucursalId,
                'nombre' => $this->nuevoProveedorNombre,
                'tipo_documento' => $this->nuevoProveedorTipoDocumento,
                'numero_documento' => $this->nuevoProveedorDocumento,
                'razon_social' => $this->nuevoProveedorRazonSocial,
                'direccion' => $this->nuevoProveedorDireccion,
                'telefono' => $this->nuevoProveedorTelefono,
                'email' => $this->nuevoProveedorEmail,
                'contacto_principal' => $this->nuevoProveedorContactoPrincipal,
                'telefono_contacto' => $this->nuevoProveedorTelefonoContacto,
                'rubro' => $this->nuevoProveedorRubro,
                'observaciones' => $this->nuevoProveedorObservaciones,
                'estado' => true,
            ]);

            $this->seleccionarProveedor($proveedor->id, $proveedor->nombre);
        });

        // Reset fields
        $this->nuevoProveedorTipoDocumento = 'RUC';
        $this->nuevoProveedorDocumento = '';
        $this->nuevoProveedorNombre = '';
        $this->nuevoProveedorRazonSocial = '';
        $this->nuevoProveedorDireccion = '';
        $this->nuevoProveedorTelefono = '';
        $this->nuevoProveedorEmail = '';
        $this->nuevoProveedorContactoPrincipal = '';
        $this->nuevoProveedorTelefonoContacto = '';
        $this->nuevoProveedorRubro = '';
        $this->nuevoProveedorObservaciones = '';

        $this->showRegistrarProveedorModal = false;

        Notification::make()
            ->title('Proveedor registrado y seleccionado con éxito.')
            ->success()
            ->send();
    }

    public function seleccionarProveedor(int $id, string $nombre): void
    {
        $this->proveedorId = $id;
        $this->searchProveedor = $nombre;
        $this->showProveedorDropdown = false;
    }

    public function getSucursalesProperty()
    {
        return app(SucursalContext::class)->sucursalesForWrite();
    }

    public function getSucursalBloqueadaProperty(): bool
    {
        return app(SucursalContext::class)->activeSucursalId() !== null;
    }

    public function getSucursalActivaNombreProperty(): ?string
    {
        return app(SucursalContext::class)->activeSucursal()?->nombre_sucursal;
    }

    public function render()
    {
        return view('livewire.compras.registrar-compra');
    }
}
