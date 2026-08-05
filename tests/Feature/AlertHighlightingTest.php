<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Lote;
use App\Models\LotePresentacion;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Models\Sucursal;
use App\Models\Ubigeo;
use App\Models\UniMedida;
use App\Models\User;
use App\Support\SucursalContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AlertHighlightingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Sucursal $sucursal;
    private UniMedida $unidad;

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
            'name' => 'Operator',
            'email' => 'operator@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->sucursal->users()->attach($this->user);
        session([SucursalContext::SESSION_KEY => $this->sucursal->id]);

        $this->unidad = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);

        // Seed and assign permissions to avoid 403 response
        Permission::firstOrCreate(['name' => 'ventas.crear', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'productos.ver', 'guard_name' => 'web']);
        $this->user->givePermissionTo(['ventas.crear', 'productos.ver']);
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_lotes_list_prioritizes_and_highlights_lote_when_parameter_is_passed(): void
    {
        $this->actingAs($this->user);

        $prod = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PRODUCTO TEST',
            'slug' => 'producto-test',
            'activo' => true,
        ]);

        $pres1 = ProductoPresentacion::create([
            'producto_id' => $prod->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
        ]);

        $pres2 = ProductoPresentacion::create([
            'producto_id' => $prod->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 12,
            'tipo_presentacion' => 'Caja',
        ]);

        // Create two lotes
        $lote1 = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOTE-A',
            'producto_nombre' => 'PRODUCTO A',
            'stock_total' => 10,
            'estado_lote' => 'activo',
        ]);

        $lote2 = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOTE-B',
            'producto_nombre' => 'PRODUCTO B',
            'stock_total' => 20,
            'estado_lote' => 'activo',
        ]);

        LotePresentacion::create([
            'lote_id' => $lote1->id,
            'producto_presentacion_id' => $pres1->id,
            'stock' => 10,
            'estado' => 'activo',
        ]);

        LotePresentacion::create([
            'lote_id' => $lote2->id,
            'producto_presentacion_id' => $pres2->id,
            'stock' => 20,
            'estado' => 'activo',
        ]);

        // Render ListLotes page with highlight_lote parameter
        Livewire::withQueryParams(['highlight_lote' => $lote2->id])
            ->test(\App\Filament\Clusters\Inventario\Resources\Lotes\Pages\ListLotes::class)
            ->assertSuccessful()
            // Should see the incandescent style red class injected for the highlighted record
            ->assertSee('bg-red-500/20');
    }

    public function test_stock_sucursal_list_prioritizes_highlighted_stock_presentation(): void
    {
        $this->actingAs($this->user);

        $prod = Producto::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'PRODUCTO A',
            'slug' => 'producto-a',
            'activo' => true,
        ]);

        $pres1 = ProductoPresentacion::create([
            'producto_id' => $prod->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Unidad',
        ]);

        $pres2 = ProductoPresentacion::create([
            'producto_id' => $prod->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 12,
            'tipo_presentacion' => 'Caja',
        ]);

        $lote1 = Lote::create([
            'sucursal_id' => $this->sucursal->id,
            'codigo_lote' => 'LOTE-A',
            'producto_nombre' => 'PRODUCTO A',
            'stock_total' => 10,
            'estado_lote' => 'activo',
        ]);

        $lp1 = LotePresentacion::create([
            'lote_id' => $lote1->id,
            'producto_presentacion_id' => $pres1->id,
            'stock' => 10,
            'estado' => 'activo',
        ]);

        $lp2 = LotePresentacion::create([
            'lote_id' => $lote1->id,
            'producto_presentacion_id' => $pres2->id,
            'stock' => 5,
            'estado' => 'activo',
        ]);

        $ps1 = ProductoSucursal::create([
            'lote_presentacion_id' => $lp1->id,
            'producto_id' => $prod->id,
            'sucursal_id' => $this->sucursal->id,
            'stock' => 10,
            'stock_minimo' => 2,
            'precio' => 10.0,
            'activo' => true,
        ]);

        $ps2 = ProductoSucursal::create([
            'lote_presentacion_id' => $lp2->id,
            'producto_id' => $prod->id,
            'sucursal_id' => $this->sucursal->id,
            'stock' => 5,
            'stock_minimo' => 1,
            'precio' => 100.0,
            'activo' => true,
        ]);

        // Render ListStockSucursal page with highlight_stock parameter
        Livewire::withQueryParams(['highlight_stock' => $pres2->id])
            ->test(\App\Filament\Clusters\Almacen\Resources\StockSucursal\Pages\ListStockSucursal::class)
            ->assertSuccessful()
            // Should see the incandescent style amber class injected for the highlighted record
            ->assertSee('bg-amber-500/20');
    }
}
