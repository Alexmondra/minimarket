<?php

namespace App\Livewire\Escritorio\Concerns;

use Illuminate\Support\Str;

trait HasEscritorioChart
{
    /**
     * Unique HTML element ID for the chart canvas.
     */
    public string $chartId;

    /**
     * Configuration array sent to Chart.js via Alpine.
     */
    public array $chartConfig = [];

    /**
     * flag indicating if the data is loaded.
     */
    public bool $loaded = false;

    /**
     * Mount helper to initialize chart configuration and load metrics.
     */
    public function initializeChart(string $prefix): void
    {
        $this->chartId = $prefix . '_' . uniqid();
        $this->loadData();
    }

    /**
     * After chartConfig is updated (e.g. month/period change), push the
     * new config to the browser so the chart inside wire:ignore can
     * pick it up and re-render.
     */
    public function updatedChartConfig(): void
    {
        $this->dispatch('chart-refresh', [
            'chartId' => $this->chartId,
            'config' => $this->chartConfig,
        ]);
    }

    /**
     * Render the Livewire component dynamically based on the class name.
     */
    public function render()
    {
        if ($this->loaded && request()->hasHeader('X-Livewire')) {
            $this->dispatch('chart-refresh', [
                'chartId' => $this->chartId,
                'config' => $this->chartConfig,
            ]);
        }

        $viewName = 'livewire.escritorio.' . Str::kebab(class_basename($this));

        return view($viewName);
    }
}
