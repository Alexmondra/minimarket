<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\MetricCalculator;
use Livewire\Component;

class KpiCards extends Component
{
    public array $kpis = [];

    public ?string $lastUpdated = null;

    public bool $loaded = false;

    public function mount(): void
    {
        $this->loadKpis();
    }

    public function loadKpis(): void
    {
        $calculator = app(MetricCalculator::class);
        $this->kpis = $calculator->allKpis();
        $this->lastUpdated = now()->format('H:i:s');
        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="kpi-strip-grid">
            @for ($i = 0; $i < 3; $i++)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 space-y-3">
                    <div class="skeleton h-8 w-8 rounded-xl"></div>
                    <div class="skeleton h-3 w-20"></div>
                    <div class="skeleton h-6 w-24"></div>
                    <div class="skeleton h-3 w-12"></div>
                </div>
            @endfor
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.escritorio.kpi-cards');
    }
}
