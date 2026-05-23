<?php

namespace Tests\Feature;

use App\Livewire\SucursalSelector;
use Livewire\Livewire;
use Tests\TestCase;

class SucursalSelectorTest extends TestCase
{
    public function test_it_renders_even_when_hidden(): void
    {
        Livewire::test(SucursalSelector::class)
            ->assertStatus(200);
    }
}
