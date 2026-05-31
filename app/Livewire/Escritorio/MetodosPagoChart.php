<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class MetodosPagoChart extends Component
{
    public string $chartId;
    public array $chartConfig = [];
    public bool $loaded = false;

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
        $this->chartId = 'chart_metodos_pago_' . uniqid();
        $this->loadData();
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
                    'borderWidth' => 2,
                    'hoverBorderWidth' => 3,
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

    public function render()
    {
        return view('livewire.escritorio.metodos-pago-chart');
    }
}
