<?php

namespace Tests\Feature;

use App\Jobs\ProcesarFacturaSunat;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentacionBarra;
use App\Models\ProductoSucursal;
use App\Models\SessioneCaja;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use App\Support\Ventas\RegistrarVenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DecompresionStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Prevent jobs from running during test if not needed
        Event::fake(ProcesarFacturaSunat::class);
    }

    public function test_it_decompresses_parent_presentation_stock_when_base_stock_is_insufficient(): void
    {
        // 1. Setup basic environment
        $ubigeo = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);

        $empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
            'incluido_tributo' => true,
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->id,
            'nombre_sucursal' => 'CENTRO',
            'direccion' => 'AV. CENTRAL 123',
            'impuesto_porcentaje' => 18,
        ]);

        $user = User::create([
            'empresa_id' => $empresa->id,
            'name' => 'Vendedor',
            'email' => 'vendedor@example.com',
            'password' => bcrypt('password'),
        ]);

        $sucursal->users()->attach($user);

        $sesion = SessioneCaja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $user->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.0,
            'estado' => true,
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'nombre' => 'Juan',
            'apellido' => 'Perez',
        ]);

        $unidadMedida = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);

        // 2. Create product
        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Inca Kola 600ml',
            'slug' => 'inca-kola-600ml',
            'codigo_interno' => 'IK-600',
            'afecto_igv' => true,
            'activo' => true,
        ]);

        // 3. Create presentations
        $presUnidad = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $unidadMedida->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
            'es_pesable' => false,
        ]);

        $presCaja = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'presentacion_base_id' => $presUnidad->id, // points to Unit!
            'unidad_medida_id' => $unidadMedida->id,
            'cantidad' => 12, // 12 units in a box
            'tipo_presentacion' => 'Caja x 12',
            'es_pesable' => false,
        ]);

        // 4. Create Lot and LotePresentacion for Box only (Unit starts with 0 stock)
        $lote = Lote::create([
            'sucursal_id' => $sucursal->id,
            'codigo_lote' => 'LOTE-001',
            'producto_nombre' => $producto->nombre,
            'fecha_fabricacion' => now()->subMonth(),
            'fecha_vencimiento' => now()->addYear(),
            'precio_compra' => 24.00,
            'estado_lote' => 'activo',
        ]);

        $lotePresCaja = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $presCaja->id,
            'stock' => 1.0, // We have 1 box
        ]);

        // Define box price
        $prodSucCaja = ProductoSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
            'lote_presentacion_id' => $lotePresCaja->id,
            'stock_minimo' => 0,
            'precio' => 30.00, // 30 PEN per box
            'activo' => true,
        ]);

        // Define a reference price for Unit (so decompression knows how to price units)
        // We create a template/reference record with stock 0
        $lotePresUnidadVacia = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $presUnidad->id,
            'stock' => 0.0,
        ]);

        ProductoSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $sucursal->id,
            'lote_presentacion_id' => $lotePresUnidadVacia->id,
            'stock_minimo' => 0,
            'precio' => 3.00, // 3.00 PEN per unit
            'activo' => true,
        ]);

        // 5. Try selling 5 units
        $action = app(RegistrarVenta::class);

        $payload = [
            'sucursal_id' => $sucursal->id,
            'tipo_comprobante' => 'BOLETA',
            'medio_pago' => 'EFECTIVO',
            'monto_recibido' => 15.00,
            'porcentaje_igv' => 18,
            'cliente' => [
                'documento' => '12345678',
            ],
            'items' => [
                [
                    'producto_presentacion_id' => $presUnidad->id,
                    'cantidad' => 5.0,
                    'precio_unitario' => 3.00,
                ],
            ],
        ];

        // Execute sale
        $documento = $action->ejecutar($user, $payload);

        // 6. Assert decompression occurred
        // We should have 0 boxes left in Lote-001
        $this->assertEquals(0, $lotePresCaja->fresh()->stock);

        // We should have a base LotePresentacion record for Unit in Lote-001 with stock: 12 - 5 = 7 units
        $baseLotePres = LotePresentacion::query()
            ->where('lote_id', $lote->id)
            ->where('producto_presentacion_id', $presUnidad->id)
            ->first();

        $this->assertNotNull($baseLotePres);
        $this->assertEquals(7.0, (float) $baseLotePres->stock);

        // Check that inventory movements were recorded:
        // - salida_descompresion of 1 Box
        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $sucursal->id,
            'producto_presentacion_id' => $presCaja->id,
            'tipo' => 'salida_descompresion',
            'cantidad' => -1.0,
        ]);

        // - entrada_descompresion of 12 Units
        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $sucursal->id,
            'producto_presentacion_id' => $presUnidad->id,
            'tipo' => 'entrada_descompresion',
            'cantidad' => 12.0,
        ]);

        // - salida_venta of 5 Units
        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $sucursal->id,
            'producto_presentacion_id' => $presUnidad->id,
            'tipo' => 'salida_venta',
            'cantidad' => -5.0,
        ]);
    }

    public function test_it_resolves_multiple_barcodes_for_a_presentation(): void
    {
        $empresa = Empresa::create([
            'ruc' => '20999999999',
            'razon_social' => 'TEST EMPRESA',
            'direccion_fiscal' => 'AV. FISCAL 456',
        ]);

        $producto = Producto::create([
            'empresa_id' => $empresa->id,
            'nombre' => 'Producto Barras',
            'slug' => 'prod-barras',
            'codigo_interno' => 'PB-1',
            'activo' => true,
        ]);

        $presentacion = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
        ]);

        // Associate multiple barcodes
        $barra1 = ProductoPresentacionBarra::create([
            'producto_presentacion_id' => $presentacion->id,
            'codigo_barra' => '7750123456789',
        ]);

        $barra2 = ProductoPresentacionBarra::create([
            'producto_presentacion_id' => $presentacion->id,
            'codigo_barra' => '7750987654321',
        ]);

        // Query by barcode 1
        $resolved1 = ProductoPresentacion::query()
            ->whereHas('barras', fn ($q) => $q->where('codigo_barra', '7750123456789'))
            ->first();

        $this->assertNotNull($resolved1);
        $this->assertEquals($presentacion->id, $resolved1->id);

        // Query by barcode 2
        $resolved2 = ProductoPresentacion::query()
            ->whereHas('barras', fn ($q) => $q->where('codigo_barra', '7750987654321'))
            ->first();

        $this->assertNotNull($resolved2);
        $this->assertEquals($presentacion->id, $resolved2->id);
    }
}
