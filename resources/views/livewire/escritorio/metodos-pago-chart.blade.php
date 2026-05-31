<div class="glass-card p-5">
    <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/></svg>
        Métodos de Pago
    </h3>
    <div class="relative h-56" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
    <div wire:poll.300s="loadData"></div>
</div>
