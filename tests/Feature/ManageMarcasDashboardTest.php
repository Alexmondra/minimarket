<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Marca;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Filament\Clusters\Almacen\Resources\Marcas\Pages\ManageMarcas;

class ManageMarcasDashboardTest extends TestCase
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

    public function test_it_renders_marcas_dashboard_and_shows_kpis(): void
    {
        $this->actingAs($this->user);

        // Create some marcas
        Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Gloria', 'descripcion' => 'Lácteos']);
        Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Nestle', 'descripcion' => 'Chocolates']);
        $deleted = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Deleted Brand', 'descripcion' => 'Test']);
        $deleted->delete();

        Livewire::test(ManageMarcas::class)
            ->assertStatus(200)
            ->assertSee('Gloria')
            ->assertSee('Nestle')
            ->assertSee('Deleted Brand');
    }

    public function test_it_filters_by_status(): void
    {
        $this->actingAs($this->user);

        Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Active Brand']);
        $deleted = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Deleted Brand']);
        $deleted->delete();

        // Active only
        Livewire::test(ManageMarcas::class)
            ->set('estado', 'active')
            ->assertSee('Active Brand')
            ->assertDontSee('Deleted Brand');

        // Trashed only
        Livewire::test(ManageMarcas::class)
            ->set('estado', 'trashed')
            ->assertDontSee('Active Brand')
            ->assertSee('Deleted Brand');
    }

    public function test_it_searches_brands(): void
    {
        $this->actingAs($this->user);

        Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Gloria Lácteos']);
        Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Coca-Cola Bebidas']);

        Livewire::test(ManageMarcas::class)
            ->set('search', 'Gloria')
            ->assertSee('Gloria Lácteos')
            ->assertDontSee('Coca-Cola Bebidas');
    }

    public function test_it_creates_brand_successfully(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageMarcas::class)
            ->call('openCreateModal')
            ->assertSet('showModal', true)
            ->set('nombre', 'Soprole')
            ->set('descripcion', 'Lácteos chilenos')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('marcas', [
            'empresa_id' => $this->empresa->id,
            'nombre' => 'Soprole',
            'descripcion' => 'Lácteos chilenos',
        ]);
    }

    public function test_it_validates_required_nombre_when_creating_brand(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageMarcas::class)
            ->call('openCreateModal')
            ->set('nombre', '')
            ->call('save')
            ->assertHasErrors(['nombre' => 'required']);
    }

    public function test_it_edits_existing_brand(): void
    {
        $this->actingAs($this->user);

        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Old Name']);

        Livewire::test(ManageMarcas::class)
            ->call('openEditModal', $marca->id)
            ->assertSet('showModal', true)
            ->assertSet('nombre', 'Old Name')
            ->set('nombre', 'New Name')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('marcas', [
            'id' => $marca->id,
            'nombre' => 'New Name',
        ]);
    }

    public function test_it_soft_deletes_and_restores_brand(): void
    {
        $this->actingAs($this->user);

        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Trash Me']);

        // Soft delete
        Livewire::test(ManageMarcas::class)
            ->call('confirmDelete', $marca->id)
            ->assertSet('showDeleteConfirmModal', true)
            ->assertSet('marcaToDeleteId', $marca->id)
            ->call('delete')
            ->assertSet('showDeleteConfirmModal', false)
            ->assertSet('marcaToDeleteId', null);

        $this->assertSoftDeleted('marcas', ['id' => $marca->id]);

        // Restore
        Livewire::test(ManageMarcas::class)
            ->call('restore', $marca->id);

        $this->assertDatabaseHas('marcas', [
            'id' => $marca->id,
            'deleted_at' => null,
        ]);
    }

    public function test_it_permanently_deletes_brand(): void
    {
        $this->actingAs($this->user);

        $marca = Marca::create(['empresa_id' => $this->empresa->id, 'nombre' => 'Burn Me']);
        $marca->delete();

        // Permanent delete
        Livewire::test(ManageMarcas::class)
            ->call('forceDelete', $marca->id);

        $this->assertDatabaseMissing('marcas', ['id' => $marca->id]);
    }
}
