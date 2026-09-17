<?php

namespace Tests\Feature;

use App\Livewire\Ventas\RegistrarVenta;
use App\Models\Empresa;
use App\Models\SessioneCaja;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrarVentaPreferenciaComprobanteTest extends TestCase
{
    use RefreshDatabase;

    private User $user1;
    private User $user2;
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

        $this->user1 = User::create([
            'empresa_id' => $this->empresa->id,
            'name' => 'Cajero Uno',
            'email' => 'cajero1@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->user2 = User::create([
            'empresa_id' => $this->empresa->id,
            'name' => 'Cajero Dos',
            'email' => 'cajero2@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach([$this->user1->id, $this->user2->id]);

        SessioneCaja::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user1->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.00,
            'estado' => true,
        ]);

        SessioneCaja::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $this->sucursal->id,
            'user_id' => $this->user2->id,
            'fecha_apertura' => now(),
            'saldo_inicial' => 100.00,
            'estado' => true,
        ]);
    }

    public function test_por_defecto_primera_vez_es_boleta(): void
    {
        $this->actingAs($this->user1);

        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'BOLETA');
    }

    public function test_cambiar_tipo_comprobante_persiste_preferencia_por_usuario(): void
    {
        $this->actingAs($this->user1);

        // El usuario 1 cambia a FACTURA
        Livewire::test(RegistrarVenta::class)
            ->call('cambiarTipoComprobante', 'FACTURA')
            ->assertSet('tipoComprobante', 'FACTURA')
            ->assertSet('clienteTipoDocumento', 'RUC');

        $this->assertSame('FACTURA', Cache::get("pos_tipo_comprobante_user_{$this->user1->id}"));

        // Al volver a montar o recargar el componente con el mismo usuario, mantiene FACTURA
        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'FACTURA');
    }

    public function test_cambiar_a_ticket_persiste_para_el_usuario(): void
    {
        $this->actingAs($this->user1);

        Livewire::test(RegistrarVenta::class)
            ->call('cambiarTipoComprobante', 'TICKET')
            ->assertSet('tipoComprobante', 'TICKET');

        $this->assertSame('TICKET', Cache::get("pos_tipo_comprobante_user_{$this->user1->id}"));

        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'TICKET');
    }

    public function test_usuarios_diferentes_mantienen_sus_propias_preferencias(): void
    {
        // Usuario 1 guarda TICKET
        $this->actingAs($this->user1);
        Livewire::test(RegistrarVenta::class)
            ->call('cambiarTipoComprobante', 'TICKET');

        // Usuario 2 no ha configurado nada (debe ser BOLETA)
        $this->actingAs($this->user2);
        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'BOLETA')
            ->call('cambiarTipoComprobante', 'FACTURA');

        // Al volver el Usuario 1, sigue siendo TICKET
        $this->actingAs($this->user1);
        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'TICKET');

        // Al volver el Usuario 2, sigue siendo FACTURA
        $this->actingAs($this->user2);
        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'FACTURA');
    }

    public function test_limpiar_carrito_o_sesion_no_pierde_preferencia_de_comprobante(): void
    {
        $this->actingAs($this->user1);

        $component = Livewire::test(RegistrarVenta::class)
            ->call('cambiarTipoComprobante', 'FACTURA')
            ->call('cerrarSuccessModal'); // Cierra modal de éxito / resetea carrito

        // Simulamos ciclo rendering
        $component->call('rendering');

        // Al recargar/montar de nuevo, sigue en FACTURA
        Livewire::test(RegistrarVenta::class)
            ->assertSet('tipoComprobante', 'FACTURA');
    }
}
