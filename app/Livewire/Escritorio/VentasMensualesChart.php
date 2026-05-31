<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class VentasMensualesChart extends Component
{
    public string $chartId;
    public array $chartConfig = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->chartId = 'chart_ventas_mes_' . uniqid();
        $this->loadData();
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $result = $calc->ventasUltimosMeses(12);

        $this->chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $result['labels'],
                'datasets' => [[
                    'label' => 'Ventas Mensuales',
                    'data' => $result['data'],
                    'backgroundColor' => array_map(fn($v) => $v > 0 ? 'rgba(99, 102, 241, 0.7)' : 'rgba(226, 232, 240, 0.4)', $result['data']),
                    'borderColor' => 'rgba(99, 102, 241, 0.9)',
                    'borderWidth' => 1,
                    'borderRadius' => 6,
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
            <div class="skeleton h-64 w-full"></div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.escritorio.ventas-mensuales-chart');
    }
}
