<x-filament-panels::page @class(['escritorio-dashboard'])>
    @vite(['resources/js/app.js'])

    @canany(['reportes.ver', 'ventas.crear', 'ventas.ver', 'productos.ver', 'productos.crear', 'compras.crear'])
        @php
            $quickActions = [
                [
                    'label' => 'Ingreso de productos a tienda',
                    'description' => 'Añade mercadería y actualiza stock sin entrar a menús.',
                    'href' => url('/admin/ingreso-productos'),
                    'visible' => auth()->user()?->can('productos.crear') || auth()->user()?->can('compras.crear') || auth()->user()?->can('ventas.crear'),
                    'surface' => 'bg-gradient-to-br from-amber-500 via-amber-600 to-orange-600 dark:from-amber-950/20 dark:via-slate-900/95 dark:to-slate-950/98',
                    'border' => 'border-white/15 dark:border-amber-500/15 dark:hover:border-amber-400/80',
                    'text_title' => 'text-white font-extrabold dark:text-amber-100',
                    'text_desc' => 'text-white/80 dark:text-amber-300/60',
                    'badge' => 'bg-white/10 text-white ring-1 ring-white/25 group-hover:bg-white/20 backdrop-blur dark:bg-amber-500/20 dark:text-amber-300 dark:ring-amber-500/30 dark:group-hover:bg-amber-500/30',
                    'glow' => 'bg-white/10',
                    'shadow' => 'shadow-amber-500/20',
                    'hover_shadow' => 'hover:shadow-[0_20px_40px_rgba(245,158,11,0.25)]',
                    'icon_container' => 'dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
                    'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z',
                ],
                [
                    'label' => 'Registrar venta',
                    'description' => 'Abre el punto de venta y atiende al cliente al instante.',
                    'href' => url('/admin/documentos/registrar'),
                    'visible' => auth()->user()?->can('ventas.crear'),
                    'surface' => 'bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 dark:from-emerald-950/20 dark:via-slate-900/95 dark:to-slate-950/98',
                    'border' => 'border-white/15 dark:border-emerald-500/15 dark:hover:border-emerald-400/80',
                    'text_title' => 'text-white font-extrabold dark:text-emerald-100',
                    'text_desc' => 'text-white/80 dark:text-emerald-300/60',
                    'badge' => 'bg-white/10 text-white ring-1 ring-white/25 group-hover:bg-white/20 backdrop-blur dark:bg-emerald-500/20 dark:text-emerald-300 dark:ring-emerald-500/30 dark:group-hover:bg-emerald-500/30',
                    'glow' => 'bg-white/10',
                    'shadow' => 'shadow-emerald-500/20',
                    'hover_shadow' => 'hover:shadow-[0_20px_40px_rgba(16,185,129,0.25)]',
                    'icon_container' => 'dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
                    'icon' => 'M12 4.5v15m7.5-7.5h-15',
                ],
                [
                    'label' => 'Visualizar productos en tienda',
                    'description' => 'Revisa existencias, precios y productos disponibles.',
                    'href' => url('/admin/stock-sucursal/stock-sucursals'),
                    'visible' => auth()->user()?->can('productos.ver'),
                    'surface' => 'bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 dark:from-indigo-950/20 dark:via-slate-900/95 dark:to-slate-950/98',
                    'border' => 'border-white/15 dark:border-indigo-500/15 dark:hover:border-indigo-400/80',
                    'text_title' => 'text-white font-extrabold dark:text-indigo-100',
                    'text_desc' => 'text-white/80 dark:text-indigo-300/60',
                    'badge' => 'bg-white/10 text-white ring-1 ring-white/25 group-hover:bg-white/20 backdrop-blur dark:bg-indigo-500/20 dark:text-indigo-300 dark:ring-indigo-500/30 dark:group-hover:bg-indigo-500/30',
                    'glow' => 'bg-white/10',
                    'shadow' => 'shadow-indigo-500/20',
                    'hover_shadow' => 'hover:shadow-[0_20px_40px_rgba(99,102,241,0.25)]',
                    'icon_container' => 'dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20',
                    'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6Zm4.5 3.75h7.5m-7.5 3h7.5m-7.5 3h4.5',
                ],
                [
                    'label' => 'Ventas registradas',
                    'description' => 'Consulta comprobantes, pagos y ventas emitidas.',
                    'href' => url('/admin/documentos'),
                    'visible' => auth()->user()?->can('ventas.ver'),
                    'surface' => 'bg-gradient-to-br from-blue-500 via-blue-600 to-cyan-600 dark:from-blue-950/20 dark:via-slate-900/95 dark:to-slate-950/98',
                    'border' => 'border-white/15 dark:border-blue-500/15 dark:hover:border-blue-400/80',
                    'text_title' => 'text-white font-extrabold dark:text-blue-100',
                    'text_desc' => 'text-white/80 dark:text-blue-300/60',
                    'badge' => 'bg-white/10 text-white ring-1 ring-white/25 group-hover:bg-white/20 backdrop-blur dark:bg-blue-500/20 dark:text-blue-300 dark:ring-blue-500/30 dark:group-hover:bg-blue-500/30',
                    'glow' => 'bg-white/10',
                    'shadow' => 'shadow-blue-500/20',
                    'hover_shadow' => 'hover:shadow-[0_20px_40px_rgba(59,130,246,0.25)]',
                    'icon_container' => 'dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
                    'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                ],
            ];

            $visibleActions = array_values(array_filter($quickActions, fn($action) => $action['visible']));
            $totalVisible = count($visibleActions);
        @endphp

        <section class="relative overflow-hidden rounded-[2rem] border border-slate-200/70 bg-white/85 p-5 shadow-sm ring-1 ring-white/70 backdrop-blur-sm dark:border-slate-800/70 dark:bg-slate-950/55 dark:ring-white/5 sm:p-7 lg:p-8">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-amber-400/20 blur-3xl dark:bg-amber-400/10"></div>
            <div class="absolute -bottom-28 left-1/4 h-72 w-72 rounded-full bg-emerald-400/15 blur-3xl dark:bg-emerald-400/10"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <span class="block text-xs font-black uppercase tracking-[0.2em] text-slate-400 dark:text-slate-500 mb-2">
                        Hola 👋 {{ auth()->user()?->name ?? 'Usuario' }}
                    </span>
                    <h1 class="text-4xl font-black tracking-tight text-slate-950 dark:text-white sm:text-5xl lg:text-6xl">
                        ¿QUE HACEMOS HOY?
                    </h1>
                </div>

                <div x-data="{
                    time: '',
                    date: '',
                    updateTime() {
                        const dateOptions = { timeZone: 'America/Lima', day: '2-digit', month: '2-digit', year: 'numeric' };
                        const timeOptions = { timeZone: 'America/Lima', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
                        const now = new Date();
                        this.date = new Intl.DateTimeFormat('es-PE', dateOptions).format(now);
                        this.time = new Intl.DateTimeFormat('es-PE', timeOptions).format(now);
                    }
                }" x-init="updateTime(); setInterval(() => updateTime(), 1000)" class="rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-sm font-bold text-slate-600 shadow-inner dark:border-slate-800/80 dark:bg-slate-900/70 dark:text-slate-300">
                    <span class="block text-[10px] font-black uppercase tracking-[0.22em] text-slate-400 dark:text-slate-500">Hoy</span>
                    <span x-text="date"></span> · <span x-text="time" class="font-mono"></span>
                </div>
            </div>
        </section>

        <div class="mt-6 space-y-6">
            <main class="min-w-0 space-y-6">
                <section class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($visibleActions as $index => $action)
                        @php
                            $cardClass = '';
                            if ($totalVisible === 4) {
                                if ($index === 3) {
                                    $cardClass = 'lg:col-span-3';
                                }
                            } elseif ($totalVisible === 3) {
                                if ($index === 2) {
                                    $cardClass = 'sm:col-span-2 lg:col-span-1';
                                }
                            } elseif ($totalVisible === 1) {
                                $cardClass = 'sm:col-span-2 lg:col-span-3';
                            }
                        @endphp
                        <a href="{{ $action['href'] }}" class="liquid-card group relative flex h-full flex-col justify-between overflow-hidden rounded-[1.5rem] border {{ $action['border'] }} p-3.5 {{ $action['shadow'] }} ring-1 ring-white/20 transition-all duration-500 ease-out hover:-translate-y-1.5 hover:ring-white/35 sm:p-4 {{ $action['hover_shadow'] }} {{ $action['surface'] }} {{ $cardClass }}">
                            <div class="absolute -right-16 -top-16 h-44 w-44 rounded-full {{ $action['glow'] }} blur-3xl opacity-80 transition-transform duration-700 group-hover:scale-125"></div>
                            <div class="absolute -bottom-20 -left-16 h-44 w-44 rounded-full bg-black/10 blur-3xl opacity-50 dark:bg-white/5"></div>
                            
                            <div class="relative flex h-full flex-col items-center justify-between gap-3">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-2xl bg-white/10 text-white shadow-inner ring-1 ring-white/25 backdrop-blur transition-all duration-500 group-hover:rotate-6 group-hover:scale-110 {{ $action['icon_container'] }}">
                                        <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}" />
                                        </svg>
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-widest ring-1 backdrop-blur transition-all duration-300 {{ $action['badge'] }}">
                                        Ir ahora
                                    </span>
                                </div>

                                <div class="flex flex-col items-center text-center">
                                    <h2 class="text-xs leading-snug tracking-tight drop-shadow-sm sm:text-sm font-black {{ $action['text_title'] }}">
                                        {{ $action['label'] }}
                                    </h2>
                                    <p class="mt-1.5 max-w-sm text-[11px] leading-relaxed sm:text-xs {{ $action['text_desc'] }}">
                                        {{ $action['description'] }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </section>

                @can('reportes.ver')
                    <section class="rounded-[1.5rem] border border-slate-200/70 bg-white/75 p-3 shadow-sm backdrop-blur-sm dark:border-slate-800/70 dark:bg-slate-950/50 sm:p-4">
                        <livewire:escritorio.kpi-cards lazy />
                    </section>

                    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <livewire:escritorio.ventas-por-dia-chart lazy />
                        <livewire:escritorio.ventas-mensuales-chart lazy />
                    </section>

                    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <livewire:escritorio.metodos-pago-chart lazy />
                        <livewire:escritorio.ganancias-chart lazy />
                        <livewire:escritorio.top-productos-chart lazy />
                    </section>
                @endcan

                <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <livewire:escritorio.alertas-inteligentes lazy />
                    <livewire:escritorio.actividad-reciente lazy />
                </section>

                <section>
                    <livewire:escritorio.top-productos lazy />
                </section>
            </main>
        </div>
    @else
        <div class="rounded-[2rem] border border-slate-200 bg-white p-8 text-center shadow-sm dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-900 dark:text-slate-500">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <p class="mt-4 text-sm font-black text-slate-700 dark:text-slate-200">Bienvenido al sistema</p>
            <p class="mt-1 text-xs font-semibold text-slate-400 dark:text-slate-500">Consulta con el administrador para acceder a mas funcionalidades.</p>
        </div>
    @endcanany
</x-filament-panels::page>
