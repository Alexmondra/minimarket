<?php

namespace Tests\Feature;

use App\Filament\Clusters\Sunat\Resources\EnviosSunat\Pages\ListEnviosSunat;
use App\Models\Documento;
use App\Models\DocumentoReferencium;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\Sunat;
use App\Models\Ubigeo;
use App\Models\User;
use App\Support\Facturacion\FacturacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EnviosSunatTest extends TestCase
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
            'name' => 'Cashier',
            'email' => 'cashier@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user);

        // Asignar permiso requerido
        Permission::firstOrCreate(['name' => 'sunat.monitor', 'guard_name' => 'web']);
        $this->user->givePermissionTo('sunat.monitor');
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_it_can_render_sunat_transmissions_page_with_columns(): void
    {
        $this->actingAs($this->user);

        // Crear un documento y su correspondiente registro de envio sunat
        $documento = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000001',
            'fecha_emision' => now()->toDateString(),
            'total_neto' => 100.00,
            'estado' => true,
        ]);

        $sunat = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $documento->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '0',
            'mensaje_sunat' => 'Aceptado por SUNAT',
            'fecha_envio' => now(),
        ]);

        Livewire::test(ListEnviosSunat::class)
            ->set('activeTab', 'todos')
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$sunat])
            ->assertTableColumnExists('documento.sucursal.nombre')
            ->assertTableColumnExists('documento.tipo_comprobante')
            ->assertTableColumnExists('documento.serie')
            ->assertTableColumnExists('estado_sunat')
            ->assertTableColumnExists('mensaje_sunat')
            ->assertTableColumnExists('fecha_envio');
    }

    public function test_tabs_filter_transmissions_correctly(): void
    {
        $this->actingAs($this->user);

        // 1. Factura aceptada
        $doc1 = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_neto' => 100.00,
            'estado' => true,
        ]);
        $sunat1 = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $doc1->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '0',
            'mensaje_sunat' => 'Aceptado',
            'fecha_envio' => now(),
        ]);

        // 2. Boleta con error
        $doc2 = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000002',
            'fecha_emision' => now(),
            'total_neto' => 50.00,
            'estado' => true,
        ]);
        $sunat2 = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $doc2->id,
            'estado_sunat' => false,
            'codigo_respuesta_sunat' => 'ERROR',
            'mensaje_sunat' => 'Rechazado por firma inválida',
            'fecha_envio' => now(),
        ]);

        // 3. Nota de crédito con error
        $doc3 = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'NOTA_CREDITO',
            'serie' => 'FC01',
            'numero' => '00000003',
            'fecha_emision' => now(),
            'total_neto' => 100.00,
            'estado' => true,
        ]);
        $sunat3 = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $doc3->id,
            'estado_sunat' => false,
            'codigo_respuesta_sunat' => 'ERROR',
            'mensaje_sunat' => 'Rechazado',
            'fecha_envio' => now(),
        ]);

        // 4. Factura aceptada con observaciones (debe ir al monitor general también)
        $doc4 = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000004',
            'fecha_emision' => now(),
            'total_neto' => 150.00,
            'estado' => true,
        ]);
        $sunat4 = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $doc4->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '1032',
            'mensaje_sunat' => 'Aceptado con observaciones de firma',
            'fecha_envio' => now(),
        ]);

        // Test general_monitor (fallidos o con observaciones: doc2, doc3, y doc4)
        Livewire::test(ListEnviosSunat::class)
            ->set('activeTab', 'general_monitor')
            ->assertCanSeeTableRecords([$sunat2, $sunat3, $sunat4])
            ->assertCanNotSeeTableRecords([$sunat1]);

        // Test boletas_facturas (solo doc1, doc2, y doc4)
        Livewire::test(ListEnviosSunat::class)
            ->set('activeTab', 'boletas_facturas')
            ->assertCanSeeTableRecords([$sunat1, $sunat2, $sunat4])
            ->assertCanNotSeeTableRecords([$sunat3]);

        // Test notas_credito (solo doc3)
        Livewire::test(ListEnviosSunat::class)
            ->set('activeTab', 'notas_credito')
            ->assertCanSeeTableRecords([$sunat3])
            ->assertCanNotSeeTableRecords([$sunat1, $sunat2, $sunat4]);

        // Test todos (todos los envíos)
        Livewire::test(ListEnviosSunat::class)
            ->set('activeTab', 'todos')
            ->assertCanSeeTableRecords([$sunat1, $sunat2, $sunat3, $sunat4]);
    }

    public function test_it_can_resend_invoice_and_succeed(): void
    {
        $this->actingAs($this->user);

        $documento = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000005',
            'fecha_emision' => now(),
            'total_neto' => 200.00,
            'estado' => true,
        ]);

        $sunat = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $documento->id,
            'estado_sunat' => false,
            'codigo_respuesta_sunat' => 'ERROR',
            'mensaje_sunat' => 'Firma incorrecta',
            'fecha_envio' => now(),
        ]);

        // Crear una instancia de Sunat con éxito para retornar del mock
        $sunatExito = new Sunat([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $documento->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '0',
            'mensaje_sunat' => 'Aceptado por SUNAT exitosamente',
        ]);
        $sunatExito->id = $sunat->id;

        // Mockear el FacturacionService
        $this->mock(FacturacionService::class, function (MockInterface $mock) use ($documento, $sunatExito) {
            $mock->shouldReceive('procesar')
                ->once()
                ->with(Mockery::on(fn ($doc) => $doc->id === $documento->id))
                ->andReturn($sunatExito);
        });

        Livewire::test(ListEnviosSunat::class)
            ->callTableAction('reenviar', $sunat, data: ['confirmar_rectificacion' => true]);
    }

    public function test_it_can_resend_credit_note_and_succeed(): void
    {
        $this->actingAs($this->user);

        // Documento original afectado
        $documentoAfectado = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'FACTURA',
            'serie' => 'F001',
            'numero' => '00000010',
            'fecha_emision' => now(),
            'total_neto' => 500.00,
            'estado' => false, // anulado
        ]);

        // Nota de crédito
        $nota = Documento::create([
            'user_id' => $this->user->id,
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'tipo_comprobante' => 'NOTA_CREDITO',
            'serie' => 'FC01',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_neto' => 500.00,
            'estado' => true,
        ]);

        // Relación de referencia
        DocumentoReferencium::create([
            'documento_id' => $nota->id,
            'tipo_relacion' => 'NOTA_CREDITO',
            'documento_referenciado_id' => $documentoAfectado->id,
            'tipo_documento_ref' => 'FACTURA',
            'serie_ref' => 'F001',
            'numero_ref' => '00000010',
            'motivo_codigo' => '01',
            'motivo_descripcion' => 'Anulación de la operación',
            'fecha_emision_ref' => $documentoAfectado->fecha_emision,
            'moneda_ref' => 'PEN',
        ]);

        $sunat = Sunat::create([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $nota->id,
            'estado_sunat' => false,
            'codigo_respuesta_sunat' => 'ERROR',
            'mensaje_sunat' => 'Rechazado por correlativo duplicado',
            'fecha_envio' => now(),
        ]);

        $sunatExito = new Sunat([
            'empresa_id' => $this->empresa->id,
            'documento_id' => $nota->id,
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => '0',
            'mensaje_sunat' => 'Nota Aceptada por SUNAT',
        ]);
        $sunatExito->id = $sunat->id;

        // Mockear el FacturacionService
        $this->mock(FacturacionService::class, function (MockInterface $mock) use ($nota, $documentoAfectado, $sunatExito) {
            $mock->shouldReceive('procesarNota')
                ->once()
                ->with(
                    Mockery::on(fn ($n) => $n->id === $nota->id),
                    Mockery::on(fn ($da) => $da->id === $documentoAfectado->id)
                )
                ->andReturn($sunatExito);
        });

        Livewire::test(ListEnviosSunat::class)
            ->callTableAction('reenviar', $sunat, data: ['confirmar_rectificacion' => true]);
    }
}
