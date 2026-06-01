<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class VentasPorDiaChart extends Component
{
    use HasEscritorioChart;

    public function mount(): void
    {
        $this->initializeChart('chart_ventas_dia');
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $result = $calc->ventasUltimosDias(7);

        $this->chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $result['labels'],
                'datasets' => [[
                    'label' => 'Ventas',
                    'data' => $result['data'],
                ]],
            ],
        ];
        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-3">
            <div class="skeleton h-4 w-32"></div>
            <div class="skeleton h-64 w-full"></div>
        </div>
        HTML;
    }

}
