<?php

namespace Tests\Feature;

use App\Livewire\Compras\RegistrarCompra;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\User;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UniMedida;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\DetalleCompra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrarCompraTest extends TestCase
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
            'name' => 'Comprador',
            'email' => 'compras@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user);
    }

    public function test_it_searches_suppliers_regardless_of_sucursal(): void
    {
        $sucursal2 = Sucursal::create([
            'empresa_id' => $this->empresa->id,
            'codigo' => '0002',
            'ubigeo' => $this->sucursal->ubigeo,
            'nombre_sucursal' => 'NORTE',
            'direccion' => 'AV. NORTE 123',
            'impuesto_porcentaje' => 18,
        ]);

        // Proveedor from another sucursal (or null sucursal) but same empresa
        $prov1 = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => null,
            'nombre' => 'PROVEEDOR NACIONAL',
            'numero_documento' => '20111111111',
            'estado' => true,
        ]);

        $prov2 = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'sucursal_id' => $sucursal2->id, // valid different sucursal id
            'nombre' => 'DISTRIBUIDORA SAC',
            'numero_documento' => '20222222222',
            'estado' => true,
        ]);

        $this->actingAs($this->user);

        Livewire::test(RegistrarCompra::class)
            ->set('searchProveedor', 'PROV')
            ->assertSet('showProveedorDropdown', true)
            ->assertCount('proveedoresResultados', 1)
            ->set('searchProveedor', 'DIST')
            ->assertSet('showProveedorDropdown', true)
            ->assertCount('proveedoresResultados', 1);
    }

    public function test_it_opens_supplier_registration_modal(): void
    {
        $this->actingAs($this->user);

        Livewire::test(RegistrarCompra::class)
            ->set('searchProveedor', '20123456789')
            ->call('abrirRegistrarProveedorModal')
            ->assertSet('showRegistrarProveedorModal', true)
            ->assertSet('nuevoProveedorDocumento', '20123456789')
            ->assertSet('nuevoProveedorTipoDocumento', 'RUC');

        Livewire::test(RegistrarCompra::class)
            ->set('searchProveedor', '12345678')
            ->call('abrirRegistrarProveedorModal')
            ->assertSet('showRegistrarProveedorModal', true)
            ->assertSet('nuevoProveedorDocumento', '12345678')
            ->assertSet('nuevoProveedorTipoDocumento', 'DNI');
    }

    public function test_it_queries_external_api_for_new_supplier(): void
    {
        config(['services.datos.key' => 'test-api-key']);
        config(['services.datos.ruc_url' => 'https://api.test/ruc/']);

        Http::fake([
            'https://api.test/ruc/20123456789' => Http::response([
                'success' => true,
                'data' => [
                    'razon_social' => 'SUNAT CORP',
                    'direccion' => 'CALLE LAS COMPRAS 456',
                    'email' => 'contacto@sunatcorp.com',
                ],
            ], 200),
        ]);

        $this->actingAs($this->user);

        Livewire::test(RegistrarCompra::class)
            ->set('nuevoProveedorTipoDocumento', 'RUC')
            ->set('nuevoProveedorDocumento', '20123456789')
            ->call('buscarNuevoProveedor')
            ->assertSet('nuevoProveedorNombre', 'SUNAT CORP')
            ->assertSet('nuevoProveedorRazonSocial', 'SUNAT CORP')
            ->assertSet('nuevoProveedorDireccion', 'CALLE LAS COMPRAS 456')
            ->assertSet('nuevoProveedorEmail', 'contacto@sunatcorp.com');
    }

    public function test_it_registers_supplier_manually_and_selects_it(): void
    {
        $this->actingAs($this->user);

        Livewire::test(RegistrarCompra::class)
            ->set('nuevoProveedorTipoDocumento', 'RUC')
            ->set('nuevoProveedorDocumento', '20555555555')
            ->set('nuevoProveedorNombre', 'NUEVO PROVEEDOR SAC')
            ->set('nuevoProveedorRazonSocial', 'NUEVO PROVEEDOR SAC')
            ->set('nuevoProveedorDireccion', 'AV. INDUSTRIAL 789')
            ->call('registrarProveedorManual')
            ->assertHasNoErrors()
            ->assertSet('showRegistrarProveedorModal', false)
            ->assertSet('searchProveedor', 'NUEVO PROVEEDOR SAC')
            ->assertNotSet('proveedorId', null);

        $this->assertDatabaseHas('proveedores', [
            'empresa_id' => $this->empresa->id,
            'numero_documento' => '20555555555',
            'nombre' => 'NUEVO PROVEEDOR SAC',
        ]);
    }

    public function test_it_authorizes_page_access_for_crear_permission_when_creating_compra(): void
    {
        $this->actingAs($this->user);

        // Clear Spatie permission cache
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Without permission, it should fail
        Livewire::test(\App\Filament\Clusters\Compras\Resources\Compras\Pages\RegistrarCompra::class)
            ->assertStatus(403);

        // With permission, it should succeed
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'compras.ver', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'compras.crear', 'guard_name' => 'web']);
        $this->user->givePermissionTo(['compras.ver', 'compras.crear']);
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::test(\App\Filament\Clusters\Compras\Resources\Compras\Pages\RegistrarCompra::class)
            ->assertStatus(200);
    }

    public function test_it_authorizes_page_access_for_editar_permission_when_editing_compra(): void
    {
        $this->actingAs($this->user);

        // Clear Spatie permission cache
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create a dummy purchase
        $proveedor = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PROVEEDOR TEST',
            'tipo_documento' => 'RUC',
            'numero_documento' => '20999999999',
            'estado' => true,
        ]);
        $compra = \App\Models\Compra::create([
            'sucursal_id' => $this->sucursal->id,
            'proveedor_id' => $proveedor->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'factura',
            'fecha_recepcion' => now(),
            'costo_total_factura' => 0.00,
            'estado' => false,
        ]);

        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'compras.ver', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'compras.crear', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'compras.editar', 'guard_name' => 'web']);

        // Only with compras.ver and compras.crear, editing should fail
        $this->user->givePermissionTo(['compras.ver', 'compras.crear']);
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::withQueryParams(['compra_id' => $compra->id])
            ->test(\App\Filament\Clusters\Compras\Resources\Compras\Pages\RegistrarCompra::class)
            ->assertStatus(403);

        // With compras.editar, editing should succeed
        $this->user->givePermissionTo('compras.editar');
        
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::withQueryParams(['compra_id' => $compra->id])
            ->test(\App\Filament\Clusters\Compras\Resources\Compras\Pages\RegistrarCompra::class)
            ->assertStatus(200);
    }

    public function test_it_counts_unique_products_and_excludes_editing_lote_recalculating_correctly(): void
    {
        $this->actingAs($this->user);

        // Create supplier
        $proveedor = Proveedor::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PROVEEDOR RESUMEN',
            'tipo_documento' => 'RUC',
            'numero_documento' => '20777777777',
            'estado' => true,
        ]);

        // Create purchase
        $compra = \App\Models\Compra::create([
            'sucursal_id' => $this->sucursal->id,
            'proveedor_id' => $proveedor->id,
            'user_id' => $this->user->id,
            'tipo_comprobante' => 'factura',
            'fecha_recepcion' => now(),
            'costo_total_factura' => 0.00,
            'estado' => false,
        ]);

        // Create 2 products
        $prod1 = Producto::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Producto 1', 'slug' => 'producto-1', 'activo' => true]);
        $prod2 = Producto::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Producto 2', 'slug' => 'producto-2', 'activo' => true]);

        $unidad = UniMedida::create(['nombre' => 'Unidad', 'abreviatura' => 'und', 'activo' => true]);

        $pres1 = ProductoPresentacion::create(['producto_id' => $prod1->id, 'unidad_medida_id' => $unidad->id, 'cantidad' => 1, 'tipo_presentacion' => 'Caja']);
        $pres2 = ProductoPresentacion::create(['producto_id' => $prod2->id, 'unidad_medida_id' => $unidad->id, 'cantidad' => 1, 'tipo_presentacion' => 'Bolsa']);

        // Create lote 1 for prod 1
        $lote1 = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-1',
            'producto_nombre' => $prod1->nombre,
            'precio_compra' => 50.00,
            'estado_lote' => 'activo',
        ]);
        LotePresentacion::create([
            'lote_id' => $lote1->id,
            'producto_presentacion_id' => $pres1->id,
            'stock_inicial' => 10,
            'stock' => 10,
            'precio_compra' => 5.00,
        ]);
        DetalleCompra::create([
            'compra_id' => $compra->id,
            'lote_id' => $lote1->id,
            'precio_compra' => 50.00,
        ]);

        // Create lote 2 for prod 2
        $lote2 = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOT-2',
            'producto_nombre' => $prod2->nombre,
            'precio_compra' => 100.00,
            'estado_lote' => 'activo',
        ]);
        LotePresentacion::create([
            'lote_id' => $lote2->id,
            'producto_presentacion_id' => $pres2->id,
            'stock_inicial' => 20,
            'stock' => 20,
            'precio_compra' => 5.00,
        ]);
        DetalleCompra::create([
            'compra_id' => $compra->id,
            'lote_id' => $lote2->id,
            'precio_compra' => 100.00,
        ]);

        // Test RegistrarCompra summary behavior
        Livewire::test(RegistrarCompra::class)
            ->set('compraId', $compra->id)
            ->set('sucursalId', $this->sucursal->id)
            ->call('actualizarResumen')
            ->assertSet('totalUnidades', 30.00)
            ->assertSet('subtotalCompra', 150.00)
            ->assertSet('totalFinal', 150.00)
            ->assertSet('cantidadProductos', 2)
            
            // Simulating edit of lote 1
            ->dispatch('loteEditando', $lote1->id)
            ->assertSet('editingLoteId', $lote1->id)
            // Lote 1 should be excluded from totals now
            ->assertSet('totalUnidades', 20.00)
            ->assertSet('subtotalCompra', 100.00)
            ->assertSet('totalFinal', 100.00)
            ->assertSet('cantidadProductos', 1)
            
            // Simulating cancel or save (which dispatches loteEditando with null)
            ->dispatch('loteEditando', null)
            ->assertSet('editingLoteId', null)
            // Totales should recover
            ->assertSet('totalUnidades', 30.00)
            ->assertSet('subtotalCompra', 150.00)
            ->assertSet('totalFinal', 150.00)
            ->assertSet('cantidadProductos', 2);
    }
}

