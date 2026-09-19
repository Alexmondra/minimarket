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
use App\Models\ProductoPresentacionBarra;
use App\Models\ProductoSucursal;
use App\Models\Lote;
use App\Models\LotePresentacion;
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

    public function test_ingreso_rapido_permite_vaciar_y_tipear_numeros_como_string(): void
    {
        $this->actingAs($this->user);

        Livewire::test(RegistrarVenta::class)
            ->call('abrirIngresoRapido')
            ->assertSet('showIngresoRapidoModal', true)
            ->assertSet('ingresoRapidoCantidad', 1.0)
            // Permite borrar a vacío ("") sin que falle por tipado estricto
            ->set('ingresoRapidoCantidad', '')
            ->assertSet('ingresoRapidoCantidad', '')
            ->set('ingresoRapidoPrecioVenta', '')
            ->assertSet('ingresoRapidoPrecioVenta', '')
            ->set('ingresoRapidoPresentacionCantidad', '')
            ->assertSet('ingresoRapidoPresentacionCantidad', '')
            // Permite colocar valores nuevos
            ->set('ingresoRapidoCantidad', '15')
            ->set('ingresoRapidoPrecioVenta', '3.50')
            ->set('ingresoRapidoPresentacionCantidad', '6')
            ->assertSet('ingresoRapidoCantidad', '15')
            ->assertSet('ingresoRapidoPrecioVenta', '3.50')
            ->assertSet('ingresoRapidoPresentacionCantidad', '6');
    }

    public function test_it_generates_clean_sku_codigo_interno(): void
    {
        $codigo1 = Producto::generarCodigoInterno('PIQUEO SNAX 110G');
        $this->assertStringStartsWith('PIQU-', $codigo1);
        $this->assertSame(9, strlen($codigo1)); // 4 letras + '-' + 4 caracteres

        $codigo2 = Producto::generarCodigoInterno('A');
        $this->assertStringStartsWith('AXXX-', $codigo2);
    }

    public function test_it_opens_vincular_codigo_modal_when_barcode_was_in_codigo_interno(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PIQUEO SNAX 110G',
            'slug' => 'piqueo-snax-110g',
            'codigo_interno' => '7758574006722',
            'activo' => true,
        ]);

        $unidad = UniMedida::firstOrCreate(
            ['abreviatura' => 'und'],
            ['nombre' => 'Unidad', 'activo' => true]
        );

        $pres1 = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Bolsa 55g',
        ]);
        ProductoPresentacionBarra::create([
            'producto_presentacion_id' => $pres1->id,
            'codigo_barra' => '7758574004230',
        ]);

        $pres2 = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Bolsa 110g',
        ]);

        // Simular escaneo de 7758574006722
        Livewire::test(RegistrarVenta::class)
            ->set('searchProducto', '7758574006722')
            ->call('procesarEnterBuscador')
            ->assertSet('showVincularCodigoModal', true)
            ->assertSet('vincularCodigoBarra', '7758574006722')
            ->assertSet('vincularProductoId', $producto->id)
            ->assertCount('vincularPresentaciones', 2);
    }

    public function test_it_links_barcode_to_presentation_and_updates_codigo_interno_to_sku(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PIQUEO SNAX 110G',
            'slug' => 'piqueo-snax-110g',
            'codigo_interno' => '7758574006722',
            'activo' => true,
        ]);

        $unidad = UniMedida::firstOrCreate(
            ['abreviatura' => 'und'],
            ['nombre' => 'Unidad', 'activo' => true]
        );

        $pres = ProductoPresentacion::create([
            'producto_id' => $producto->id,
            'unidad_medida_id' => $unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Bolsa 110g',
        ]);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-001',
            'producto_nombre' => $producto->nombre,
            'precio_compra' => 2.00,
            'estado_lote' => 'activo',
        ]);

        $lotePres = LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $pres->id,
            'stock' => 10,
            'estado' => 'activo',
        ]);

        ProductoSucursal::create([
            'producto_id' => $producto->id,
            'sucursal_id' => $this->sucursal->id,
            'lote_presentacion_id' => $lotePres->id,
            'precio' => 3.50,
            'activo' => true,
        ]);

        Livewire::test(RegistrarVenta::class)
            ->set('vincularProductoId', $producto->id)
            ->set('vincularCodigoBarra', '7758574006722')
            ->set('showVincularCodigoModal', true)
            ->call('seleccionarPresentacionParaVincular', $pres->id)
            ->assertSet('showVincularCodigoModal', false)
            ->assertCount('cartItems', 1);

        // Verificar que el código de barra ahora pertenece a la presentación
        $this->assertDatabaseHas('producto_presentacion_barras', [
            'producto_presentacion_id' => $pres->id,
            'codigo_barra' => '7758574006722',
        ]);

        // Verificar que el producto ya no tiene el código de barra en codigo_interno
        $producto->refresh();
        $this->assertNotSame('7758574006722', $producto->codigo_interno);
        $this->assertStringStartsWith('PIQU-', $producto->codigo_interno);
    }

    public function test_it_frees_barcode_when_desvincular_codigo_sin_asignar_is_called(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PIQUEO SNAX 110G',
            'slug' => 'piqueo-snax-110g',
            'codigo_interno' => '7758574006722',
            'activo' => true,
        ]);

        Livewire::test(RegistrarVenta::class)
            ->set('vincularProductoId', $producto->id)
            ->set('vincularCodigoBarra', '7758574006722')
            ->set('showVincularCodigoModal', true)
            ->call('desvincularCodigoSinAsignar')
            ->assertSet('showVincularCodigoModal', false);

        $producto->refresh();
        $this->assertNotSame('7758574006722', $producto->codigo_interno);
        $this->assertStringStartsWith('PIQU-', $producto->codigo_interno);
    }

    public function test_it_creates_new_presentation_and_links_barcode(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PIQUEO SNAX 110G',
            'slug' => 'piqueo-snax-110g',
            'codigo_interno' => '7758574006722',
            'activo' => true,
        ]);

        $unidad = UniMedida::firstOrCreate(
            ['abreviatura' => 'und'],
            ['nombre' => 'Unidad', 'activo' => true]
        );

        Livewire::test(RegistrarVenta::class)
            ->set('vincularProductoId', $producto->id)
            ->set('vincularCodigoBarra', '7758574006722')
            ->set('showVincularCodigoModal', true)
            ->call('toggleFormularioNuevaPresentacion')
            ->assertSet('mostrarFormularioNuevaPresentacion', true)
            ->set('vincularNuevaPresentacionNombre', 'Bolsaza 110g')
            ->set('vincularNuevaPresentacionCantidad', 1)
            ->call('crearYVincularNuevaPresentacion')
            ->assertSet('showVincularCodigoModal', false);

        // Verificar que se creó la nueva presentación con ese nombre
        $nuevaPres = ProductoPresentacion::where('producto_id', $producto->id)
            ->where('tipo_presentacion', 'Bolsaza 110g')
            ->first();
        $this->assertNotNull($nuevaPres);

        // Verificar que el código de barra ahora pertenece a esa nueva presentación
        $this->assertDatabaseHas('producto_presentacion_barras', [
            'producto_presentacion_id' => $nuevaPres->id,
            'codigo_barra' => '7758574006722',
        ]);

        // Verificar que el producto ya no tiene el código de barra en codigo_interno
        $producto->refresh();
        $this->assertNotSame('7758574006722', $producto->codigo_interno);
        $this->assertStringStartsWith('PIQU-', $producto->codigo_interno);
    }

    public function test_it_opens_quick_entry_modal_for_new_product_and_frees_barcode_from_old_product(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PIQUEO SNAX 110G',
            'slug' => 'piqueo-snax-110g',
            'codigo_interno' => '7758574006722',
            'activo' => true,
        ]);

        Livewire::test(RegistrarVenta::class)
            ->set('vincularProductoId', $producto->id)
            ->set('vincularCodigoBarra', '7758574006722')
            ->set('showVincularCodigoModal', true)
            ->call('crearNuevoProductoDesdeVincularModal')
            ->assertSet('showVincularCodigoModal', false)
            ->assertSet('showIngresoRapidoModal', true)
            ->assertSet('ingresoRapidoCrearProducto', true)
            ->assertSet('ingresoRapidoCodigoBarra', '7758574006722')
            ->assertSet('ingresoRapidoPresentacionNombre', 'Unidad')
            ->assertSet('ingresoRapidoPresentacionCantidad', 1);

        // El producto anterior debe tener su nuevo SKU generado y su código de barra liberado
        $producto->refresh();
        $this->assertNotSame('7758574006722', $producto->codigo_interno);
        $this->assertStringStartsWith('PIQU-', $producto->codigo_interno);
    }
}


