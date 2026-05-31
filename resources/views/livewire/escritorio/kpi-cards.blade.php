<div>
    {{-- Last updated indicator --}}
    <div class="flex items-center justify-between mb-4">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
            Indicadores clave
        </span>
        @if($lastUpdated)
            <span class="inline-flex items-center gap-1.5 text-[10px] font-semibold text-slate-400 dark:text-slate-500">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                </span>
                Actualizado {{ $lastUpdated }}
            </span>
        @endif
    </div>

    {{-- KPI Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
        @php
            $iconMap = [
                'heroicon-o-currency-dollar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                'heroicon-o-arrow-trending-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>',
                'heroicon-o-ticket' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/>',
                'heroicon-o-shopping-bag' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
                'heroicon-o-exclamation-triangle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>',
                'heroicon-o-clock' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
                'heroicon-o-users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
                'heroicon-o-banknotes' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M5.25 7.5h13.5m-12 9h10.5M5.25 15h13.5m-13.5-6h13.5m-13.5 3h13.5m-10.5 6h7.5M10.5 4.5V3a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5"/>',
                'heroicon-o-lock-open' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>',
            ];
        @endphp

        @foreach($kpis as $key => $kpi)
            @php
                $iconSvg = $iconMap[$kpi['icon']] ?? $iconMap['heroicon-o-currency-dollar'];
            @endphp
            <div class="kpi-card kpi-{{ $kpi['color'] }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-500/10 text-{{ $kpi['color'] }}-600 dark:text-{{ $kpi['color'] }}-400 border border-{{ $kpi['color'] }}-500/15">
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">{!! $iconSvg !!}</svg>
                    </div>
                    @if($kpi['trend'] !== null)
                        <span @class([
                            'inline-flex items-center gap-0.5 text-[10px] font-black rounded-lg px-1.5 py-0.5',
                            'text-emerald-700 dark:text-emerald-400 bg-emerald-500/10' => $kpi['trend_up'],
                            'text-rose-700 dark:text-rose-400 bg-rose-500/10' => !$kpi['trend_up'],
                        ])>
                            @if($kpi['trend_up'])
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/></svg>
                            @else
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/></svg>
                            @endif
                            {{ abs($kpi['trend']) }}%
                        </span>
                    @endif
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">{{ $kpi['label'] }}</p>
                <p class="text-lg font-black text-slate-900 dark:text-white font-mono tracking-tight">
                    {{ $kpi['prefix'] }}{{ $kpi['value'] }}{{ $kpi['suffix'] }}
                </p>
            </div>
        @endforeach
    </div>

    {{-- Auto-refresh every 60 seconds --}}
    <div wire:poll.60s="loadKpis"></div>
</div>
