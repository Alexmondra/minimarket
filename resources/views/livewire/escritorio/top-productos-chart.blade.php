<div class="glass-card colorful-card card-violet p-6">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-500/10 text-violet-500 border border-violet-500/15">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
            </span>
            Top 10 Productos
        </h3>

        <!-- Period Selector (Monochrome SaaS style) -->
        <div class="flex bg-slate-100 dark:bg-slate-900/60 p-0.5 rounded-lg text-[10px] font-bold border border-slate-200/30 dark:border-slate-800/20">
            <button
                type="button"
                wire:click="setPeriodo('dia')"
                class="px-2.5 py-1 rounded-md transition-all duration-200
                    {{ $periodo === 'dia' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
            >Día</button>
            <button
                type="button"
                wire:click="setPeriodo('semana')"
                class="px-2.5 py-1 rounded-md transition-all duration-200
                    {{ $periodo === 'semana' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
            >Semana</button>
            <button
                type="button"
                wire:click="setPeriodo('mes')"
                class="px-2.5 py-1 rounded-md transition-all duration-200
                    {{ $periodo === 'mes' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
            >Mes</button>
        </div>
    </div>

    @if(empty($chartConfig['data']['labels'] ?? []))
        <div class="flex flex-col items-center justify-center h-72 text-slate-400 dark:text-slate-500 border-2 border-dashed border-slate-200/50 dark:border-slate-800/30 rounded-xl p-6 text-center">
            <svg class="h-8 w-8 mb-2 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            <p class="text-xs font-semibold">No hay ventas en este período</p>
        </div>
    @else
        <div class="relative h-72" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    @endif

    <div wire:poll.300s="loadData"></div>
</div>

