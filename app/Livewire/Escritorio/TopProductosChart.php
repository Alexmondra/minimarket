<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class TopProductosChart extends Component
{
    public string $chartId;
    public array $chartConfig = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->chartId = 'chart_top_productos_' . uniqid();
        $this->loadData();
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $data = $calc->topProductos(10);

        $labels = [];
        $values = [];
        foreach ($data as $row) {
            $labels[] = $row['producto_nombre'];
            $values[] = (int) $row['total_ventas'];
        }

        // Reverse for horizontal bar (bottom to top)
        $labels = array_reverse($labels);
        $values = array_reverse($values);

        $this->chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Ventas',
                    'data' => $values,
                    'backgroundColor' => array_map(fn() => 'rgba(139, 92, 246, 0.6)', $values),
                    'borderColor' => 'rgba(139, 92, 246, 0.8)',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false,
                ]],
            ],
        ];
        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-3">
            <div class="skeleton h-4 w-36"></div>
            @for ($i = 0; $i < 5; $i++)
                <div class="skeleton h-6 w-full"></div>
            @endfor
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.escritorio.top-productos-chart');
    }
}
