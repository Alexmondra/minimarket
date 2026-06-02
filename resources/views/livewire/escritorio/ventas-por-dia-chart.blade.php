<div class="glass-card colorful-card card-emerald p-0">
    {{-- Card Header --}}
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/40 bg-gradient-to-r from-emerald-500/8 via-emerald-500/2 to-transparent dark:from-emerald-500/10 dark:via-transparent dark:to-transparent flex items-center justify-between">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2.5">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-xs">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5"/></svg>
            </span>
            Ventas de la Semana
        </h3>
        @if($totalSemana > 0)
            <span class="text-[11px] font-black font-mono tracking-tight px-3 py-1.5 rounded-lg bg-emerald-500 text-white border border-emerald-600/10 shadow-xs">
                <span class="text-[9px] font-semibold opacity-85 mr-0.5">S/</span>{{ number_format($totalSemana, 2) }}
            </span>
        @endif
    </div>

    {{-- Card Body --}}
    <div class="p-6">
        <div class="relative h-64" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
            <canvas id="{{ $chartId }}"></canvas>
        </div>

        @if($totalSemana <= 0 && $loaded)
            <div class="absolute inset-x-0 bottom-12 flex flex-col items-center justify-center pointer-events-none p-6 text-center">
                <svg class="h-8 w-8 text-slate-300 dark:text-slate-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" />
                </svg>
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">Sin ventas registradas en la semana actual</p>
            </div>
        @endif
    </div>

    <div wire:poll.300s="loadData"></div>
</div>


