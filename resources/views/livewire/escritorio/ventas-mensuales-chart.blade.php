<div class="glass-card colorful-card card-indigo p-0">
    {{-- Card Header --}}
    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800/40 bg-gradient-to-r from-indigo-500/8 via-indigo-500/2 to-transparent dark:from-indigo-500/10 dark:via-transparent dark:to-transparent flex items-center justify-between">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2.5">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-500 text-white shadow-xs">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            </span>
            Ventas por Mes
        </h3>
    </div>

    {{-- Card Body --}}
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-[140px_1fr] gap-6">
            <!-- Month Selector Panel (Monochrome, SaaS-style) -->
            <div class="flex flex-row md:flex-col gap-1 overflow-x-auto md:overflow-x-visible pb-2 md:pb-0 scrollbar-none shrink-0 bg-slate-100/50 dark:bg-slate-900/30 p-1 rounded-xl border border-slate-200/30 dark:border-slate-800/20">
                @foreach($availableMonths as $m)
                    <button
                        type="button"
                        wire:click="selectMonth('{{ $m['value'] }}')"
                        class="group flex-shrink-0 flex items-center px-3 py-2 rounded-lg text-[11px] font-bold transition-all duration-300 md:w-full
                            {{ $selectedMonth === $m['value']
                                ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm scale-[1.01]'
                                : 'text-slate-500 dark:text-slate-400 hover:bg-slate-200/50 dark:hover:bg-slate-800/40 hover:text-slate-800 dark:hover:text-slate-200' }}"
                    >
                        <span class="h-1.5 w-1.5 rounded-full transition-all duration-300 mr-2
                            {{ $selectedMonth === $m['value'] ? 'bg-amber-500 scale-125' : 'bg-slate-300 dark:bg-slate-700 group-hover:bg-amber-400' }}"></span>
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
    </div>


    <div wire:poll.300s="loadData"></div>
</div>

