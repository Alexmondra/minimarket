<div class="glass-card colorful-card card-amber p-6">
    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-4 flex items-center gap-2">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500 border border-amber-500/15">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
        </span>
        Alertas Críticas
    </h3>

    @if(empty($alertas))
        <div class="flex flex-col items-center justify-center py-8 text-center border-2 border-dashed border-slate-200/50 dark:border-slate-800/30 rounded-2xl p-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/15 mb-3">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <p class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Todo en orden</p>
            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">No hay alertas de stock ni vencimientos pendientes.</p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach($alertas as $alerta)
                @php
                    $colorMap = [
                        'rose' => 'border-rose-200/40 dark:border-rose-950/40 bg-rose-500/4 dark:bg-rose-500/3',
                        'amber' => 'border-amber-200/40 dark:border-amber-950/40 bg-amber-500/4 dark:bg-amber-500/3',
                        'orange' => 'border-orange-200/40 dark:border-orange-950/40 bg-orange-500/4 dark:bg-orange-500/3',
                        'emerald' => 'border-emerald-200/40 dark:border-emerald-950/40 bg-emerald-500/4 dark:bg-emerald-500/3',
                    ];
                    $iconColorMap = [
                        'rose' => 'text-rose-600 dark:text-rose-400 bg-rose-500/10 border-rose-500/15',
                        'amber' => 'text-amber-600 dark:text-amber-400 bg-amber-500/10 border-amber-500/15',
                        'orange' => 'text-orange-600 dark:text-orange-400 bg-orange-500/10 border-orange-500/15',
                        'emerald' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/15',
                    ];
                @endphp
                <div class="flex items-start gap-3 p-3.5 rounded-xl border {{ $colorMap[$alerta['color']] }} hover:shadow-xs transition-all duration-300">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border {{ $iconColorMap[$alerta['color']] }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            @if($alerta['icon'] === 'heroicon-o-x-circle')
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            @elseif($alerta['icon'] === 'heroicon-o-clock')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            @elseif($alerta['icon'] === 'heroicon-o-calendar-days')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            @elseif($alerta['icon'] === 'heroicon-o-exclamation-triangle')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            @elseif($alerta['icon'] === 'heroicon-o-lock-open')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            @endif
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-xs font-black text-slate-800 dark:text-slate-200">{{ $alerta['title'] }}</p>
                            <span @class([
                                'shrink-0 rounded-md px-1.5 py-0.5 text-[9.5px] font-black font-mono',
                                'bg-rose-500/10 text-rose-600 dark:text-rose-400' => $alerta['color'] === 'rose',
                                'bg-amber-500/10 text-amber-600 dark:text-amber-400' => $alerta['color'] === 'amber',
                                'bg-orange-500/10 text-orange-600 dark:text-orange-400' => $alerta['color'] === 'orange',
                                'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' => $alerta['color'] === 'emerald',
                            ])>
                                {{ $alerta['count'] }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $alerta['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div wire:poll.120s="loadAlertas"></div>
</div>

