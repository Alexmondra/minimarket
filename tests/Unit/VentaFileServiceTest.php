<?php

namespace Tests\Unit;

use App\Models\Archivo;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use App\Support\Ventas\VentaFileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VentaFileServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardar_pdf_saves_file_and_creates_archivo_record(): void
    {
        Storage::fake('local');

        $ubigeo = Ubigeo::create([
            'codigo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
        ]);

        $empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
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

        $documento = Documento::create([
            'sucursal_id' => $sucursal->id,
            'empresa_id' => $empresa->id,
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

        $pdfContent = 'fake_pdf_content';
        $fileService = new VentaFileService();

        $archivo = $fileService->guardarPdf($documento, $pdfContent);

        $this->assertInstanceOf(Archivo::class, $archivo);
        $this->assertSame('pdf', $archivo->tipo_archivo);
        $this->assertSame('local', $archivo->proveedor_almacenamiento);
        $this->assertSame('private', $archivo->bucket);
        
        $expectedFilename = '20123456789-F001-00000001-pdf.pdf';
        $this->assertSame($expectedFilename, $archivo->nombre_archivo);
        
        $expectedPath = 'ventas/' . $empresa->id . '/' . now()->format('Y/m') . '/factura/' . $expectedFilename;
        $this->assertSame($expectedPath, $archivo->ruta_archivo);

        Storage::disk('local')->assertExists($expectedPath);
        $this->assertSame($pdfContent, Storage::disk('local')->get($expectedPath));
    }
}
