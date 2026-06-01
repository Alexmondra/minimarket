<?php

namespace Tests\Feature;

use App\Livewire\Almacen\AjusteStock;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AjusteStockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Sucursal $sucursal;
    private Producto $producto;
    private ProductoPresentacion $presentacion;

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
            'name' => 'Operator',
            'email' => 'operator@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user);

        $this->producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Coca Cola 500ml',
            'slug' => 'coca-cola-500ml',
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
            'tipo_presentacion' => 'Botella',
        ]);
    }

    public function test_modal_opens_and_resets_state_correctly(): void
    {
        $this->actingAs($this->user);

        Livewire::test(AjusteStock::class)
            ->call('abrirEntrada')
            ->set('cantidad', 10)
            ->set('loteCodigo', 'LOTE-TEST')
            ->call('cerrarModal')
            ->assertSet('cantidad', 1)
            ->assertSet('loteCodigo', '');
    }

    public function test_unit_price_is_automatically_calculated_on_entry(): void
    {
        $this->actingAs($this->user);

        Livewire::test(AjusteStock::class)
            ->call('abrirEntrada')
            ->set('cantidad', 20)
            ->set('totalPagado', 100.00)
            ->assertSet('costo', 5.00);
    }

    public function test_output_requires_available_stock(): void
    {
        $this->actingAs($this->user);

        // Try to adjust output with 0 stock
        Livewire::test(AjusteStock::class)
            ->call('abrirSalida')
            ->set('sucursalId', $this->sucursal->id)
            ->call('seleccionarPresentacion', [
                'producto_id' => $this->producto->id,
                'presentacion_id' => $this->presentacion->id,
                'producto_nombre' => $this->producto->nombre,
                'tipo_presentacion' => $this->presentacion->tipo_presentacion,
                'unidad_medida_abr' => 'und',
                'codigo_interno' => null
            ])
            ->set('cantidad', 5)
            ->set('tipoMerma', 'roto')
            ->call('guardar')
            ->assertHasErrors(['cantidad']);
    }

    public function test_output_is_successful_when_stock_is_sufficient(): void
    {
        $this->actingAs($this->user);

        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-OK',
            'producto_nombre' => $this->producto->nombre,
            'precio_compra' => 2.00,
            'estado_lote' => 'activo',
        ]);

        LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $this->presentacion->id,
            'stock_inicial' => 10,
            'stock' => 10,
            'precio_compra' => 2.00,
        ]);

        Livewire::test(AjusteStock::class)
            ->call('abrirSalida')
            ->set('sucursalId', $this->sucursal->id)
            ->call('seleccionarPresentacion', [
                'producto_id' => $this->producto->id,
                'presentacion_id' => $this->presentacion->id,
                'producto_nombre' => $this->producto->nombre,
                'tipo_presentacion' => $this->presentacion->tipo_presentacion,
                'unidad_medida_abr' => 'und',
                'codigo_interno' => null
            ])
            ->set('cantidad', 4)
            ->set('tipoMerma', 'roto')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('showConfirmStep', true);
    }
}
