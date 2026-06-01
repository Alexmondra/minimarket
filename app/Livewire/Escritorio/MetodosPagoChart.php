<?php

namespace App\Livewire\Escritorio;

use App\Livewire\Escritorio\Concerns\HasEscritorioChart;
use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class MetodosPagoChart extends Component
{
    use HasEscritorioChart;

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

        foreach ($data as $row) {
            $mp = $row['medio_pago'] ?? 'OTRO';
            $labels[] = ucfirst(strtolower($mp));
            $values[] = (float) $row['total'];
            $c = self::COLORS[$mp] ?? self::COLORS['OTRO'];
            $colors[] = $c[0];
            $bgColors[] = $c[1];
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
