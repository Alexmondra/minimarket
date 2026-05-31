<div>
    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">Dashboard de Reportes</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Selecciona un módulo de reporte para comenzar el análisis.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach($links as $link)
            <a href="{{ $link['url'] }}"
               class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 p-6 shadow-sm transition-all duration-300 hover:shadow-xl hover:-translate-y-1 hover:border-{{ $link['color'] }}-300 dark:hover:border-{{ $link['color'] }}-700">
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-{{ $link['color'] }}-400 to-{{ $link['color'] }}-600 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-{{ $link['color'] }}-500/10 text-{{ $link['color'] }}-600 dark:text-{{ $link['color'] }}-400 border border-{{ $link['color'] }}-500/15 group-hover:scale-110 transition-transform">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            @if($link['icon'] === 'heroicon-o-currency-dollar')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            @elseif($link['icon'] === 'heroicon-o-arrow-trending-up')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                            @elseif($link['icon'] === 'heroicon-o-arrow-trending-down')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.95 11.95 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/>
                            @elseif($link['icon'] === 'heroicon-o-shopping-bag')
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white group-hover:text-{{ $link['color'] }}-600 dark:group-hover:text-{{ $link['color'] }}-400 transition-colors">{{ $link['title'] }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $link['desc'] }}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
