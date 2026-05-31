<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\Serie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SucursalAutoSeriesTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa1;
    protected Empresa $empresa2;
    protected Ubigeo $ubigeoLima;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa1 = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC 1',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
        ]);

        $this->empresa2 = Empresa::create([
            'ruc' => '20987654321',
            'razon_social' => 'MINIMARKET SAC 2',
            'direccion_fiscal' => 'AV. LIMA 456',
            'entorno' => false,
        ]);

        $this->ubigeoLima = Ubigeo::create([
            'ubigeo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);
    }

    public function test_creates_first_sucursal_with_auto_code_and_default_series(): void
    {
        // Create first branch for Empresa 1
        $sucursal1 = Sucursal::create([
            'empresa_id' => $this->empresa1->id,
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. BRASIL 123',
            'nombre_sucursal' => 'Sucursal Brasil',
            'activo' => true,
        ]);

        // Code should be automatically set to "0000"
        $this->assertSame('0000', $sucursal1->codigo);

        // Verify that 5 series records are created for this branch
        $series = Serie::where('sucursal_id', $sucursal1->id)->get();
        $this->assertCount(5, $series);

        $expectedSeries = [
            'BOLETA' => 'B001',
            'FACTURA' => 'F001',
            'NOTA_CREDITO_BOLETA' => 'BC01',
            'NOTA_CREDITO_FACTURA' => 'FC01',
            'TICKET' => 'T001',
        ];

        foreach ($expectedSeries as $tipo => $serieVal) {
            $matchingSerie = $series->firstWhere('tipo_comprobante', $tipo);
            $this->assertNotNull($matchingSerie);
            $this->assertSame($serieVal, $matchingSerie->serie);
            $this->assertSame(1, $matchingSerie->correlativo);
        }
    }

    public function test_creates_second_sucursal_with_auto_code_and_incremented_series(): void
    {
        // Create first branch for Empresa 1
        $sucursal1 = Sucursal::create([
            'empresa_id' => $this->empresa1->id,
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. BRASIL 123',
            'nombre_sucursal' => 'Sucursal Brasil',
            'activo' => true,
        ]);

        // Create second branch for Empresa 1
        $sucursal2 = Sucursal::create([
            'empresa_id' => $this->empresa1->id,
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. AREQUIPA 456',
            'nombre_sucursal' => 'Sucursal Arequipa',
            'activo' => true,
        ]);

        // Second branch code should be "0001"
        $this->assertSame('0001', $sucursal2->codigo);

        // Verify series for second branch have suffix "002"
        $series = Serie::where('sucursal_id', $sucursal2->id)->get();
        $this->assertCount(5, $series);

        $expectedSeries = [
            'BOLETA' => 'B002',
            'FACTURA' => 'F002',
            'NOTA_CREDITO_BOLETA' => 'BC02',
            'NOTA_CREDITO_FACTURA' => 'FC02',
            'TICKET' => 'T002',
        ];

        foreach ($expectedSeries as $tipo => $serieVal) {
            $matchingSerie = $series->firstWhere('tipo_comprobante', $tipo);
            $this->assertNotNull($matchingSerie);
            $this->assertSame($serieVal, $matchingSerie->serie);
            $this->assertSame(1, $matchingSerie->correlativo);
        }
    }

    public function test_respects_manually_provided_code(): void
    {
        // Create branch with manual code
        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresa1->id,
            'codigo' => 'SUC-CUSTOM-99',
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. BRASIL 123',
            'nombre_sucursal' => 'Sucursal Brasil',
            'activo' => true,
        ]);

        $this->assertSame('SUC-CUSTOM-99', $sucursal->codigo);
    }

    public function test_isolates_sequences_by_company(): void
    {
        // Create first branch for Empresa 1
        $suc1Emp1 = Sucursal::create([
            'empresa_id' => $this->empresa1->id,
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. BRASIL 123',
            'nombre_sucursal' => 'Sucursal Brasil 1',
            'activo' => true,
        ]);

        // Create first branch for Empresa 2
        $suc1Emp2 = Sucursal::create([
            'empresa_id' => $this->empresa2->id,
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. BRASIL 456',
            'nombre_sucursal' => 'Sucursal Brasil 2',
            'activo' => true,
        ]);

        // Both should be "0000" as they are first for their respective companies
        $this->assertSame('0000', $suc1Emp1->codigo);
        $this->assertSame('0000', $suc1Emp2->codigo);

        // Both should have "001" series
        $this->assertSame('B001', Serie::where('sucursal_id', $suc1Emp1->id)->where('tipo_comprobante', 'BOLETA')->first()->serie);
        $this->assertSame('B001', Serie::where('sucursal_id', $suc1Emp2->id)->where('tipo_comprobante', 'BOLETA')->first()->serie);
    }

    public function test_auto_assigns_creator_user(): void
    {
        $user = User::create([
            'empresa_id' => $this->empresa1->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $this->actingAs($user);

        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresa1->id,
            'ubigeo' => $this->ubigeoLima->ubigeo,
            'direccion' => 'AV. BRASIL 123',
            'nombre_sucursal' => 'Sucursal Brasil',
            'activo' => true,
        ]);

        // Verify that the user was automatically assigned to the new branch
        $this->assertTrue($user->sucursales->contains($sucursal->id));
    }
}
