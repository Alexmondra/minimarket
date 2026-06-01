<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class GananciasChart extends Component
{
    use HasEscritorioChart;

    public function mount(): void
    {
        $this->initializeChart('chart_ganancias');
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $result = $calc->gananciasUltimosMeses(12);

        $this->chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $result['labels'],
                'datasets' => [
                    [
                        'label' => 'Ingresos',
                        'data' => $result['ingresos'],
                    ],
                    [
                        'label' => 'Ganancia',
                        'data' => $result['ganancias'],
                    ],
                ],
            ],
        ];
        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-3">
            <div class="skeleton h-4 w-24"></div>
            <div class="skeleton h-48 w-full"></div>
        </div>
        HTML;
    }

}
