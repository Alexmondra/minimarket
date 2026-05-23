<x-filament-panels::page>
    @php
        $compra = $this->getCompra();
        $detalles = $compra->detalle ?? collect();

        $totalItems = count($detalles);
        $totalUnidades = $detalles->sum(fn($d) => (int) ($d->lote?->lotePresentaciones?->sum('stock') ?? 0));
        $totalCalculado = $detalles->sum(fn($d) => (float) ($d->precio_compra ?? 0));
        $totalFactura = (float) ($compra->costo_total_factura ?? 0);
        $diferencia = round($totalCalculado - $totalFactura, 2);

        $estadoLabel = $this->getEstadoLabel();
        $estadoColor = $this->getEstadoColor();
        $estadoIcon = $this->getEstadoIcon();

        // ─── Clases adaptables al tema (claro/oscuro) ───
        $bgCard = 'bg-white dark:bg-gray-900';
        $bgPanel = 'bg-gray-50 dark:bg-gray-950';
        $borderCard = 'border-gray-200 dark:border-gray-700';
        $textMuted = 'text-gray-500 dark:text-gray-400';
        $textPrimary = 'text-gray-900 dark:text-white';
        $bgIcon = 'bg-gray-100 dark:bg-gray-800';
        $hoverRow = 'hover:bg-gray-50 dark:hover:bg-white/[0.02]';
        $theadBg = 'bg-gray-100 dark:bg-white/5';
        $theadText = 'text-gray-600 dark:text-gray-500';
    @endphp

    <div class="space-y-6 min-h-screen pb-10 {{ $bgPanel }}">

        {{-- ═══════════════════════════════════════════════════════════════
             1. HEADER CON ESTADO Y ACCIONES
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                {{-- Badge de estado con más color --}}
                @php
                    $estadoColors = [
                        'success' => ['bg' => 'bg-emerald-100 dark:bg-emerald-500/20', 'text' => 'text-emerald-700 dark:text-emerald-400', 'border' => 'border-emerald-300 dark:border-emerald-500/30', 'icon' => 'text-emerald-500'],
                        'danger' => ['bg' => 'bg-red-100 dark:bg-red-500/20', 'text' => 'text-red-700 dark:text-red-400', 'border' => 'border-red-300 dark:border-red-500/30', 'icon' => 'text-red-500'],
                        'warning' => ['bg' => 'bg-amber-100 dark:bg-amber-500/20', 'text' => 'text-amber-700 dark:text-amber-400', 'border' => 'border-amber-300 dark:border-amber-500/30', 'icon' => 'text-amber-500'],
                        'gray' => ['bg' => 'bg-gray-100 dark:bg-gray-500/20', 'text' => 'text-gray-700 dark:text-gray-400', 'border' => 'border-gray-300 dark:border-gray-500/30', 'icon' => 'text-gray-500'],
                    ];
                    $ec = $estadoColors[$estadoColor] ?? $estadoColors['gray'];
                @endphp
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl {{ $ec['bg'] }} border {{ $ec['border'] }} shadow-sm">
                    <x-filament::icon :name="$estadoIcon" class="w-5 h-5 {{ $ec['icon'] }}" />
                    <span class="text-sm font-black uppercase tracking-wider {{ $ec['text'] }}">{{ $estadoLabel }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════
             2. GRID SUPERIOR DE DOS PANELES
             ═══════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- PANEL IZQUIERDO: DATOS DE LA COMPRA --}}
            <div class="lg:col-span-7 rounded-2xl {{ $bgCard }} border {{ $borderCard }} overflow-hidden shadow-sm">
                <div class="p-4 border-b {{ $borderCard }} flex items-center gap-3 bg-gradient-to-r from-blue-50/50 to-transparent dark:from-blue-900/10 dark:to-transparent">
                    <div class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-500/10 border border-blue-300 dark:border-blue-500/20 text-blue-600 dark:text-blue-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </div>
                    <h2 class="font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest text-xs">Datos de la Compra</h2>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Columna izquierda --}}
                    <div class="space-y-6">
                        {{-- Sucursal --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-xl {{ $bgIcon }} text-blue-600 dark:text-blue-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                            </div>
                            <div>
                                <p class="{{ $textMuted }} text-xs uppercase font-medium mb-1">Sucursal</p>
                                <p class="text-sm font-bold {{ $textPrimary }} leading-tight">{{ $compra->sucursal->nombre_sucursal ?? $compra->sucursal->nombre ?? 'N/A' }}</p>
                            </div>
                        </div>
                        {{-- Proveedor --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-xl {{ $bgIcon }} text-indigo-600 dark:text-indigo-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="{{ $textMuted }} text-xs uppercase font-medium mb-1">Proveedor</p>
                                <p class="text-sm font-bold {{ $textPrimary }} leading-tight">{{ $compra->proveedor->nombre ?? 'N/A' }}</p>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-bold mt-0.5">{{ $compra->proveedor->ruc ?? '' }}</p>
                            </div>
                        </div>
                        {{-- Registrado por --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-xl {{ $bgIcon }} text-violet-600 dark:text-violet-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="{{ $textMuted }} text-xs uppercase font-medium mb-1">Registrado por</p>
                                <p class="text-sm font-bold {{ $textPrimary }} leading-tight">{{ $compra->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    {{-- Columna derecha --}}
                    <div class="space-y-6">
                        {{-- Tipo comprobante --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-xl {{ $bgIcon }} text-amber-600 dark:text-amber-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="{{ $textMuted }} text-xs uppercase font-medium mb-1">Tipo comprobante</p>
                                <p class="text-sm font-bold {{ $textPrimary }} leading-tight">{{ ucfirst($compra->tipo_comprobante ?? 'N/A') }}</p>
                            </div>
                        </div>
                        {{-- N° comprobante --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-xl {{ $bgIcon }} text-cyan-600 dark:text-cyan-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
                                </svg>
                            </div>
                            <div>
                                <p class="{{ $textMuted }} text-xs uppercase font-medium mb-1">N° comprobante</p>
                                <p class="text-sm font-bold {{ $textPrimary }} leading-tight">{{ $compra->numero_factura_proveedor ?? 'N/A' }}</p>
                            </div>
                        </div>
                        {{-- Fecha de recepción --}}
                        <div class="flex items-start gap-4">
                            <div class="p-2 rounded-xl {{ $bgIcon }} text-rose-600 dark:text-rose-500">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <div>
                                <p class="{{ $textMuted }} text-xs uppercase font-medium mb-1">Fecha de recepción</p>
                                <p class="text-sm font-bold {{ $textPrimary }} leading-tight">{{ $compra->fecha_recepcion?->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL DERECHO: COMPROBANTE Y ESTADO --}}
            <div class="lg:col-span-5 rounded-2xl {{ $bgCard }} border {{ $borderCard }} overflow-hidden shadow-sm">
                <div class="p-4 border-b {{ $borderCard }} flex items-center gap-3 bg-gradient-to-r from-emerald-50/50 to-transparent dark:from-emerald-900/10 dark:to-transparent">
                    <div class="p-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-500/10 border border-emerald-300 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </div>
                    <h2 class="font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest text-xs">Comprobante</h2>
                </div>
                <div class="p-6 space-y-5">
                    @php
                        $ext = $this->getComprobanteExtension();
                        $url = $this->getComprobanteUrl();
                    @endphp
                    @if($url)
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-200 dark:border-emerald-800/30">
                            <div class="p-3 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-500">
                                @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.41a2.25 2.25 0 0 1 3.182 0l2.909 2.91m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                @else
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold {{ $textPrimary }} truncate">Comprobante adjunto</p>
                                <p class="text-xs {{ $textMuted }} uppercase mt-0.5">{{ strtoupper($ext) }}</p>
                            </div>
                            <a href="{{ $url }}" target="_blank" class="shrink-0 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-600/20 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                Ver
                            </a>
                        </div>
                    @else
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-dashed {{ $borderCard }}">
                            <div class="p-3 rounded-xl {{ $bgIcon }} text-gray-400">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold {{ $textMuted }}">Sin comprobante adjunto</p>
                                <p class="text-xs {{ $textMuted }} opacity-60 mt-0.5">No se ha subido ningún archivo</p>
                            </div>
                        </div>
                    @endif

                    {{-- Resumen rápido --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-200 dark:border-blue-800/20">
                            <p class="text-[10px] font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">Items</p>
                            <p class="text-lg font-black {{ $textPrimary }}">{{ $totalItems }}</p>
                        </div>
                        <div class="p-3 rounded-xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/10 dark:to-orange-900/10 border border-amber-200 dark:border-amber-800/20">
                            <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400">Unidades</p>
                            <p class="text-lg font-black {{ $textPrimary }}">{{ $totalUnidades }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════
                 3. SECCIÓN DE OBSERVACIONES
                 ═══════════════════════════════════════════════════════════════ --}}
            @if($compra->observaciones)
            <div class="lg:col-span-12 rounded-xl {{ $bgCard }} border {{ $borderCard }} p-4 flex items-center gap-4 shadow-sm">
                <div class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09.457-.133.871-.133.871C4.956 19.235 4.5 18.448 4.5 17.591V6.75a2.25 2.25 0 0 1 2.25-2.25h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-2.572l-1.716 1.717a.75.75 0 0 1-1.06 0L10.572 19.5H6.75Z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400 mb-0.5">Observaciones</h4>
                    <p class="text-sm italic {{ $textMuted }}">{{ $compra->observaciones }}</p>
                </div>
            </div>
            @endif

            {{-- ═══════════════════════════════════════════════════════════════
                 4. TABLA DE DETALLES ESTILIZADA
                 ═══════════════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-12 rounded-2xl {{ $bgCard }} border {{ $borderCard }} overflow-hidden shadow-sm">
                <div class="p-5 border-b {{ $borderCard }} flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-gray-50/50 to-transparent dark:from-white/[0.02] dark:to-transparent">
                    <div class="flex items-center gap-4">
                        <h2 class="font-black text-lg tracking-tight {{ $textPrimary }}">Detalle de Items / Lotes Recibidos</h2>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 bg-blue-100 dark:bg-blue-600/20 text-blue-700 dark:text-blue-400 rounded-full text-[10px] font-black uppercase border border-blue-300 dark:border-blue-600/30">{{ $totalItems }} item</span>
                            <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-600/20 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-black uppercase border border-emerald-300 dark:border-emerald-600/30">{{ $totalUnidades }} unds.</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="{{ $theadBg }} text-[10px] font-black uppercase tracking-widest {{ $theadText }}">
                                <th class="px-6 py-4">#</th>
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4">Código Lote</th>
                                <th class="px-6 py-4">Presentaciones</th>
                                <th class="px-6 py-4 text-center">Stock</th>
                                <th class="px-6 py-4 text-orange-600 dark:text-orange-500">Total pagado</th>
                                <th class="px-6 py-4">Vencimiento</th>
                                <th class="px-6 py-4">Ubicación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y {{ $borderCard }}">
                            @foreach($detalles as $index => $det)
                            @php
                                $presentaciones = $det->lote?->lotePresentaciones ?? collect();
                                $stockLote = $presentaciones->sum('stock');
                            @endphp
                            <tr class="{{ $hoverRow }} transition-colors">
                                <td class="px-6 py-4 text-sm font-bold {{ $textMuted }}">{{ $index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl {{ $bgIcon }} border {{ $borderCard }} flex items-center justify-center {{ $textMuted }}">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black {{ $textPrimary }} leading-none mb-1">{{ $det->lote?->producto_nombre ?? 'Producto' }}</p>
                                            <p class="text-[10px] font-bold {{ $textMuted }} uppercase tracking-tighter">{{ $presentaciones->count() }} presentación(es)</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-blue-600 dark:text-blue-500 font-black text-sm">{{ $det->lote?->codigo_lote ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @foreach($presentaciones as $lp)
                                            <div class="text-xs {{ $textMuted }}">
                                                <span class="font-bold {{ $textPrimary }}">{{ $lp->productoPresentacion?->tipo_presentacion ?? 'Presentación' }}</span>
                                                <span>x {{ $lp->productoPresentacion?->cantidad ?? 1 }} {{ $lp->productoPresentacion?->unidadMedida?->abreviatura ?? 'und' }}</span>
                                                <span class="font-bold text-emerald-600 dark:text-emerald-500">{{ number_format($lp->stock, 0) }} recib.</span>
                                                @if($lp->precio_oferta !== null)
                                                    <span class="font-bold text-primary-600">S/ {{ number_format($lp->precio_oferta, 2) }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-base font-black {{ $textPrimary }}">{{ $stockLote }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-base font-black text-orange-600 dark:text-orange-500">S/ {{ number_format($det->precio_compra, 2) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-black text-cyan-600 dark:text-cyan-500 border border-cyan-300 dark:border-cyan-500/20 bg-cyan-50 dark:bg-cyan-500/5 px-2 py-1 rounded-md">
                                        {{ $det->lote?->fecha_vencimiento ? (is_string($det->lote->fecha_vencimiento) ? \Carbon\Carbon::parse($det->lote->fecha_vencimiento)->format('d/m/Y') : $det->lote->fecha_vencimiento->format('d/m/Y')) : 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right sm:text-left">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-emerald-100 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-500 border border-emerald-300 dark:border-emerald-500/20 text-[10px] font-black uppercase">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                        {{ $det->lote?->ubicacion ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════
                 5. FOOTER DE TOTALES (DASHBOARD) - 4 TARJETAS
                 ═══════════════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Tarjeta 1: Total Items --}}
                <div class="{{ $bgCard }} border {{ $borderCard }} rounded-2xl p-4 flex items-center gap-4 relative overflow-hidden group shadow-sm">
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black {{ $textMuted }} uppercase tracking-widest mb-1">Total Items</p>
                        <p class="text-2xl font-black italic {{ $textPrimary }}">{{ $totalItems }} items</p>
                    </div>
                </div>

                {{-- Tarjeta 2: Total Factura --}}
                <div class="{{ $bgCard }} border {{ $borderCard }} rounded-2xl p-4 flex items-center gap-4 relative overflow-hidden group shadow-sm">
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div class="p-3 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black {{ $textMuted }} uppercase tracking-widest mb-1">Total Factura</p>
                        <p class="text-2xl font-black italic {{ $textPrimary }}">S/ {{ number_format($totalFactura, 2) }}</p>
                    </div>
                </div>

                {{-- Tarjeta 3: Diferencia --}}
                <div class="{{ $bgCard }} border {{ $borderCard }} rounded-2xl p-4 flex items-center gap-4 relative overflow-hidden group shadow-sm">
                    <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <div class="p-3 rounded-xl bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black {{ $textMuted }} uppercase tracking-widest mb-1">Diferencia</p>
                        <p class="text-2xl font-black italic text-red-600 dark:text-red-500">{{ $diferencia >= 0 ? '+' : '-' }} S/ {{ number_format(abs($diferencia), 2) }}</p>
                    </div>
                </div>

                {{-- Tarjeta 4: Total Final con gradiente --}}
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 border border-emerald-500 rounded-2xl p-4 flex items-center justify-between group cursor-pointer hover:from-emerald-700 hover:to-emerald-800 transition-all shadow-xl shadow-emerald-600/20">
                    <div>
                        <p class="text-[10px] font-black text-emerald-200 uppercase tracking-widest mb-1">TOTAL FACTURA</p>
                        <p class="text-2xl font-black italic text-white leading-tight">S/ {{ number_format($totalFactura, 2) }}</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-filament-panels::page>
