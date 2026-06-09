<?php

namespace Tests\Feature;

use App\Livewire\Productos\IngresoRapidoProducto;
use App\Models\Empresa;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentacionBarra;
use App\Models\ProductoSucursal;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class IngresoRapidoProductoTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    private Sucursal $sucursal;

    private User $user;

    private UniMedida $unidad;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'productos.crear', 'guard_name' => 'web']);

        $ubigeo = Ubigeo::create([
            'ubigeo' => '150101',
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
            'ubigeo' => $ubigeo->ubigeo,
            'nombre_sucursal' => 'CENTRO',
            'direccion' => 'AV. CENTRAL 123',
            'impuesto_porcentaje' => 18,
        ]);

        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'name' => 'Operator',
            'email' => 'operator@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->givePermissionTo('productos.crear');
        $this->sucursal->users()->attach($this->user);

        $this->unidad = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);
    }

    public function test_creates_new_product_with_presentation_stock_and_price(): void
    {
        $this->actingAs($this->user);

        Livewire::test(IngresoRapidoProducto::class)
            ->set('codigoBarra', '7750001000012')
            ->set('nombre', 'Gaseosa Nueva 500ml')
            ->set('unidadMedidaId', $this->unidad->id)
            ->set('tipoPresentacion', 'Botella 500ml')
            ->set('cantidadPresentacion', 1)
            ->set('cantidadIngreso', 24)
            ->set('totalPagado', 28.80)
            ->set('precioVenta', 2.00)
            ->call('guardar')
            ->assertHasNoErrors();

        $producto = Producto::where('nombre', 'Gaseosa Nueva 500ml')->firstOrFail();
        $presentacion = ProductoPresentacion::where('producto_id', $producto->id)->firstOrFail();

        $this->assertDatabaseHas('producto_presentacion', [
            'id' => $presentacion->id,
            'tipo_presentacion' => 'Botella 500ml',
            'cantidad' => 1,
        ]);

        $this->assertDatabaseHas('producto_presentacion_barras', [
            'producto_presentacion_id' => $presentacion->id,
            'codigo_barra' => '7750001000012',
        ]);

        $this->assertDatabaseHas('lote_presentacion', [
            'producto_presentacion_id' => $presentacion->id,
            'stock_inicial' => 24,
            'stock' => 24,
        ]);

        $lotePresentacion = LotePresentacion::where('producto_presentacion_id', $presentacion->id)->firstOrFail();

        $this->assertDatabaseHas('producto_sucursal', [
            'producto_id' => $producto->id,
            'sucursal_id' => $this->sucursal->id,
            'lote_presentacion_id' => $lotePresentacion->id,
            'precio' => 2.00,
        ]);
    }

    public function test_searches_existing_product_and_creates_new_presentation_for_it(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Aceite Primor',
            'slug' => 'aceite-primor',
            'activo' => true,
        ]);

        ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad 1L',
        ]);

        Livewire::test(IngresoRapidoProducto::class)
            ->set('busqueda', 'Primor')
            ->assertSet('productoSearchResults.0.id', $producto->id)
            ->call('seleccionarProducto', $producto->id)
            ->assertSet('productoExistenteId', $producto->id)
            ->set('crearNuevaPresentacion', true)
            ->set('codigoBarra', '7750002000011')
            ->set('tipoPresentacion', 'Caja x 12')
            ->set('cantidadPresentacion', 12)
            ->set('unidadMedidaId', $this->unidad->id)
            ->set('cantidadIngreso', 5)
            ->set('totalPagado', 240.00)
            ->set('precioVenta', 60.00)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertSame(1, Producto::where('nombre', 'Aceite Primor')->count());

        $this->assertDatabaseHas('producto_presentacion', [
            'producto_id' => $producto->id,
            'tipo_presentacion' => 'Caja x 12',
            'cantidad' => 12,
        ]);
    }

    public function test_barcode_selects_existing_presentation_and_replenishes_that_presentation(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Leche Gloria',
            'slug' => 'leche-gloria',
            'activo' => true,
        ]);

        $unidadPresentacion = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
        ]);

        $cajaPresentacion = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 24,
            'tipo_presentacion' => 'Caja x 24',
        ]);

        ProductoPresentacionBarra::create([
            'producto_presentacion_id' => $cajaPresentacion->id,
            'codigo_barra' => '7750003000010',
        ]);

        Livewire::test(IngresoRapidoProducto::class)
            ->set('busqueda', '7750003000010')
            ->assertSet('codigoBarra', '7750003000010')
            ->assertSet('productoExistenteId', $producto->id)
            ->assertSet('presentacionExistenteId', $cajaPresentacion->id)
            ->assertSet('crearNuevaPresentacion', false)
            ->set('cantidadIngreso', 3)
            ->set('totalPagado', 150.00)
            ->set('precioVenta', 62.00)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('lote_presentacion', [
            'producto_presentacion_id' => $unidadPresentacion->id,
            'stock' => 3,
        ]);

        $this->assertDatabaseHas('lote_presentacion', [
            'producto_presentacion_id' => $cajaPresentacion->id,
            'stock_inicial' => 3,
            'stock' => 3,
        ]);

        $lotePresentacion = LotePresentacion::where('producto_presentacion_id', $cajaPresentacion->id)->firstOrFail();
        $productoSucursal = ProductoSucursal::where('lote_presentacion_id', $lotePresentacion->id)->firstOrFail();

        $this->assertSame($producto->id, $productoSucursal->producto_id);
        $this->assertSame($this->sucursal->id, $productoSucursal->sucursal_id);
    }

    public function test_product_detail_modal_can_edit_existing_product(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Arroz Costeño',
            'slug' => 'arroz-costeno',
            'descripcion' => 'Descripcion antigua',
            'activo' => true,
        ]);

        ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Bolsa 1kg',
        ]);

        Livewire::test(IngresoRapidoProducto::class)
            ->call('seleccionarProducto', $producto->id)
            ->call('abrirDetalleProducto')
            ->assertSet('showProductoModal', true)
            ->assertSet('editandoProductoModal', false)
            ->call('editarProductoModal')
            ->set('modalNombre', 'Arroz Costeño Premium')
            ->set('modalDescripcion', 'Descripcion actualizada')
            ->call('guardarProductoModal')
            ->assertSet('showProductoModal', false)
            ->assertSet('productoExistenteNombre', 'Arroz Costeño Premium');

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'Arroz Costeño Premium',
            'descripcion' => 'Descripcion actualizada',
        ]);
    }
}
