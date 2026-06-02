<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class VentasMensualesChart extends Component
{
    use HasEscritorioChart;

    public array $availableMonths = [];
    public string $selectedMonth;

    public function mount(): void
    {
        $this->availableMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = today()->subMonths($i);
            $this->availableMonths[] = [
                'value' => $date->format('Y-m'),
                'label' => ucfirst($date->translatedFormat('F')),
                'short' => ucfirst($date->translatedFormat('M')),
            ];
        }
        $this->selectedMonth = today()->format('Y-m');

        $this->initializeChart('chart_ventas_mes');
    }

    public function selectMonth(string $month): void
    {
        $this->selectedMonth = $month;
        $this->loadData();
    }

    public function loadData(): void
    {
        $parts = explode('-', $this->selectedMonth);
        $year = (int) $parts[0];
        $month = (int) $parts[1];

        $calc = app(MetricCalculator::class);
        $result = $calc->ventasDiariasMes($year, $month);

        $this->chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $result['labels'],
                'datasets' => [[
                    'label' => 'Ventas Diarias',
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
            <div class="skeleton h-4 w-36"></div>
            <div class="skeleton h-64 w-full"></div>
        </div>
        HTML;
    }

}
