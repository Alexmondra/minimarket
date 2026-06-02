<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class TopProductosChart extends Component
{
    use HasEscritorioChart;

    public string $periodo = 'mes';

    public function mount(): void
    {
        $this->initializeChart('chart_top_productos');
    }

    public function setPeriodo(string $periodo): void
    {
        $this->periodo = $periodo;
        $this->loadData();
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $data = $calc->topProductosPeriodo($this->periodo, 10);

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

}
