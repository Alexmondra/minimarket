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
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\DetalleCompra as DetalleCompraModel;
use App\Models\MovimientoInventario;
use App\Models\ProductoSucursal;
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

    public function test_it_searches_existing_lots_by_code(): void
    {
        $this->actingAs($this->user);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-EXISTENTE',
            'producto_nombre' => $this->producto->nombre,
            'estado_lote' => 'activo',
            'precio_compra' => 50.00,
        ]);

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->set('codigoLote', 'LOT-')
        ->assertSet('showLotesDropdown', true)
        ->assertCount('lotesResultados', 1)
        ->assertSet('lotesResultados.0.codigo_lote', 'LOT-EXISTENTE');
    }

    public function test_it_opens_existing_lot_modal_and_loads_for_editing(): void
    {
        $this->actingAs($this->user);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-EXIST-MODAL',
            'producto_nombre' => $this->producto->nombre,
            'estado_lote' => 'activo',
            'precio_compra' => 120.00,
        ]);

        $lp = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $this->presentacion->id,
            'stock_inicial' => 15,
            'stock' => 15,
            'precio_compra' => 8.00,
        ]);

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('verLoteExistente', $lote->id)
        ->assertSet('showLoteExistenteModal', true)
        ->assertSet('modalLoteDetalles.codigo_lote', 'LOT-EXIST-MODAL')
        ->call('cargarLoteExistenteParaEditar')
        ->assertSet('editingLoteId', $lote->id)
        ->assertSet('codigoLote', 'LOT-EXIST-MODAL')
        ->assertSet('presentacionesDisponibles.0.cantidad', 15)
        ->assertSet('presentacionesDisponibles.0.precio_compra', 8.00);
    }

    public function test_it_loads_lot_details_from_added_batches_table_to_edit(): void
    {
        $this->actingAs($this->user);

        $compraTest = Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('seleccionarProducto', $this->producto->id, $this->producto->nombre)
        ->set('codigoLote', 'LOT-ADDED')
        ->set('presentacionesDisponibles.0.cantidad', 5)
        ->set('presentacionesDisponibles.0.total_pagado', 25.00)
        ->call('agregarLote');

        $detalle = DetalleCompraModel::where('compra_id', $this->compra->id)->first();
        $this->assertNotNull($detalle);

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('editarDetalle', $detalle->id)
        ->assertSet('editingLoteId', $detalle->lote_id)
        ->assertSet('editingDetalleId', $detalle->id)
        ->assertSet('codigoLote', 'LOT-ADDED')
        ->assertSet('presentacionesDisponibles.0.cantidad', 5)
        ->assertSet('presentacionesDisponibles.0.precio_compra', 5.00);
    }

    public function test_it_cancels_editing_mode(): void
    {
        $this->actingAs($this->user);

        $compraTest = Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('seleccionarProducto', $this->producto->id, $this->producto->nombre)
        ->set('codigoLote', 'LOT-CANCEL')
        ->set('presentacionesDisponibles.0.cantidad', 5)
        ->set('presentacionesDisponibles.0.total_pagado', 25.00)
        ->call('agregarLote');

        $detalle = DetalleCompraModel::where('compra_id', $this->compra->id)->first();

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('editarDetalle', $detalle->id)
        ->assertSet('editingLoteId', $detalle->lote_id)
        ->call('cancelarEdicion')
        ->assertSet('editingLoteId', null)
        ->assertSet('editingDetalleId', null)
        ->assertSet('codigoLote', '');
    }

    public function test_it_updates_lote_and_details_atomically(): void
    {
        $this->actingAs($this->user);

        $compraTest = Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('seleccionarProducto', $this->producto->id, $this->producto->nombre)
        ->set('codigoLote', 'LOT-TO-EDIT')
        ->set('presentacionesDisponibles.0.cantidad', 10)
        ->set('presentacionesDisponibles.0.total_pagado', 100.00)
        ->call('agregarLote');

        $detalle = DetalleCompraModel::where('compra_id', $this->compra->id)->first();
        $this->assertNotNull($detalle);
        $originalLp = LotePresentacion::where('lote_id', $detalle->lote_id)->first();
        $this->assertNotNull($originalLp);

        // Verify initial counts in DB
        $this->assertDatabaseHas('lotes', [
            'id' => $detalle->lote_id,
            'codigo_lote' => 'LOT-TO-EDIT',
            'precio_compra' => 100.00,
        ]);
        $this->assertDatabaseHas('lote_presentacion', [
            'lote_id' => $detalle->lote_id,
            'stock' => 10,
            'precio_compra' => 10.00,
        ]);

        // Load to edit and update
        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('editarDetalle', $detalle->id)
        ->set('codigoLote', 'LOT-UPDATED')
        ->set('presentacionesDisponibles.0.cantidad', 20)
        ->set('presentacionesDisponibles.0.total_pagado', 180.00)
        ->call('agregarLote')
        ->assertHasNoErrors();

        // Verify updated database records
        $this->assertDatabaseHas('lotes', [
            'id' => $detalle->lote_id,
            'codigo_lote' => 'LOT-UPDATED',
            'precio_compra' => 180.00,
        ]);

        // Old LotePresentacion should be deleted, new one created
        $this->assertDatabaseMissing('lote_presentacion', [
            'id' => $originalLp->id,
        ]);
        $this->assertDatabaseHas('lote_presentacion', [
            'lote_id' => $detalle->lote_id,
            'stock' => 20,
            'precio_compra' => 9.00, // 180.00 / 20
        ]);

        // Old ProductoSucursal and movements should be cleaned and replaced
        $this->assertDatabaseMissing('producto_sucursal', [
            'lote_presentacion_id' => $originalLp->id,
        ]);
        $this->assertSoftDeleted('movimientos_inventario', [
            'referencia' => "LotePresentacion:{$originalLp->id}",
        ]);

        $newLp = LotePresentacion::where('lote_id', $detalle->lote_id)->first();
        $this->assertDatabaseHas('producto_sucursal', [
            'lote_presentacion_id' => $newLp->id,
        ]);
        $this->assertDatabaseHas('movimientos_inventario', [
            'referencia' => "LotePresentacion:{$newLp->id}",
            'motivo' => "Compra #{$this->compra->id} - lote LOT-UPDATED (Editado)",
            'cantidad' => 20,
        ]);

        $this->assertDatabaseHas('detalle_compras', [
            'id' => $detalle->id,
            'precio_compra' => 180.00,
        ]);
    }
}

