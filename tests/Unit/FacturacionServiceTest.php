<?php

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\EmpresaConfig;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use App\Support\Facturacion\DocumentoGreenterFactory;
use App\Support\Facturacion\FacturacionFileService;
use App\Support\Facturacion\FacturacionService;
use App\Support\Facturacion\GreenterSeeFactory;
use Greenter\Model\Response\BillResult;
use Greenter\Model\Response\CdrResponse;
use Greenter\Model\Sale\Invoice;
use Greenter\See;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturacionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Documento $documento;

    protected Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        $ubigeo = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $this->empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
        ]);

        EmpresaConfig::create([
            'empresa_id' => $this->empresa->id,
            'user_sol' => 'MODDATOS',
            'pass_sol' => 'MODDATOS',
            'certificado' => 'certificado.pem',
            'certificado_pass' => '123456',
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->id,
            'direccion' => 'AV. TIENDA 456',
            'nombre_sucursal' => 'Central',
            'impuesto_porcentaje' => 18,
            'activo' => true,
        ]);

        $cliente = Cliente::create([
            'nombre' => 'Juan Perez',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->documento = Documento::create([
            'sucursal_id' => $sucursal->id,
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_bruto' => 100.00,
            'total_neto' => 118.00,
            'subtotal' => 100.00,
            'total_igv' => 18.00,
            'porcentaje_igv' => 18.00,
        ]);
    }

    public function test_procesar_success_con_cdr(): void
    {
        // 1. Mock Greenter See and BillResult/CdrResponse
        $seeMock = $this->createMock(See::class);
        $seeMock->method('getXmlSigned')->willReturn('xml_signed_content');

        $cdrResponseMock = $this->createMock(CdrResponse::class);
        $cdrResponseMock->method('getCode')->willReturn('0');
        $cdrResponseMock->method('getDescription')->willReturn('El comprobante numero F001-00000001 ha sido aceptado');
        $cdrResponseMock->method('getNotes')->willReturn([]);

        $billResultMock = $this->createMock(BillResult::class);
        $billResultMock->method('isSuccess')->willReturn(true);
        $billResultMock->method('getCdrZip')->willReturn('zip_cdr_content');
        $billResultMock->method('getCdrResponse')->willReturn($cdrResponseMock);

        $seeMock->method('send')->willReturn($billResultMock);

        // 2. Mock factories and file service
        $seeFactoryMock = $this->createMock(GreenterSeeFactory::class);
        $seeFactoryMock->method('make')->willReturn($seeMock);

        $documentFactoryMock = $this->createMock(DocumentoGreenterFactory::class);
        $greenterDocMock = $this->createMock(Invoice::class);
        $documentFactoryMock->method('make')->willReturn($greenterDocMock);

        $fileServiceMock = $this->createMock(FacturacionFileService::class);
        $fileServiceMock->expects($this->once())
            ->method('guardarXmlFirmado')
            ->with($this->callback(fn ($doc) => $doc->id === $this->documento->id), 'xml_signed_content');
        $fileServiceMock->expects($this->once())
            ->method('guardarCdrZip')
            ->with($this->callback(fn ($doc) => $doc->id === $this->documento->id), 'zip_cdr_content');

        // 3. Action
        $service = new FacturacionService($seeFactoryMock, $documentFactoryMock, $fileServiceMock);
        $sunat = $service->procesar($this->documento);

        // 4. Assertions
        $this->assertTrue($sunat->estado_sunat);
        $this->assertSame('0', $sunat->codigo_respuesta_sunat);
        $this->assertSame('El comprobante numero F001-00000001 ha sido aceptado', $sunat->mensaje_sunat);

        $this->assertDatabaseHas('sunat', [
            'documento_id' => $this->documento->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '0',
            'mensaje_sunat' => 'El comprobante numero F001-00000001 ha sido aceptado',
        ]);
    }

    public function test_procesar_warning_con_notas(): void
    {
        // 1. Mock Greenter See and BillResult/CdrResponse
        $seeMock = $this->createMock(See::class);
        $seeMock->method('getXmlSigned')->willReturn('xml_signed_content');

        $cdrResponseMock = $this->createMock(CdrResponse::class);
        $cdrResponseMock->method('getCode')->willReturn('0');
        $cdrResponseMock->method('getDescription')->willReturn('Aceptado con observaciones');
        $cdrResponseMock->method('getNotes')->willReturn([
            'Adicional nota 1',
            'Adicional nota 2',
        ]);

        $billResultMock = $this->createMock(BillResult::class);
        $billResultMock->method('isSuccess')->willReturn(true);
        $billResultMock->method('getCdrZip')->willReturn('zip_cdr_content');
        $billResultMock->method('getCdrResponse')->willReturn($cdrResponseMock);

        $seeMock->method('send')->willReturn($billResultMock);

        // 2. Mock factories and file service
        $seeFactoryMock = $this->createMock(GreenterSeeFactory::class);
        $seeFactoryMock->method('make')->willReturn($seeMock);

        $documentFactoryMock = $this->createMock(DocumentoGreenterFactory::class);
        $greenterDocMock = $this->createMock(Invoice::class);
        $documentFactoryMock->method('make')->willReturn($greenterDocMock);

        $fileServiceMock = $this->createMock(FacturacionFileService::class);
        $fileServiceMock->expects($this->once())
            ->method('guardarXmlFirmado')
            ->with($this->callback(fn ($doc) => $doc->id === $this->documento->id), 'xml_signed_content');
        $fileServiceMock->expects($this->once())
            ->method('guardarCdrZip')
            ->with($this->callback(fn ($doc) => $doc->id === $this->documento->id), 'zip_cdr_content');

        // 3. Action
        $service = new FacturacionService($seeFactoryMock, $documentFactoryMock, $fileServiceMock);
        $sunat = $service->procesar($this->documento);

        // 4. Assertions
        $this->assertTrue($sunat->estado_sunat);
        $this->assertSame('0', $sunat->codigo_respuesta_sunat);
        $this->assertSame('Aceptado con observaciones Notas: Adicional nota 1 | Adicional nota 2', $sunat->mensaje_sunat);

        $this->assertDatabaseHas('sunat', [
            'documento_id' => $this->documento->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '0',
            'mensaje_sunat' => 'Aceptado con observaciones Notas: Adicional nota 1 | Adicional nota 2',
        ]);
    }

    public function test_procesar_error_soap_failure(): void
    {
        // 1. Mock Greenter See and exception
        $seeMock = $this->createMock(See::class);
        $seeMock->method('getXmlSigned')->willReturn('xml_signed_content');
        $seeMock->method('send')->willThrowException(new \Exception('SOAP connection failure'));

        // 2. Mock factories and file service
        $seeFactoryMock = $this->createMock(GreenterSeeFactory::class);
        $seeFactoryMock->method('make')->willReturn($seeMock);

        $documentFactoryMock = $this->createMock(DocumentoGreenterFactory::class);
        $greenterDocMock = $this->createMock(Invoice::class);
        $documentFactoryMock->method('make')->willReturn($greenterDocMock);

        $fileServiceMock = $this->createMock(FacturacionFileService::class);
        $fileServiceMock->expects($this->once())
            ->method('guardarXmlFirmado')
            ->with($this->callback(fn ($doc) => $doc->id === $this->documento->id), 'xml_signed_content');
        $fileServiceMock->expects($this->never())
            ->method('guardarCdrZip');

        // 3. Action
        $service = new FacturacionService($seeFactoryMock, $documentFactoryMock, $fileServiceMock);
        $sunat = $service->procesar($this->documento);

        // 4. Assertions
        $this->assertFalse($sunat->estado_sunat);
        $this->assertSame('ERROR', $sunat->codigo_respuesta_sunat);
        $this->assertSame('SOAP connection failure', $sunat->mensaje_sunat);

        $this->assertDatabaseHas('sunat', [
            'documento_id' => $this->documento->id,
            'estado_sunat' => false,
            'codigo_respuesta_sunat' => 'ERROR',
            'mensaje_sunat' => 'SOAP connection failure',
        ]);
    }

    public function test_procesar_cdr_rechazado_guarda_codigo_y_estado_falso(): void
    {
        $seeMock = $this->createMock(See::class);
        $seeMock->method('getXmlSigned')->willReturn('xml_signed_content');

        $cdrResponseMock = $this->createMock(CdrResponse::class);
        $cdrResponseMock->method('getCode')->willReturn('2000');
        $cdrResponseMock->method('getDescription')->willReturn('Comprobante rechazado por SUNAT');
        $cdrResponseMock->method('getNotes')->willReturn([]);

        $billResultMock = $this->createMock(BillResult::class);
        $billResultMock->method('isSuccess')->willReturn(true);
        $billResultMock->method('getCdrZip')->willReturn('zip_cdr_content');
        $billResultMock->method('getCdrResponse')->willReturn($cdrResponseMock);

        $seeMock->method('send')->willReturn($billResultMock);

        $seeFactoryMock = $this->createMock(GreenterSeeFactory::class);
        $seeFactoryMock->method('make')->willReturn($seeMock);

        $documentFactoryMock = $this->createMock(DocumentoGreenterFactory::class);
        $documentFactoryMock->method('make')->willReturn($this->createMock(Invoice::class));

        $fileServiceMock = $this->createMock(FacturacionFileService::class);
        $fileServiceMock->expects($this->once())->method('guardarXmlFirmado');
        $fileServiceMock->expects($this->once())->method('guardarCdrZip');

        $service = new FacturacionService($seeFactoryMock, $documentFactoryMock, $fileServiceMock);
        $sunat = $service->procesar($this->documento);

        $this->assertFalse($sunat->estado_sunat);
        $this->assertSame('2000', $sunat->codigo_respuesta_sunat);
        $this->assertSame('Comprobante rechazado por SUNAT', $sunat->mensaje_sunat);
    }

    public function test_procesar_failure_con_cdr_response(): void
    {
        // 1. Mock Greenter See and BillResult/CdrResponse
        $seeMock = $this->createMock(See::class);
        $seeMock->method('getXmlSigned')->willReturn('xml_signed_content');

        $cdrResponseMock = $this->createMock(CdrResponse::class);
        $cdrResponseMock->method('getCode')->willReturn('2015');
        $cdrResponseMock->method('getDescription')->willReturn('Comprobante invalido o rechazado en CDR');
        $cdrResponseMock->method('getNotes')->willReturn([]);

        $billResultMock = $this->createMock(BillResult::class);
        $billResultMock->method('isSuccess')->willReturn(false);
        $billResultMock->method('getCdrZip')->willReturn('zip_cdr_content');
        $billResultMock->method('getCdrResponse')->willReturn($cdrResponseMock);

        $seeMock->method('send')->willReturn($billResultMock);

        // 2. Mock factories and file service
        $seeFactoryMock = $this->createMock(GreenterSeeFactory::class);
        $seeFactoryMock->method('make')->willReturn($seeMock);

        $documentFactoryMock = $this->createMock(DocumentoGreenterFactory::class);
        $greenterDocMock = $this->createMock(Invoice::class);
        $documentFactoryMock->method('make')->willReturn($greenterDocMock);

        $fileServiceMock = $this->createMock(FacturacionFileService::class);
        $fileServiceMock->expects($this->once())->method('guardarXmlFirmado');
        $fileServiceMock->expects($this->once())->method('guardarCdrZip');

        // 3. Action
        $service = new FacturacionService($seeFactoryMock, $documentFactoryMock, $fileServiceMock);
        $sunat = $service->procesar($this->documento);

        // 4. Assertions
        $this->assertFalse($sunat->estado_sunat);
        $this->assertSame('2015', $sunat->codigo_respuesta_sunat);
        $this->assertSame('Comprobante invalido o rechazado en CDR', $sunat->mensaje_sunat);
    }
}
