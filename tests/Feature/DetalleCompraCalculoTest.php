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
}
