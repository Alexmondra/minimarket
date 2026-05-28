<?php

namespace Tests\Feature;

use App\Livewire\Compras\Components\DetalleCompra;
use App\Models\Compra;
use App\Models\Proveedor;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DetalleCompraCalculoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Sucursal $sucursal;
    private Compra $compra;
    private Producto $producto;
    private ProductoPresentacion $presentacion;

    protected function setUp(): void
    {
        parent::setUp();

        $ubigeo = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);

        $this->empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
            'incluido_tributo' => true,
        ]);

        $this->sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->id,
            'nombre_sucursal' => 'CENTRO',
            'direccion' => 'AV. CENTRAL 123',
            'impuesto_porcentaje' => 18,
        ]);

        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'name' => 'Comprador',
            'email' => 'compras@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user);

        $proveedor = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PROVEEDOR TEST',
            'tipo_documento' => 'RUC',
            'numero_documento' => '20999999999',
            'estado' => true,
        ]);

        $this->compra = Compra::create([
            'sucursal_id' => $this->sucursal->id,
            'proveedor_id' => $proveedor->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'factura',
            'fecha_recepcion' => now(),
            'costo_total_factura' => 0.00,
            'estado' => false,
        ]);

        $this->producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Paracetamol 500mg',
            'slug' => 'paracetamol-500mg',
            'activo' => true,
        ]);

        $unidad = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);

        $this->presentacion = ProductoPresentacion::create([
            'producto_id' => $this->producto->id,
            'unidad_medida_id' => $unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Caja',
        ]);
    }

    public function test_it_calculates_unit_cost_and_batch_total_on_update(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('seleccionarProducto', $this->producto->id, $this->producto->nombre)
        ->set('presentacionesDisponibles.0.cantidad', 10)
        ->set('presentacionesDisponibles.0.total_pagado', 50.00)
        ->assertSet('presentacionesDisponibles.0.precio_compra', 5.00)
        ->assertSet('precioCompraTotal', 50.00);
    }

    public function test_it_saves_unit_cost_to_lote_presentacion_on_agregar_lote(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('seleccionarProducto', $this->producto->id, $this->producto->nombre)
        ->set('codigoLote', 'LOT-001')
        ->set('presentacionesDisponibles.0.cantidad', 20)
        ->set('presentacionesDisponibles.0.total_pagado', 120.00)
        ->set('presentacionesDisponibles.0.precio_venta', 10.00)
        ->call('agregarLote')
        ->assertHasNoErrors();

        $this->assertDatabaseHas('lotes', [
            'codigo_lote' => 'LOT-001',
            'precio_compra' => 120.00,
        ]);

        $this->assertDatabaseHas('lote_presentacion', [
            'stock' => 20,
            'precio_compra' => 6.00, // 120.00 / 20
        ]);
    }

    public function test_it_creates_presentation_from_modal(): void
    {
        $this->actingAs($this->user);

        // 1. Create base presentation
        $unidad = UniMedida::where('abreviatura', 'und')->first();

        // 2. Use Livewire to create presentation
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('presentacion.jpg');

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('modoProductoPresentacion', 'existente')
        ->set('modalProductoId', $this->producto->id)
        ->set('modalUnidadMedidaId', $unidad->id)
        ->set('modalCantidadPorEmpaque', 12)
        ->set('modalTipoPresentacion', 'Caja de 12')
        ->set('modalCodigoBarra', '7750123456789')
        ->set('modalPresentacionBaseId', $this->presentacion->id)
        ->set('modalImagen', $file)
        ->call('crearPresentacionDesdeModal')
        ->assertHasNoErrors();

        // Assert DB records
        $this->assertDatabaseHas('producto_presentacion', [
            'producto_id' => $this->producto->id,
            'unidad_medida_id' => $unidad->id,
            'cantidad' => 12,
            'tipo_presentacion' => 'Caja de 12',
            'presentacion_base_id' => $this->presentacion->id,
        ]);

        $this->assertDatabaseHas('producto_presentacion_barras', [
            'codigo_barra' => '7750123456789',
        ]);

        // Assert file exists in fake storage
        $newPresentation = ProductoPresentacion::where('cantidad', 12)->first();
        $this->assertNotNull($newPresentation->imagen);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($newPresentation->imagen);
    }

    public function test_it_validates_unique_barcode_on_presentation_creation(): void
    {
        $this->actingAs($this->user);

        // Register the barcode on the existing presentation
        $this->presentacion->barras()->create([
            'codigo_barra' => '7750123456789',
        ]);

        $unidad = UniMedida::where('abreviatura', 'und')->first();

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('modoProductoPresentacion', 'existente')
        ->set('modalProductoId', $this->producto->id)
        ->set('modalUnidadMedidaId', $unidad->id)
        ->set('modalCantidadPorEmpaque', 6)
        ->set('modalTipoPresentacion', 'Paquete de 6')
        ->set('modalCodigoBarra', '7750123456789') // duplicate
        ->call('crearPresentacionDesdeModal')
        ->assertHasErrors(['modalCodigoBarra' => 'unique']);
    }

    public function test_it_detects_barcode_vs_text_name_in_modal_open(): void
    {
        $this->actingAs($this->user);

        // Scenario 1: Numeric search term (scanned barcode)
        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('searchProducto', '7750123456789')
        ->call('abrirCrearPresentacionModal')
        ->assertSet('modalCodigoBarra', '7750123456789')
        ->assertSet('modalSearchProducto', '')
        ->assertSet('modoProductoPresentacion', 'nuevo');

        // Scenario 2: Text search term (product name)
        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('searchProducto', 'Chocolate Vicio')
        ->call('abrirCrearPresentacionModal')
        ->assertSet('modalCodigoBarra', null)
        ->assertSet('modalSearchProducto', 'Chocolate Vicio')
        ->assertSet('modalNuevoProductoNombre', 'Chocolate Vicio')
        ->assertSet('modoProductoPresentacion', 'nuevo');
    }

    public function test_it_can_update_existing_presentation_with_additional_barcodes(): void
    {
        $this->actingAs($this->user);

        $unidad = UniMedida::where('abreviatura', 'und')->first();

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('modoProductoPresentacion', 'existente')
        ->set('modalProductoId', $this->producto->id)
        ->set('modalTipoPresentacion', 'Ca')
        ->assertSet('showModalPresentacionDropdown', true)
        ->call('seleccionarPresentacionDesdeModal', $this->presentacion->id)
        ->assertSet('modalEditingPresentationId', $this->presentacion->id)
        ->assertSet('modalUnidadMedidaId', $unidad->id)
        ->assertSet('modalCantidadPorEmpaque', 1)
        ->set('modalNuevoCodigoBarra', '88888888')
        ->call('agregarCodigoBarraDesdeModal')
        ->assertSet('modalBarras', ['88888888'])
        ->call('crearPresentacionDesdeModal')
        ->assertHasNoErrors();

        $this->assertDatabaseHas('producto_presentacion_barras', [
            'producto_presentacion_id' => $this->presentacion->id,
            'codigo_barra' => '88888888',
        ]);
    }

    public function test_it_resets_editing_presentation_id_when_name_changes(): void
    {
        $this->actingAs($this->user);

        $unidad = UniMedida::where('abreviatura', 'und')->first();

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('modoProductoPresentacion', 'existente')
        ->set('modalProductoId', $this->producto->id)
        ->set('modalTipoPresentacion', 'Ca')
        ->call('seleccionarPresentacionDesdeModal', $this->presentacion->id)
        ->assertSet('modalEditingPresentationId', $this->presentacion->id)
        // Now change the input text
        ->set('modalTipoPresentacion', 'Caja Nueva')
        ->assertSet('modalEditingPresentationId', null);
    }
}

