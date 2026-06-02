<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class MetodosPagoChart extends Component
{
    use HasEscritorioChart;

    public array $legendData = [];

    private const COLORS = [
        'EFECTIVO' => ['#10b981', '#34d399'],
        'YAPE' => ['#8b5cf6', '#a78bfa'],
        'PLIN' => ['#06b6d4', '#22d3ee'],
        'TRANSFERENCIA' => ['#3b82f6', '#60a5fa'],
        'TARJETA' => ['#f59e0b', '#fbbf24'],
        'OTRO' => ['#64748b', '#94a3b8'],
    ];

    public function mount(): void
    {
        $this->initializeChart('chart_metodos_pago');
    }

    public function loadData(): void
    {
        $calc = app(MetricCalculator::class);
        $data = $calc->metodosPagoDistribution();

        $labels = [];
        $values = [];
        $colors = [];
        $bgColors = [];
        $rawLegend = [];

        $totalSum = 0.0;
        foreach ($data as $row) {
            $rawMp = $row['medio_pago'] ?? 'OTRO';
            $mpKey = strtoupper(trim($rawMp));
            $labels[] = ucfirst(strtolower($rawMp));
            
            $totalVal = (float) $row['total'];
            $values[] = $totalVal;
            $totalSum += $totalVal;
            
            $c = self::COLORS[$mpKey] ?? self::COLORS['OTRO'];
            $colors[] = $c[0];
            $bgColors[] = $c[1];
            
            $rawLegend[] = [
                'label' => ucfirst(strtolower($rawMp)),
                'value' => $totalVal,
                'color' => $c[0],
            ];
        }

        if (empty($values)) {
            $labels = ['Sin datos'];
            $values = [1.0];
            $colors = ['#cbd5e1'];
            $bgColors = ['#e2e8f0'];
            $this->legendData = [];
        } else {
            $this->legendData = [];
            foreach ($rawLegend as $item) {
                $pct = $totalSum > 0 ? ($item['value'] / $totalSum) * 100 : 0;
                $this->legendData[] = [
                    'label' => $item['label'],
                    'value' => $item['value'],
                    'percentage' => $pct,
                    'color' => $item['color'],
                ];
            }
        }

        $this->chartConfig = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $values,
                    'backgroundColor' => $bgColors,
                    'borderColor' => $colors,
                ]],
            ],
        ];
        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-3">
            <div class="skeleton h-4 w-28"></div>
            <div class="skeleton h-48 w-full rounded-full mx-auto max-w-[180px]"></div>
        </div>
        HTML;
    }

}
