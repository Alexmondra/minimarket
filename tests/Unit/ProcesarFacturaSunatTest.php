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
            'ubigeo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $empresa = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Test Empresa']);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->ubigeo,
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

    public function test_job_handle_calls_enviar_sunat(): void
    {
        $ubigeo = Ubigeo::create([
            'ubigeo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $empresa = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Test Empresa']);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->ubigeo,
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
            ->method('enviarSunat')
            ->with(
                $this->callback(fn ($doc) => $doc->id === $documento->id),
                $this->equalTo(true),
                $this->anything()
            );

        $job = new ProcesarFacturaSunat($documento);
        $job->handle($serviceMock);
    }

    public function test_job_failed_updates_sunat_record_to_error_conexion(): void
    {
        $ubigeo = Ubigeo::create([
            'ubigeo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $empresa = Empresa::create(['ruc' => '20123456789', 'razon_social' => 'Test Empresa']);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->ubigeo,
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
            'numero' => '00000002',
            'fecha_emision' => now(),
            'total_bruto' => 100,
            'total_neto' => 118,
            'subtotal' => 100,
            'total_igv' => 18,
            'porcentaje_igv' => 18,
        ]);

        $job = new ProcesarFacturaSunat($documento);
        $job->failed(new \RuntimeException('cURL error 28: Operation timed out'));

        $sunat = \App\Models\Sunat::where('documento_id', $documento->id)->first();
        $this->assertNotNull($sunat);
        $this->assertFalse((bool) $sunat->estado_sunat);
        $this->assertSame('ERROR_CONEXION', $sunat->codigo_respuesta_sunat);
        $this->assertStringContainsString('Se agotaron los 3 intentos', $sunat->mensaje_sunat);
    }

    public function test_es_rechazo_formal_identifies_sunat_codes_correctly(): void
    {
        $service = app(FacturacionService::class);

        // Aceptados
        $this->assertFalse($service->esRechazoFormal('0'));
        $this->assertFalse($service->esRechazoFormal('1033'));

        // Rechazos formales definitivos de SUNAT (2000 a 3999)
        $this->assertTrue($service->esRechazoFormal('2000'));
        $this->assertTrue($service->esRechazoFormal('2324'));
        $this->assertTrue($service->esRechazoFormal('3999'));

        // Errores de servidor / transitorios / cadenas
        $this->assertFalse($service->esRechazoFormal('4000'));
        $this->assertFalse($service->esRechazoFormal('ERROR'));
        $this->assertFalse($service->esRechazoFormal(null));
    }
}
