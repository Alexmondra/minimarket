<div class="glass-card colorful-card card-blue p-6">
    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-500 border border-blue-500/15">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </span>
        Bitácora de Actividad
    </h3>

    @if(empty($actividades))
        <div class="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-slate-200/50 dark:border-slate-800/30 rounded-2xl p-6">
            <svg class="h-6 w-6 text-slate-300 dark:text-slate-700 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            <p class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Sin actividad</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">No hay movimientos registrados en las últimas horas.</p>
        </div>
    @else
        <div class="space-y-0 pl-1">
            @foreach($actividades as $item)
                @php
                    $dotColor = [
                        'emerald' => 'bg-emerald-500 ring-4 ring-emerald-500/15',
                        'rose' => 'bg-rose-500 ring-4 ring-rose-500/15',
                        'amber' => 'bg-amber-500 ring-4 ring-amber-500/15',
                        'blue' => 'bg-blue-500 ring-4 ring-blue-500/15',
                    ][$item['icon_color']];
                @endphp
                <div class="activity-line">
                    <span class="activity-dot {{ $dotColor }}"></span>
                    <div class="flex items-start justify-between gap-2 mb-0.5">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200 leading-none">{{ $item['title'] }}</p>
                        <span class="text-[9.5px] font-bold text-slate-400 dark:text-slate-500 shrink-0">{{ $item['time'] }}</span>
                    </div>
                    <p class="text-[10.5px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                    <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-wider flex items-center gap-1.5">
                        <span>👤 {{ $item['user'] }}</span>
                        <span class="text-slate-300 dark:text-slate-700">·</span>
                        <span>📍 {{ $item['sucursal'] }}</span>
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <div wire:poll.30s="loadActividad"></div>
</div>

