<?php

namespace Tests\Feature;

use App\Livewire\Compras\Components\DetalleCompra;
use App\Models\Compra;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\LotePresentacionMerma;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class MermasLoteTest extends TestCase
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

    public function test_it_saves_stock_inicial_alongside_stock_on_agregar_lote(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DetalleCompra::class, [
            'compraId' => $this->compra->id,
            'sucursalId' => $this->sucursal->id
        ])
        ->call('seleccionarProducto', $this->producto->id, $this->producto->nombre)
        ->set('codigoLote', 'LOT-MERMA-1')
        ->set('presentacionesDisponibles.0.cantidad', 50)
        ->set('presentacionesDisponibles.0.total_pagado', 250.00)
        ->set('presentacionesDisponibles.0.precio_venta', 8.00)
        ->call('agregarLote')
        ->assertHasNoErrors();

        $this->assertDatabaseHas('lote_presentacion', [
            'stock_inicial' => 50,
            'stock' => 50,
            'precio_compra' => 5.00,
        ]);
    }

    public function test_it_can_register_mermas_and_updates_stocks_and_kardex(): void
    {
        $this->actingAs($this->user);

        // 1. Manually create a Lote and LotePresentacion
        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-MERMA-2',
            'producto_nombre' => $this->producto->nombre,
            'precio_compra' => 100.00,
            'estado_lote' => 'activo',
        ]);

        $lotePresentacion = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $this->presentacion->id,
            'stock_inicial' => 30,
            'stock' => 30,
            'precio_compra' => 3.33,
        ]);

        // 2. Perform merma logic manually to test calculations and DB constraints
        $cantidadMerma = 5;
        $tipoMerma = 'vencido';
        $motivo = 'Expiración de fecha de garantía';

        DB::transaction(function () use ($lotePresentacion, $cantidadMerma, $tipoMerma, $motivo) {
            $nuevoStock = $lotePresentacion->stock - $cantidadMerma;
            $lotePresentacion->update(['stock' => $nuevoStock]);

            LotePresentacionMerma::create([
                'lote_presentacion_id' => $lotePresentacion->id,
                'cantidad' => $cantidadMerma,
                'tipo_merma' => $tipoMerma,
                'motivo' => $motivo,
                'user_id' => $this->user->id,
            ]);

            MovimientoInventario::create([
                'empresa_id' => $this->empresa->id,
                'sucursal_id' => $this->sucursal->id,
                'producto_nombre' => $this->producto->nombre,
                'producto_presentacion_id' => $this->presentacion->id,
                'tipo' => 'salida_merma',
                'cantidad' => -$cantidadMerma,
                'motivo' => "Merma ({$tipoMerma}) - Lote {$lotePresentacion->lote->codigo_lote}: " . $motivo,
                'referencia' => "LotePresentacion:{$lotePresentacion->id}",
                'user_id' => $this->user->id,
                'stock_final' => $nuevoStock,
            ]);
        });

        // 3. Assert stock decreased
        $this->assertEquals(25, $lotePresentacion->fresh()->stock);
        $this->assertEquals(30, $lotePresentacion->fresh()->stock_inicial);

        // 4. Assert Merma recorded
        $this->assertDatabaseHas('lote_presentacion_mermas', [
            'lote_presentacion_id' => $lotePresentacion->id,
            'cantidad' => 5,
            'tipo_merma' => 'vencido',
            'motivo' => $motivo,
            'user_id' => $this->user->id,
        ]);

        // 5. Assert Kardex recorded negative salida_merma
        $this->assertDatabaseHas('movimientos_inventario', [
            'sucursal_id' => $this->sucursal->id,
            'producto_presentacion_id' => $this->presentacion->id,
            'tipo' => 'salida_merma',
            'cantidad' => -5,
            'stock_final' => 25,
        ]);
    }
}
