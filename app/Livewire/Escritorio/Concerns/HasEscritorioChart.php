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
     * Render the Livewire component dynamically based on the class name.
     */
    public function render()
    {
        $viewName = 'livewire.escritorio.' . Str::kebab(class_basename($this));
        
        return view($viewName);
    }
}
