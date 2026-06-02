<div class="glass-card p-5">
    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
        <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 flex items-center gap-2">
            <svg class="h-4 w-4 text-teal-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
            Ingresos vs Ganancia
        </h3>

        <!-- Time Range Selector -->
        <div class="flex bg-slate-100 dark:bg-slate-800 p-0.5 rounded-lg text-[10px] font-bold">
            <button
                type="button"
                wire:click="setMeses(3)"
                class="px-2.5 py-1 rounded-md transition-all duration-200
                    {{ $meses === 3 ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
            >3M</button>
            <button
                type="button"
                wire:click="setMeses(6)"
                class="px-2.5 py-1 rounded-md transition-all duration-200
                    {{ $meses === 6 ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
            >6M</button>
            <button
                type="button"
                wire:click="setMeses(12)"
                class="px-2.5 py-1 rounded-md transition-all duration-200
                    {{ $meses === 12 ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}"
            >12M</button>
        </div>
    </div>

    <!-- Legend chips (compact, always visible as reference) -->
    <div class="flex items-center gap-4 mb-2 flex-wrap">
        <div class="flex items-center gap-1.5">
            <span class="h-0.5 w-4 rounded-full" style="background: repeating-linear-gradient(to right, #3b82f6 0px, #3b82f6 3px, transparent 3px, transparent 6px)"></span>
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Inversión</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-0.5 w-4 rounded-full bg-indigo-500"></span>
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Ventas</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-[3px] w-4 rounded-full bg-teal-500"></span>
            <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">Ganancia</span>
        </div>
    </div>

    <div class="relative h-56" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
        <canvas id="{{ $chartId }}"></canvas>
    </div>

    <div wire:poll.300s="loadData"></div>
</div>
