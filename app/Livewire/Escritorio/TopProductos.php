<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use App\Support\Reportes\ReporteQueryBuilder;
use Livewire\Component;

class TopProductos extends Component
{
    public array $productos = [];
    public string $tab = 'mas_vendidos';
    public bool $loaded = false;

    public function mount(): void
    {
        $this->loadData();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->loadData();
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $qb = app(ReporteQueryBuilder::class);

        if ($this->tab === 'mas_vendidos') {
            $this->productos = $calc->topProductos(10);
        } elseif ($this->tab === 'sin_movimiento') {
            // Products without sales in the last 60 days
            $this->productos = []; // Placeholder — requires more complex query
        }

        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-4">
            <div class="skeleton h-4 w-40"></div>
            @for ($i=0; $i<5; $i++)
                <div class="skeleton h-10 w-full"></div>
            @endfor
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.escritorio.top-productos');
    }
}
