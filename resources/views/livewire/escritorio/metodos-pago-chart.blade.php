<div class="glass-card p-5">
    <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/></svg>
        Métodos de Pago
        <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 ml-1">— Este mes</span>
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-[1.1fr_1fr] gap-4 items-center mt-2">
        <!-- Donut Chart Canvas — center text rendered by JS plugin -->
        <div class="relative h-48 sm:h-52" wire:ignore x-data="chartComponent(@js($chartId), @js($chartConfig))">
            <canvas id="{{ $chartId }}"></canvas>
        </div>

        <!-- Premium Custom Legend -->
        <div class="space-y-2 max-h-52 overflow-y-auto pr-1 scrollbar-none">
            @if(empty($legendData))
                <div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 py-8 text-center">
                    <svg class="h-8 w-8 mb-2 text-slate-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                    <p class="text-[11px] font-bold">Sin transacciones</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">este mes</p>
                </div>
            @else
                @foreach($legendData as $item)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/50 dark:bg-slate-900/30 border border-slate-100/50 dark:border-slate-800/40 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 transition-all duration-200 cursor-default">
                        <div class="flex items-center gap-2.5">
                            <span class="h-2.5 w-2.5 rounded-full shrink-0 shadow-sm" style="background-color: {{ $item['color'] }}"></span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $item['label'] }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-black text-slate-900 dark:text-white">S/ {{ number_format($item['value'], 2) }}</span>
                            <span class="block text-[9px] font-bold text-slate-400 dark:text-slate-500">{{ number_format($item['percentage'], 1) }}%</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div wire:poll.300s="loadData"></div>
</div>
