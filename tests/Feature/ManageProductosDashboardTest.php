<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Filament\Clusters\Almacen\Resources\Productos\Pages\ListProductos;

class ManageProductosDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Categoria $categoria;
    private Marca $marca;

    protected function setUp(): void
    {
        parent::setUp();

        $this->empresa = Empresa::create([
            'ruc' => '20123456789',
            'razon_social' => 'MINIMARKET SAC',
            'direccion_fiscal' => 'AV. PERU 123',
            'entorno' => false,
            'incluido_tributo' => true,
        ]);

        $this->user = User::create([
            'empresa_id' => $this->empresa->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->categoria = Categoria::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Bebidas',
            'estado' => true,
        ]);

        $this->marca = Marca::create([
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Coca-Cola',
        ]);
    }

    public function test_it_renders_productos_dashboard_and_shows_kpis(): void
    {
        $this->actingAs($this->user);

        // Create some products
        Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Coca-Cola 500ml',
            'slug' => 'coca-cola-500ml',
            'codigo_interno' => 'COCA500',
        ]);

        Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Inca Kola 1L',
            'slug' => 'inca-kola-1l',
            'codigo_interno' => 'INCA1000',
        ]);

        Livewire::test(ListProductos::class)
            ->assertStatus(200)
            ->assertSee('Coca-Cola 500ml')
            ->assertSee('Inca Kola 1L')
            ->assertSee('Total Productos');
    }

    public function test_it_filters_by_category_and_brand(): void
    {
        $this->actingAs($this->user);

        $cat2 = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Snacks', 'estado' => true]);
        $marca2 = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Lay\'s']);

        Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Coca-Cola 500ml',
            'slug' => 'coca-cola-500ml',
        ]);

        Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $cat2->id,
            'marca_id' => $marca2->id,
            'nombre' => 'Papas Lay\'s 100g',
            'slug' => 'papas-lays-100g',
        ]);

        // Filter by category
        Livewire::test(ListProductos::class)
            ->set('categoria_id', $this->categoria->id)
            ->assertSee('Coca-Cola 500ml')
            ->assertDontSee('Papas Lay\'s 100g');

        // Filter by brand
        Livewire::test(ListProductos::class)
            ->set('marca_id', $marca2->id)
            ->assertDontSee('Coca-Cola 500ml')
            ->assertSee('Papas Lay\'s 100g');
    }

    public function test_it_filters_by_status(): void
    {
        $this->actingAs($this->user);

        $p1 = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Active Product',
            'slug' => 'active-product',
        ]);

        $p2 = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Deleted Product',
            'slug' => 'deleted-product',
        ]);
        $p2->delete();

        // Active only
        Livewire::test(ListProductos::class)
            ->set('estado', 'active')
            ->assertSee('Active Product')
            ->assertDontSee('Deleted Product');

        // Trashed only
        Livewire::test(ListProductos::class)
            ->set('estado', 'trashed')
            ->assertDontSee('Active Product')
            ->assertSee('Deleted Product');
    }

    public function test_it_searches_products(): void
    {
        $this->actingAs($this->user);

        Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Sprite 350ml',
            'slug' => 'sprite-350ml',
        ]);

        Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Fanta 350ml',
            'slug' => 'fanta-350ml',
        ]);

        Livewire::test(ListProductos::class)
            ->set('search', 'Sprite')
            ->assertSee('Sprite 350ml')
            ->assertDontSee('Fanta 350ml');
    }

    public function test_it_creates_product_successfully(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListProductos::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('nombre', 'Coca-Cola Zero')
            ->set('categoriaId', $this->categoria->id)
            ->set('marcaId', $this->marca->id)
            ->set('codigo_interno', 'CCZERO123')
            ->set('descripcion', 'Gaseosa sin azúcar')
            ->set('afecto_igv', true)
            ->set('activo', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('productos', [
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Coca-Cola Zero',
            'codigo_interno' => 'CCZERO123',
            'descripcion' => 'Gaseosa sin azúcar',
            'afecto_igv' => true,
            'activo' => true,
        ]);
    }

    public function test_it_validates_required_fields_when_creating_product(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ListProductos::class)
            ->call('openCreateModal')
            ->set('nombre', '')
            ->set('categoriaId', '')
            ->set('marcaId', '')
            ->call('save')
            ->assertHasErrors([
                'nombre' => 'required',
                'categoriaId' => 'required',
                'marcaId' => 'required',
            ]);
    }

    public function test_it_edits_existing_product(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Old Name',
            'slug' => 'old-name',
        ]);

        Livewire::test(ListProductos::class)
            ->call('openEditModal', $producto->id)
            ->assertSet('showModal', true)
            ->assertSet('nombre', 'Old Name')
            ->set('nombre', 'New Name')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'nombre' => 'New Name',
        ]);
    }

    public function test_it_soft_deletes_and_restores_product(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Trash Me',
            'slug' => 'trash-me',
        ]);

        // Soft delete
        Livewire::test(ListProductos::class)
            ->call('confirmDelete', $producto->id)
            ->assertSet('showDeleteConfirmModal', true)
            ->assertSet('productoToDeleteId', $producto->id)
            ->call('delete')
            ->assertSet('showDeleteConfirmModal', false)
            ->assertSet('productoToDeleteId', null);

        $this->assertSoftDeleted('productos', ['id' => $producto->id]);

        // Restore
        Livewire::test(ListProductos::class)
            ->call('restore', $producto->id);

        $this->assertDatabaseHas('productos', [
            'id' => $producto->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_permanently_deletes_product(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Burn Me',
            'slug' => 'burn-me',
        ]);
        $producto->delete();

        // Permanent delete
        Livewire::test(ListProductos::class)
            ->call('forceDelete', $producto->id);

        $this->assertDatabaseMissing('productos', ['id' => $producto->id]);
    }

    public function test_it_toggles_view_mode_and_opens_presentations(): void
    {
        $this->actingAs($this->user);

        $producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Test View Modes',
            'slug' => 'test-view-modes',
        ]);

        Livewire::test(ListProductos::class)
            ->assertSet('viewMode', 'grid')
            ->call('toggleViewMode', 'table')
            ->assertSet('viewMode', 'table')
            ->call('verPresentaciones', $producto->id)
            ->assertSet('selectedProductForPresentationsId', $producto->id);
    }
}
