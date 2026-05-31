<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Empresa;
use App\Models\SessioneCaja;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchivoPrivadoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_pdf_on_the_fly(): void
    {
        Storage::fake('local');

        $ubigeo = Ubigeo::create([
            'ubigeo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);

        $empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->ubigeo,
            'nombre_sucursal' => 'CENTRO',
            'direccion' => 'AV. CENTRAL 123',
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'nombre' => 'Juan',
            'apellido' => 'Perez',
        ]);

        $user = User::create([
            'empresa_id' => $empresa->id,
            'name' => 'Vendedor',
            'email' => 'vendedor@example.com',
            'password' => bcrypt('password'),
        ]);

        $sucursal->users()->attach($user);

        $sesion = SessioneCaja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $user->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.0,
            'estado' => true,
        ]);

        $documento = Documento::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'caja_sesion_id' => $sesion->id,
            'tipo_comprobante' => 'BOLETA',
            'serie' => 'B001',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_bruto' => 100.0,
            'total_descuento' => 0.0,
            'subtotal' => 84.75,
            'total_igv' => 15.25,
            'total_neto' => 100.0,
            'monto_recibido' => 100.0,
            'vuelto' => 0.0,
        ]);

        $this->actingAs($user);

        // Call PDF route
        $response = $this->get(route('filament.documentos.pdf', $documento));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // Check if Archivo record was created
        $this->assertDatabaseHas('archivos', [
            'documento_id' => $documento->id,
            'tipo_archivo' => 'pdf',
        ]);
    }

    public function test_it_generates_ticket_html_on_the_fly(): void
    {
        Storage::fake('local');

        $ubigeo = Ubigeo::create([
            'ubigeo' => '150101',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'LIMA',
        ]);

        $empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
        ]);

        $sucursal = Sucursal::create([
            'empresa_id' => $empresa->id,
            'codigo' => '0001',
            'ubigeo' => $ubigeo->ubigeo,
            'nombre_sucursal' => 'CENTRO',
            'direccion' => 'AV. CENTRAL 123',
        ]);

        $cliente = Cliente::create([
            'empresa_id' => $empresa->id,
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'nombre' => 'Juan',
            'apellido' => 'Perez',
        ]);

        $user = User::create([
            'empresa_id' => $empresa->id,
            'name' => 'Vendedor',
            'email' => 'vendedor@example.com',
            'password' => bcrypt('password'),
        ]);

        $sucursal->users()->attach($user);

        $sesion = SessioneCaja::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'user_id' => $user->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.0,
            'estado' => true,
        ]);

        $documento = Documento::create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'caja_sesion_id' => $sesion->id,
            'tipo_comprobante' => 'TICKET',
            'serie' => 'T001',
            'numero' => '00000001',
            'fecha_emision' => now(),
            'total_bruto' => 100.0,
            'total_descuento' => 0.0,
            'subtotal' => 100.0,
            'total_igv' => 0.0,
            'total_neto' => 100.0,
            'monto_recibido' => 100.0,
            'vuelto' => 0.0,
        ]);

        $this->actingAs($user);

        // Call Ticket route
        $response = $this->get(route('filament.documentos.ticket', $documento));

        $response->assertStatus(200);

        // Check if Archivo record was created
        $this->assertDatabaseHas('archivos', [
            'documento_id' => $documento->id,
            'tipo_archivo' => 'ticket_html',
        ]);
    }
}
