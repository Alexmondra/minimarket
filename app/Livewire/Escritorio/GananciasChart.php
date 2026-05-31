<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class GananciasChart extends Component
{
    public string $chartId;
    public array $chartConfig = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->chartId = 'chart_ganancias_' . uniqid();
        $this->loadData();
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
                        'borderColor' => 'rgb(59, 130, 246)',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.06)',
                        'fill' => true,
                        'tension' => 0.4,
                        'borderWidth' => 2,
                        'pointRadius' => 3,
                        'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    ],
                    [
                        'label' => 'Ganancia',
                        'data' => $result['ganancias'],
                        'borderColor' => 'rgb(16, 185, 129)',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                        'fill' => true,
                        'tension' => 0.4,
                        'borderWidth' => 3,
                        'pointRadius' => 4,
                        'pointBackgroundColor' => 'rgb(16, 185, 129)',
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

    public function render()
    {
        return view('livewire.escritorio.ganancias-chart');
    }
}
