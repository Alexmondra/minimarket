<?php

namespace Tests\Feature;

use App\Livewire\Ventas\RegistrarVenta;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\DetalleDocumento;
use App\Models\Empresa;
use App\Models\SessioneCaja;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UniMedida;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrarVentaSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Sucursal $sucursal;
    private SessioneCaja $caja;

    protected function setUp(): void
    {
        parent::setUp();

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
            'email' => 'ventas@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user);

        $this->caja = SessioneCaja::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.00,
            'estado' => true,
        ]);
    }

    public function test_it_can_open_and_close_search_modal(): void
    {
        $this->actingAs($this->user);

        Livewire::test(RegistrarVenta::class)
            ->assertSet('showBuscarVentaModal', false)
            ->call('openBuscarVentaModal')
            ->assertSet('showBuscarVentaModal', true)
            ->assertSet('searchVentaQuery', '')
            ->assertSet('ventasResultados', [])
            ->assertSet('selectedVentaId', null)
            ->assertSet('selectedVentaDetalles', null)
            ->call('cerrarBuscarVentaModal')
            ->assertSet('showBuscarVentaModal', false);
    }

    public function test_it_can_search_sales(): void
    {
        $cliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'tipo_documento' => 'DNI',
            'documento' => '44445555',
            'nombre' => 'JUAN',
            'apellido' => 'PEREZ',
        ]);

        $doc1 = Documento::create([
            'caja_sesion_id' => $this->caja->id,
            'sucursal_id' => $this->sucursal->id,
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000001',
            'fecha_emision' => now()->toDateString(),
            'total_bruto' => 100.00,
            'total_descuento' => 0.00,
            'subtotal' => 100.00,
            'total_neto' => 100.00,
            'total_igv' => 18.00,
            'porcentaje_igv' => 18.00,
            'tipo_moneda' => 'PEN',
            'medio_pago' => 'EFECTIVO',
            'monto_recibido' => 100.00,
            'vuelto' => 0.00,
            'estado' => true,
        ]);

        $this->actingAs($this->user);

        Livewire::test(RegistrarVenta::class)
            ->call('openBuscarVentaModal')
            ->set('searchVentaQuery', 'B001')
            ->assertCount('ventasResultados', 1)
            ->assertSet('ventasResultados.0.id', $doc1->id)
            ->set('searchVentaQuery', 'JUAN')
            ->assertCount('ventasResultados', 1)
            ->set('searchVentaQuery', 'nonexistent')
            ->assertCount('ventasResultados', 0);
    }

    public function test_it_can_view_sale_details(): void
    {
        $cliente = Cliente::create([
            'empresa_id' => $this->empresa->id,
            'tipo_documento' => 'DNI',
            'documento' => '44445555',
            'nombre' => 'JUAN',
            'apellido' => 'PEREZ',
        ]);

        $doc = Documento::create([
            'caja_sesion_id' => $this->caja->id,
            'sucursal_id' => $this->sucursal->id,
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000001',
            'fecha_emision' => now()->toDateString(),
            'total_bruto' => 100.00,
            'total_descuento' => 0.00,
            'subtotal' => 100.00,
            'total_neto' => 100.00,
            'total_igv' => 18.00,
            'porcentaje_igv' => 18.00,
            'tipo_moneda' => 'PEN',
            'medio_pago' => 'EFECTIVO',
            'monto_recibido' => 100.00,
            'vuelto' => 0.00,
            'estado' => true,
        ]);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Gaseosa Inka Cola 1L',
            'slug' => 'gaseosa-inka-cola-1l',
            'activo' => true,
        ]);

        $unidad = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);

        $presentacion = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Botella',
        ]);

        $lote = \App\Models\Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-001',
            'producto_nombre' => $producto->nombre,
            'precio_compra' => 3.50,
            'estado_lote' => 'activo',
        ]);

        DetalleDocumento::create([
            'documento_id' => $doc->id,
            'lote_id' => $lote->id,
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'producto_presentacion_id' => $presentacion->id,
            'cantidad' => 2.000,
            'precio_unitario' => 5.00,
            'valor_unitario' => 4.24,
            'total_linea' => 10.00,
        ]);

        $this->actingAs($this->user);

        Livewire::test(RegistrarVenta::class)
            ->call('openBuscarVentaModal')
            ->call('verDetalleVenta', $doc->id)
            ->assertSet('selectedVentaId', $doc->id)
            ->assertSet('selectedVentaDetalles.comprobante', 'BOLETA B001-00000001')
            ->assertCount('selectedVentaDetalles.items', 1)
            ->assertSet('selectedVentaDetalles.items.0.producto_nombre', 'Gaseosa Inka Cola 1L')
            ->assertSet('selectedVentaDetalles.items.0.presentacion', 'Botella');
    }
}
