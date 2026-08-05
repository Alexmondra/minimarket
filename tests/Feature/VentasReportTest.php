<?php

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use App\Support\Reportes\ReporteQueryBuilder;
use App\Support\SucursalContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentasReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Sucursal $sucursal;

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
        session([SucursalContext::SESSION_KEY => $this->sucursal->id]);
    }

    public function test_ventas_y_notas_query_includes_active_credit_notes_and_annulled_docs(): void
    {
        $this->actingAs($this->user);

        // 1. Create active Boleta
        $boletaActiva = Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000001',
            'fecha_emision' => today(),
            'total_neto' => 100.00,
            'estado' => true,
        ]);

        // 2. Create annulled Boleta (estado = false)
        $boletaAnulada = Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000002',
            'fecha_emision' => today(),
            'total_neto' => 150.00,
            'estado' => false,
        ]);

        // 3. Create active Credit Note (estado = true) referencing the annulled Boleta
        $notaCredito = Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'NOTA_CREDITO',
            'serie' => 'BC01',
            'numero' => '00000001',
            'fecha_emision' => today(),
            'total_neto' => 150.00,
            'estado' => true,
        ]);

        // Create reference
        \App\Models\DocumentoReferencium::create([
            'documento_id' => $notaCredito->id,
            'tipo_relacion' => 'NOTA_CREDITO',
            'documento_referenciado_id' => $boletaAnulada->id,
            'tipo_documento_ref' => 'BOLETA',
            'serie_ref' => $boletaAnulada->serie,
            'numero_ref' => $boletaAnulada->numero,
            'motivo_codigo' => '01',
            'motivo_descripcion' => 'Anulación',
            'fecha_emision_ref' => $boletaAnulada->fecha_emision,
        ]);

        $qb = app(ReporteQueryBuilder::class);
        $results = $qb->ventasYNotasBase()->get();
        // Should include active boleta, annulled boleta (since it has active NC reference), and the credit note
        $this->assertCount(3, $results);

        // Check if types are correct
        $types = $results->pluck('tipo_comprobante')->toArray();
        $this->assertContains('BOLETA', $types);
        $this->assertContains('NOTA_CREDITO', $types);

        // Verify if total net sum handles credit notes correctly (using the CASE WHEN logic in loadStats)
        $agg = $qb->ventasYNotasBase()->selectRaw("
            COALESCE(SUM(CASE WHEN tipo_comprobante LIKE 'NOTA_CREDITO%' THEN -total_neto ELSE total_neto END), 0) as total
        ")->first();

        // 100 (active boleta) + 150 (annulled boleta) - 150 (credit note) = 100
        $this->assertEquals(100.00, (float) $agg->total);
    }

    public function test_livewire_component_renders_and_filters_correctly(): void
    {
        $this->actingAs($this->user);

        // Create a boleta and a credit note
        Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000001',
            'fecha_emision' => today(),
            'total_neto' => 100.00,
            'estado' => true,
        ]);

        Documento::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'NOTA_CREDITO',
            'serie' => 'BC01',
            'numero' => '00000001',
            'fecha_emision' => today(),
            'total_neto' => 50.00,
            'estado' => true,
        ]);

        // Test component rendering
        \Livewire\Livewire::test(\App\Livewire\Reportes\VentasReport::class)
            ->assertSee('Reporte de Ventas')
            ->assertSet('tipoReporte', 'resumen')
            ->assertSet('tipoComprobanteFiltro', '')
            // Set type filter to VENTA (only Boleta should be included, total stats is 100)
            ->set('tipoComprobanteFiltro', 'VENTA')
            ->assertSet('stats.total_ventas', '100.00')
            // Set type filter to NOTA_CREDITO (only Credit Note should be included, total stats is -50.00)
            ->set('tipoComprobanteFiltro', 'NOTA_CREDITO')
            ->assertSet('stats.total_ventas', '-50.00')
            // Set format to detailed
            ->set('tipoReporte', 'detalle')
            ->assertSet('tipoReporte', 'detalle')
            // Call export actions to verify no exception is thrown
            ->call('exportarExcel', 'sunat')
            ->assertSuccessful()
            ->call('exportarPdf', 'sunat')
            ->assertSuccessful();
    }
}
