<?php

namespace Tests\Unit;

use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Support\Ventas\RegistrarVenta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SucursalTaxesTest extends TestCase
{
    use RefreshDatabase;

    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
        ]);
    }

    public function test_sucursal_saving_listener_calculates_impuesto_exempt(): void
    {
        $ubigeoLoreto = Ubigeo::create([
            'codigo' => '160101',
            'departamento' => 'LORETO',
            'provincia' => 'MAYNAS',
            'distrito' => 'IQUITOS',
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => 'SUC1',
            'ubigeo' => $ubigeoLoreto->id,
            'direccion' => 'Av. Iquitos 123',
            'nombre_sucursal' => 'Sucursal Iquitos',
            'impuesto_porcentaje' => 18.00, // Should be overwritten to 0.00
            'activo' => true,
        ]);

        $this->assertEquals(0.00, (float) $sucursal->fresh()->impuesto_porcentaje);
    }

    public function test_sucursal_saving_listener_calculates_impuesto_standard(): void
    {
        $ubigeoLima = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => 'SUC2',
            'ubigeo' => $ubigeoLima->id,
            'direccion' => 'Av. Lima 123',
            'nombre_sucursal' => 'Sucursal Lima',
            'impuesto_porcentaje' => 0.00, // Should be overwritten to 18.00
            'activo' => true,
        ]);

        $this->assertEquals(18.00, (float) $sucursal->fresh()->impuesto_porcentaje);
    }

    public function test_es_exento_de_igv_helper_on_registrar_venta(): void
    {
        $registrarVenta = $this->app->make(RegistrarVenta::class);

        $ubigeoLoreto = Ubigeo::create([
            'codigo' => '160101',
            'departamento' => 'LORETO',
            'provincia' => 'MAYNAS',
            'distrito' => 'IQUITOS',
        ]);

        $sucursalLoreto = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => 'SUC1',
            'ubigeo' => $ubigeoLoreto->id,
            'direccion' => 'Av. Iquitos 123',
            'nombre_sucursal' => 'Sucursal Iquitos',
            'activo' => true,
        ]);

        $ubigeoLima = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);

        $sucursalLima = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => 'SUC2',
            'ubigeo' => $ubigeoLima->id,
            'direccion' => 'Av. Lima 123',
            'nombre_sucursal' => 'Sucursal Lima',
            'activo' => true,
        ]);

        $this->assertTrue($registrarVenta->esExentoDeIgv($sucursalLoreto));
        $this->assertFalse($registrarVenta->esExentoDeIgv($sucursalLima));
    }
}
