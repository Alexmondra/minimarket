<div class="glass-card p-5">
    <h3 class="text-sm font-black text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        Actividad Reciente
    </h3>

    @if(empty($actividades))
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <p class="text-sm text-slate-400 dark:text-slate-500">No hay actividad reciente.</p>
        </div>
    @else
        <div class="space-y-0">
            @foreach($actividades as $item)
                @php
                    $dotColor = [
                        'emerald' => 'bg-emerald-500',
                        'rose' => 'bg-rose-500',
                        'amber' => 'bg-amber-500',
                        'blue' => 'bg-blue-500',
                    ][$item['icon_color']];
                @endphp
                <div class="activity-line">
                    <span class="activity-dot {{ $dotColor }}"></span>
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $item['title'] }}</p>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 shrink-0">{{ $item['time'] }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                        {{ $item['user'] }} · {{ $item['sucursal'] }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <div wire:poll.30s="loadActividad"></div>
</div>
