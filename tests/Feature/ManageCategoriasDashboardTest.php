<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Filament\Clusters\Global\Resources\Categorias\Pages\ManageCategorias;

class ManageCategoriasDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;

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
    }

    public function test_it_renders_categorias_dashboard_and_shows_kpis(): void
    {
        $this->actingAs($this->user);

        // Create some categories
        Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Bebidas', 'descripcion' => 'Gaseosas y Aguas']);
        Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Lácteos', 'descripcion' => 'Leches y Quesos']);
        $deleted = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Deleted Category', 'descripcion' => 'Test']);
        $deleted->delete();

        Livewire::test(ManageCategorias::class)
            ->assertStatus(200)
            ->assertSee('Bebidas')
            ->assertSee('Lácteos')
            ->assertSee('Deleted Category');
    }

    public function test_it_filters_by_status(): void
    {
        $this->actingAs($this->user);

        Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Active Category']);
        $deleted = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Deleted Category']);
        $deleted->delete();

        // Active only
        Livewire::test(ManageCategorias::class)
            ->set('estado', 'active')
            ->assertSee('Active Category')
            ->assertDontSee('Deleted Category');

        // Trashed only
        Livewire::test(ManageCategorias::class)
            ->set('estado', 'trashed')
            ->assertDontSee('Active Category')
            ->assertSee('Deleted Category');
    }

    public function test_it_searches_categories(): void
    {
        $this->actingAs($this->user);

        Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Bebidas con Gas']);
        Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Limpieza Hogar']);

        Livewire::test(ManageCategorias::class)
            ->set('search', 'Bebidas')
            ->assertSee('Bebidas con Gas')
            ->assertDontSee('Limpieza Hogar');
    }

    public function test_it_creates_category_successfully(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageCategorias::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('nombre', 'Snacks y Galletas')
            ->set('descripcion', 'Papas y galletas de chocolate')
            ->set('estado_campo', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('categorias', [
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Snacks y Galletas',
            'descripcion' => 'Papas y galletas de chocolate',
            'estado' => true,
        ]);
    }

    public function test_it_validates_required_nombre_when_creating_category(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageCategorias::class)
            ->call('openCreateModal')
            ->set('nombre', '')
            ->call('save')
            ->assertHasErrors(['nombre' => 'required']);
    }

    public function test_it_edits_existing_category(): void
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Old Name', 'estado' => true]);

        Livewire::test(ManageCategorias::class)
            ->call('openEditModal', $categoria->id)
            ->assertSet('showModal', true)
            ->assertSet('nombre', 'Old Name')
            ->set('nombre', 'New Name')
            ->set('estado_campo', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'nombre' => 'New Name',
            'estado' => false,
        ]);
    }

    public function test_it_soft_deletes_and_restores_category(): void
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Trash Me']);

        // Soft delete
        Livewire::test(ManageCategorias::class)
            ->call('confirmDelete', $categoria->id)
            ->assertSet('showDeleteConfirmModal', true)
            ->assertSet('categoriaToDeleteId', $categoria->id)
            ->call('delete')
            ->assertSet('showDeleteConfirmModal', false)
            ->assertSet('categoriaToDeleteId', null);

        $this->assertSoftDeleted('categorias', ['id' => $categoria->id]);

        // Restore
        Livewire::test(ManageCategorias::class)
            ->call('restore', $categoria->id);

        $this->assertDatabaseHas('categorias', [
            'id' => $categoria->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_permanently_deletes_category(): void
    {
        $this->actingAs($this->user);

        $categoria = Categoria::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Burn Me']);
        $categoria->delete();

        // Permanent delete
        Livewire::test(ManageCategorias::class)
            ->call('forceDelete', $categoria->id);

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    public function test_it_toggles_view_mode(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageCategorias::class)
            ->assertSet('viewMode', 'grid')
            ->call('toggleViewMode', 'table')
            ->assertSet('viewMode', 'table')
            ->call('toggleViewMode', 'grid')
            ->assertSet('viewMode', 'grid');
    }
}
