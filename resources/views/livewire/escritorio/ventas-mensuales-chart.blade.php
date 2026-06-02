<div class="glass-card p-5">
    <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
        Ventas Mensuales
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-[140px_1fr] gap-6 mt-2">
        <!-- Month Selector Panel -->
        <div class="flex flex-row md:flex-col gap-1.5 overflow-x-auto md:overflow-x-visible pb-2 md:pb-0 scrollbar-none shrink-0 bg-slate-50/50 dark:bg-slate-900/30 p-1.5 rounded-2xl border border-slate-100 dark:border-slate-800/40">
            @foreach($availableMonths as $m)
                <button
                    type="button"
                    wire:click="selectMonth('{{ $m['value'] }}')"
                    class="group flex-shrink-0 flex items-center px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all duration-300 md:w-full
                        {{ $selectedMonth === $m['value']
                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 scale-[1.02]'
                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/60 hover:text-slate-800 dark:hover:text-slate-200' }}"
                >
                    <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 mr-2.5
                        {{ $selectedMonth === $m['value'] ? 'bg-white scale-125' : 'bg-slate-300 dark:bg-slate-700 group-hover:bg-indigo-400' }}"></span>
                    <span class="md:hidden">{{ $m['short'] }}</span>
                    <span class="hidden md:inline">{{ $m['label'] }}</span>
                </button>
            @endforeach
        </div>

        <!-- Daily Bar Chart -->
        <div class="relative h-64" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
            <canvas id="{{ $chartId }}"></canvas>
        </div>
    </div>

    <div wire:poll.300s="loadData"></div>
</div>
