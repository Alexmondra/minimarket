<?php

namespace Tests\Feature;

use App\Livewire\Ventas\RegistrarVenta;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Models\SessioneCaja;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PosDescompresionAutomaticaTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Sucursal $sucursal;
    private User $user;
    private UniMedida $unidadMedida;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'ventas.crear', 'guard_name' => 'web']);

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
            'name' => 'Vendedor',
            'email' => 'vendedor@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->givePermissionTo('ventas.crear');
        $this->sucursal->users()->attach($this->user);

        SessioneCaja::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.0,
            'estado' => true,
        ]);

        $this->unidadMedida = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);
    }

    public function test_it_automatically_decompresses_box_and_adds_unit_to_cart_in_pos(): void
    {
        $this->actingAs($this->user);

        // 1. Crear producto con Unidad (sin stock) y Caja x 12 (con 2 cajas en stock)
        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Inka Kola 500ml',
            'slug' => 'inka-kola-500ml',
            'codigo_interno' => 'INKA-500M',
            'activo' => true,
        ]);

        $presUnidad = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidadMedida->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
            'es_pesable' => false,
        ]);

        $presCaja = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'presentacion_base_id' => $presUnidad->id,
            'unidad_medida_id' => $this->unidadMedida->id,
            'cantidad' => 12,
            'tipo_presentacion' => 'Paquete x12',
            'es_pesable' => false,
        ]);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-INKA-01',
            'producto_nombre' => $producto->nombre,
            'precio_compra' => 24.00,
            'estado_lote' => 'activo',
        ]);

        // Stock solo en la caja (2 cajas)
        $lotePresCaja = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $presCaja->id,
            'stock' => 2.0,
        ]);

        ProductoSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $this->sucursal->id,
            'lote_presentacion_id' => $lotePresCaja->id,
            'stock_minimo' => 0,
            'precio' => 36.00,
            'activo' => true,
        ]);

        // Precio de referencia de la unidad (sin stock)
        $lotePresUnidadVacia = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $presUnidad->id,
            'stock' => 0.0,
        ]);

        ProductoSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $this->sucursal->id,
            'lote_presentacion_id' => $lotePresUnidadVacia->id,
            'stock_minimo' => 0,
            'precio' => 3.50,
            'activo' => true,
        ]);

        // 2. Probar en el componente POS que al agregar la unidad (que tiene stock 0), se descomprime y se mete al carrito
        Livewire::test(RegistrarVenta::class)
            ->call('seleccionarProductoSinStock', $presUnidad->id)
            ->assertCount('cartItems', 1)
            ->assertSet('cartItems.0.producto_presentacion_id', $presUnidad->id)
            ->assertSet('cartItems.0.cantidad', 1.0)
            ->assertSet('cartItems.0.precio', 3.50);

        // 3. Verificar que la caja se redujo de 2 a 1
        $this->assertEquals(1.0, (float) $lotePresCaja->fresh()->stock);

        // 4. Verificar que se sumaron 12 unidades al lote de unidades
        $this->assertEquals(12.0, (float) $lotePresUnidadVacia->fresh()->stock);

        // 5. Verificar movimientos de inventario
        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $this->sucursal->id,
            'producto_presentacion_id' => $presCaja->id,
            'tipo' => 'salida_descompresion',
            'cantidad' => -1.0,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $this->sucursal->id,
            'producto_presentacion_id' => $presUnidad->id,
            'tipo' => 'entrada_descompresion',
            'cantidad' => 12.0,
        ]);
    }

    public function test_it_smartly_finds_parent_presentation_even_if_presentacion_base_id_was_null(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Galleta Oreo',
            'slug' => 'galleta-oreo',
            'codigo_interno' => 'OREO-1234',
            'activo' => true,
        ]);

        $presUnidad = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidadMedida->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
            'es_pesable' => false,
        ]);

        // Paquete creado SIN presentacion_base_id
        $presPaquete = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'presentacion_base_id' => null, // No vinculado previamente
            'unidad_medida_id' => $this->unidadMedida->id,
            'cantidad' => 6,
            'tipo_presentacion' => 'Sixpack x6',
            'es_pesable' => false,
        ]);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-OREO-01',
            'producto_nombre' => $producto->nombre,
            'precio_compra' => 6.00,
            'estado_lote' => 'activo',
        ]);

        $lotePresPaquete = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $presPaquete->id,
            'stock' => 3.0,
        ]);

        ProductoSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $this->sucursal->id,
            'lote_presentacion_id' => $lotePresPaquete->id,
            'stock_minimo' => 0,
            'precio' => 9.00,
            'activo' => true,
        ]);

        // Al intentar agregar la unidad, el sistema detecta inteligentemente el Sixpack
        Livewire::test(RegistrarVenta::class)
            ->call('agregarProductoDirecto', $presUnidad->id)
            ->assertCount('cartItems', 1)
            ->assertSet('cartItems.0.producto_presentacion_id', $presUnidad->id);

        // El sixpack se reduce en 1 (de 3 a 2)
        $this->assertEquals(2.0, (float) $lotePresPaquete->fresh()->stock);

        // Se auto-vinculó presentacion_base_id para el futuro
        $this->assertEquals($presUnidad->id, $presPaquete->fresh()->presentacion_base_id);
    }

    public function test_it_opens_ingreso_rapido_if_no_parent_has_stock(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Chicle Trident',
            'slug' => 'chicle-trident',
            'codigo_interno' => 'TRID-0001',
            'activo' => true,
        ]);

        $presUnidad = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $this->unidadMedida->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
            'es_pesable' => false,
        ]);

        // No hay stock de nada
        Livewire::test(RegistrarVenta::class)
            ->call('seleccionarProductoSinStock', $presUnidad->id)
            ->assertCount('cartItems', 0)
            ->assertSet('showIngresoRapidoModal', true)
            ->assertSet('ingresoRapidoPresentacionId', $presUnidad->id);
    }
}
