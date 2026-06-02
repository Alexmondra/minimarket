<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed an Empresa record
        Empresa::create([
            'ruc' => '20100066603',
            'razon_social' => 'MINIMARKET go go',
            'slug' => 'go-go',
            'direccion_fiscal' => 'Av. Principal 123, Lima',
        ]);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('¡Bienvenido de nuevo!');
        $response->assertSee('Correo electrónico');
        $response->assertSee('Contraseña');
    }

    public function test_login_component_is_rendered(): void
    {
        Livewire::test(Login::class)
            ->assertStatus(200)
            ->assertSee('¡Bienvenido de nuevo!')
            ->assertSee('Market')
            ->assertSee('G0')
            ->assertSee('Food Market');
    }
}
