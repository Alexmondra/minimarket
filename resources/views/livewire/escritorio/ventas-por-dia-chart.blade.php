<div class="glass-card p-5">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/></svg>
            Ventas Acumuladas de la Semana
        </h3>
        @if($totalSemana > 0)
            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-500/20">
                S/ {{ number_format($totalSemana, 2) }}
            </span>
        @endif
    </div>

    <div class="relative h-64" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
        <canvas id="{{ $chartId }}"></canvas>
    </div>

    @if($totalSemana <= 0 && $loaded)
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">Sin ventas esta semana</p>
        </div>
    @endif

    <div wire:poll.300s="loadData"></div>
</div>
