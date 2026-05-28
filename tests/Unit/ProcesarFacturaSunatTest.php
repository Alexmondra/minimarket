<?php

namespace Tests\Unit;

use App\Jobs\ProcesarFacturaSunat;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use App\Support\Facturacion\FacturacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcesarFacturaSunatTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_can_be_dispatched(): void
    {
        Queue::fake();

        $ubigeo = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $empresa = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Test Empresa']);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->id,
            'direccion' => 'Dir',
            'nombre_sucursal' => 'Suc',
            'impuesto_porcentaje' => 18,
        ]);

        $cliente = Cliente::create(['nombre' => 'Test Cliente']);
        $user = User::create(['name' => 'Test User', 'email' => 'test@test.com', 'password' => 'pass']);

        $documento = Documento::create([
            'sucursal_id' => $sucursal->id,
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_bruto' => 100,
            'total_neto' => 118,
            'subtotal' => 100,
            'total_igv' => 18,
            'porcentaje_igv' => 18,
        ]);

        ProcesarFacturaSunat::dispatch($documento);

        Queue::assertPushed(ProcesarFacturaSunat::class, function ($job) use ($documento) {
            return $job->documento->id === $documento->id;
        });
    }

    public function test_job_handle_calls_facturacion_service(): void
    {
        $ubigeo = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $empresa = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Test Empresa']);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->id,
            'direccion' => 'Dir',
            'nombre_sucursal' => 'Suc',
            'impuesto_porcentaje' => 18,
        ]);

        $cliente = Cliente::create(['nombre' => 'Test Cliente']);
        $user = User::create(['name' => 'Test User', 'email' => 'test@test.com', 'password' => 'pass']);

        $documento = Documento::create([
            'sucursal_id' => $sucursal->id,
            'empresa_id' => $empresa->id,
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_bruto' => 100,
            'total_neto' => 118,
            'subtotal' => 100,
            'total_igv' => 18,
            'porcentaje_igv' => 18,
        ]);

        $serviceMock = $this->createMock(FacturacionService::class);
        $serviceMock->expects($this->once())
            ->method('procesar')
            ->with($this->callback(fn ($doc) => $doc->id === $documento->id));

        $job = new ProcesarFacturaSunat($documento);
        $job->handle($serviceMock);
    }
}
