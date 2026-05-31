<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class VentasPorDiaChart extends Component
{
    public string $chartId;
    public array $chartConfig = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->chartId = 'chart_ventas_dia_' . uniqid();
        $this->loadData();
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
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => 'rgb(16, 185, 129)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'borderWidth' => 3,
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

    public function render()
    {
        return view('livewire.escritorio.ventas-por-dia-chart');
    }
}
