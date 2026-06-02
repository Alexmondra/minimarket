<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\DetalleDocumento;
use App\Models\Empresa;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use App\Support\Reportes\MetricCalculator;
use App\Support\SucursalContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricCalculatorTest extends TestCase
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

        // Put active sucursal ID in session
        session([SucursalContext::SESSION_KEY => $this->sucursal->id]);

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

    public function test_ventas_acumuladas_semana(): void
    {
        $this->actingAs($this->user);

        // Create some sales for today
        $doc = Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => 1,
            'fecha_emision' => today(),
            'total_neto' => 100.00,
            'estado' => true,
        ]);

        $calc = app(MetricCalculator::class);
        $result = $calc->ventasAcumuladasSemana();

        $this->assertCount(7, $result['labels']);
        $this->assertCount(7, $result['data']);
        
        $carbonDay = today()->dayOfWeekIso; // 1 = Monday, 7 = Sunday
        $dayIndex = $carbonDay - 1;

        $this->assertEquals(100.00, $result['data'][$dayIndex]);
    }

    public function test_ventas_diarias_mes(): void
    {
        $this->actingAs($this->user);

        $doc = Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => 2,
            'fecha_emision' => today(),
            'total_neto' => 150.00,
            'estado' => true,
        ]);

        $calc = app(MetricCalculator::class);
        $result = $calc->ventasDiariasMes(today()->year, today()->month);

        $this->assertEquals(150.00, $result['data'][today()->day - 1]);
    }

    public function test_ganancias_ingresos_ventas(): void
    {
        $this->actingAs($this->user);

        // Create a lot in current month
        $lote = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-01',
            'producto_nombre' => $this->producto->nombre,
            'precio_compra' => 50.00,
            'estado_lote' => 'activo',
            'created_at' => today(),
        ]);

        LotePresentacion::create([
            'lote_id' => $lote->id,
            'producto_presentacion_id' => $this->presentacion->id,
            'stock_inicial' => 10,
            'stock' => 10,
            'precio_compra' => 5.00,
        ]);

        // Create a sale for this lote
        $doc = Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => 3,
            'fecha_emision' => today(),
            'total_neto' => 80.00,
            'estado' => true,
        ]);

        DetalleDocumento::create([
            'documento_id' => $doc->id,
            'lote_id' => $lote->id,
            'producto_id' => $this->producto->id,
            'producto_nombre' => $this->producto->nombre,
            'producto_presentacion_id' => $this->presentacion->id,
            'cantidad' => 2,
            'precio_unitario' => 40.00,
            'valor_unitario' => 33.90,
            'subtotal_neto' => 80.00,
            'total_linea' => 80.00,
        ]);

        $calc = app(MetricCalculator::class);
        $result = $calc->gananciasIngresosVentas(1);

        // Inversión: 10 units * S/ 5.00 = S/ 50.00
        $this->assertEquals(50.00, $result['ingresos'][0]);
        
        // Ventas: 2 units * S/ 40.00 = S/ 80.00
        $this->assertEquals(80.00, $result['ventas'][0]);

        // Ganancia Real: 2 units * (S/ 40.00 - S/ 5.00) = S/ 70.00 (since it has no purchase associated, it defaults to paid)
        $this->assertEquals(70.00, $result['ganancias'][0]);
    }
}
