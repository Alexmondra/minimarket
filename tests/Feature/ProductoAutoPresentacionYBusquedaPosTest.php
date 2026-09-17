<?php

namespace Tests\Feature;

use App\Livewire\Ventas\RegistrarVenta;
use App\Models\Empresa;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\SessioneCaja;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductoAutoPresentacionYBusquedaPosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Sucursal $sucursal;

    protected function setUp(): void
    {
        parent::setUp();

        UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'codigo_sunat' => 'NIU',
            'tipo' => 'unidad',
        ]);

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
            'name' => 'Cajero Test',
            'email' => 'cajero@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user->id);

        SessioneCaja::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.00,
            'estado' => true,
        ]);
    }

    public function test_producto_sin_presentacion_se_auto_aprovisiona_y_aparece_como_sin_stock(): void
    {
        $this->actingAs($this->user);

        // Simulamos un producto que se creó en Catálogo General sin presentaciones
        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Detergente Ariel 500g',
            'codigo_interno' => 'ARIEL500',
            'slug' => 'detergente-ariel-500g',
            'afecto_igv' => true,
            'activo' => true,
        ]);

        $this->assertDatabaseMissing('producto_presentacion', [
            'producto_id' => $producto->id,
        ]);

        // Al buscarlo en el POS por nombre
        $component = Livewire::test(RegistrarVenta::class)
            ->set('searchProducto', 'Ariel')
            ->assertCount('productosResultados', 0)
            ->assertCount('productosSinStockResultados', 1)
            ->assertSet('productosSinStockResultados.0.nombre', 'Detergente Ariel 500g')
            ->assertSet('productosSinStockResultados.0.codigo', 'ARIEL500');

        // Se auto-aprovisionó la presentación "Unidad" y su código de barra
        $this->assertDatabaseHas('producto_presentacion', [
            'producto_id' => $producto->id,
            'tipo_presentacion' => 'Unidad',
        ]);

        $presentacion = ProductoPresentacion::where('producto_id', $producto->id)->first();
        $this->assertDatabaseHas('producto_presentacion_barras', [
            'producto_presentacion_id' => $presentacion->id,
            'codigo_barra' => 'ARIEL500',
        ]);

        // Al buscarlo por código de barra, también lo encuentra sin stock
        $component->set('searchProducto', 'ARIEL500')
            ->assertCount('productosSinStockResultados', 1);
    }

    public function test_ingreso_rapido_agrega_cantidad_especificada_al_carrito(): void
    {
        $this->actingAs($this->user);

        Livewire::test(RegistrarVenta::class)
            ->set('searchProducto', '7751234') // Código corto alfanumérico o numérico
            ->call('abrirIngresoRapido')
            ->assertSet('showIngresoRapidoModal', true)
            // Se detectó como código de barra automáticamente
            ->assertSet('ingresoRapidoCodigoBarra', '7751234')
            ->set('ingresoRapidoProductoNombre', 'Chocolate Sublime 30g')
            ->set('ingresoRapidoPresentacionNombre', 'Unidad')
            ->set('ingresoRapidoCantidad', 4)
            ->set('ingresoRapidoPrecioVenta', 2.50)
            ->call('guardarIngresoRapido')
            ->assertSet('showIngresoRapidoModal', false)
            ->assertCount('cartItems', 1)
            ->assertSet('cartItems.0.nombre', 'Chocolate Sublime 30g')
            // Comprobamos que el carrito tiene las 4 unidades ingresadas y no 1
            ->assertSet('cartItems.0.cantidad', 4.0);
    }
}
