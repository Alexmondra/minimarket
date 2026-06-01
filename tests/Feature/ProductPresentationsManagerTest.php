<?php

namespace Tests\Feature;

use App\Models\Empresa;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UniMedida;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Almacen\ProductPresentationsManager;

class ProductPresentationsManagerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Empresa $empresa;
    private Producto $producto;
    private Categoria $categoria;
    private Marca $marca;
    private UniMedida $unidad;

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

        $this->unidad = UniMedida::create([
            'nombre' => 'Unidad',
            'abreviatura' => 'und',
            'activo' => true,
        ]);

        $this->producto = Producto::create([
            'empresa_id' => $this->empresa->id,
            'categoria_id' => $this->categoria->id,
            'marca_id' => $this->marca->id,
            'nombre' => 'Coca-Cola 500ml',
            'slug' => 'coca-cola-500ml',
            'codigo_interno' => 'COCA500',
        ]);
    }

    public function test_it_can_render_presentations_list(): void
    {
        $this->actingAs($this->user);

        ProductoPresentacion::create([
            'producto_id' => $this->producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Personal 500ml',
        ]);

        Livewire::test(ProductPresentationsManager::class, ['record' => $this->producto])
            ->assertStatus(200)
            ->assertSee('Personal 500ml')
            ->assertSee('und');
    }

    public function test_it_can_create_presentation(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ProductPresentationsManager::class, ['record' => $this->producto])
            ->call('abrirCrear')
            ->assertSet('showModal', true)
            ->set('tipo_presentacion', 'Caja x 12')
            ->set('cantidad', 12)
            ->set('unidad_medida_id', $this->unidad->id)
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('producto_presentacion', [
            'producto_id' => $this->producto->id,
            'tipo_presentacion' => 'Caja x 12',
            'cantidad' => 12,
            'unidad_medida_id' => $this->unidad->id,
        ]);
    }

    public function test_it_can_edit_presentation(): void
    {
        $this->actingAs($this->user);

        $presentation = ProductoPresentacion::create([
            'producto_id' => $this->producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Old Name',
        ]);

        Livewire::test(ProductPresentationsManager::class, ['record' => $this->producto])
            ->call('abrirEditar', $presentation->id)
            ->assertSet('showModal', true)
            ->assertSet('tipo_presentacion', 'Old Name')
            ->set('tipo_presentacion', 'New Name')
            ->call('guardar')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('producto_presentacion', [
            'id' => $presentation->id,
            'tipo_presentacion' => 'New Name',
        ]);
    }

    public function test_it_can_delete_presentation(): void
    {
        $this->actingAs($this->user);

        $presentation = ProductoPresentacion::create([
            'producto_id' => $this->producto->id,
            'unidad_medida_id' => $this->unidad->id,
            'cantidad' => 1,
            'tipo_presentacion' => 'Delete Me',
        ]);

        Livewire::test(ProductPresentationsManager::class, ['record' => $this->producto])
            ->call('confirmDelete', $presentation->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('presentationToDeleteId', $presentation->id)
            ->call('delete')
            ->assertHasNoErrors()
            ->assertSet('showDeleteModal', false)
            ->assertSet('presentationToDeleteId', null);

        $this->assertSoftDeleted('producto_presentacion', [
            'id' => $presentation->id,
        ]);
    }
}
