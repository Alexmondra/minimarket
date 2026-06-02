<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class GananciasChart extends Component
{
    use HasEscritorioChart;

    public int $meses = 3;

    public function mount(): void
    {
        $this->meses = (int) config('app.dashboard_ganancias_meses', 3);
        $this->initializeChart('chart_ganancias');
    }

    public function setMeses(int $meses): void
    {
        $this->meses = $meses;
        $this->loadData();
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $result = $calc->gananciasIngresosVentas($this->meses);

        $this->chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $result['labels'],
                'datasets' => [
                    [
                        'label' => 'Inversión (Ingreso)',
                        'data' => $result['ingresos'],
                    ],
                    [
                        'label' => 'Ventas Reales',
                        'data' => $result['ventas'],
                    ],
                    [
                        'label' => 'Ganancia Real',
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
