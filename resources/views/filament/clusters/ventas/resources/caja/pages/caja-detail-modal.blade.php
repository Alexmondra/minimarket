<div class="space-y-6 py-2">
    @php
        $aperturaObs = $caja->getObservacionApertura();
        $cierreObs = $caja->getObservacionCierre();
        $isOpen = $caja->estado;
    @endphp

    {{-- ============ STATUS HEADER BANNER ============ --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/85 bg-white dark:bg-slate-900 shadow-xs">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:10px_10px] pointer-events-none opacity-40"></div>
        <div class="relative z-10 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                {{-- Left: Session Info --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Status Badge --}}
                        @if($isOpen)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Abierta
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60 uppercase tracking-wider">
                                <span class="h-2 w-2 rounded-full bg-slate-400 dark:bg-slate-500"></span>
                                Cerrada
                            </span>
                        @endif

                        {{-- Sucursal Badge --}}
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-350 border border-slate-200 dark:border-slate-700 uppercase tracking-wider">
                            <svg class="h-3 w-3 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                            </svg>
                            {{ $caja->sucursal?->nombre_sucursal }}
                        </span>
                    </div>

                    <div>
                        <h2 class="text-base font-bold tracking-tight text-slate-900 dark:text-white">
                            Sesión de Caja &mdash; #{{ $caja->id }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                            <svg class="h-3.5 w-3.5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                            Cajero: <strong class="text-slate-700 dark:text-slate-300 font-semibold">{{ $caja->user?->name }}</strong>
                        </p>
                    </div>
                </div>

                {{-- Right: Timeline Dates --}}
                <div class="flex flex-col gap-1.5 text-left sm:text-right shrink-0">
                    <div class="flex items-center gap-2 sm:justify-end">
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Apertura</span>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono bg-slate-50 dark:bg-slate-800/80 px-2 py-0.5 border border-slate-200/60 dark:border-slate-700/50 rounded-md shadow-xs">{{ $caja->fecha_apertura?->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($caja->fecha_cierre)
                        <div class="flex items-center gap-2 sm:justify-end">
                            <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Cierre</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 font-mono bg-slate-50 dark:bg-slate-800/80 px-2 py-0.5 border border-slate-200/60 dark:border-slate-700/50 rounded-md shadow-xs">{{ $caja->fecha_cierre?->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============ METRICS CARDS (Linear SaaS Grid) ============ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Saldo Inicial --}}
        <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all duration-300">
            <div class="flex items-center gap-2 mb-2.5">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/15">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Saldo Inicial</span>
            </div>
            <p class="text-lg font-bold text-slate-950 dark:text-white font-mono tracking-tight">S/ {{ number_format($caja->saldo_inicial, 2) }}</p>
        </div>

        {{-- Ventas Netas --}}
        <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all duration-300">
            <div class="flex items-center gap-2 mb-2.5">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/15">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Ventas Netas</span>
            </div>
            <p class="text-lg font-bold text-slate-950 dark:text-white font-mono tracking-tight">S/ {{ number_format($totalVentas, 2) }}</p>
        </div>

        {{-- Saldo Esperado --}}
        <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all duration-300">
            <div class="flex items-center gap-2 mb-2.5">
                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/15">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3M9 17h6M4 9h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2v-8a2 2 0 012-2zm2-5V4a2 2 0 012-2h8a2 2 0 012 2v2M8 12v1m4-1v1m4-1v1"/>
                    </svg>
                </div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Saldo Esperado</span>
            </div>
            <p class="text-lg font-bold text-slate-950 dark:text-white font-mono tracking-tight">S/ {{ number_format($caja->saldo_teorico ?? (float)($caja->saldo_inicial + $totalVentas), 2) }}</p>
        </div>

        {{-- Diferencia o Saldo Real --}}
        @if(!$isOpen)
            @php
                $diff = (float)$caja->diferencia;
                if ($diff === 0.0) {
                    $diffText = 'S/ 0.00';
                    $diffColorClass = 'text-emerald-600 dark:text-emerald-400';
                    $diffIconColor = 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/15';
                    $diffIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>';
                    $diffLabel = 'Cuadrada';
                } elseif ($diff > 0) {
                    $diffText = '+S/ ' . number_format($diff, 2);
                    $diffColorClass = 'text-amber-600 dark:text-amber-400';
                    $diffIconColor = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/15';
                    $diffIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>';
                    $diffLabel = 'Sobrante';
                } else {
                    $diffText = '-S/ ' . number_format(abs($diff), 2);
                    $diffColorClass = 'text-rose-600 dark:text-rose-400';
                    $diffIconColor = 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/15';
                    $diffIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>';
                    $diffLabel = 'Faltante';
                }
            @endphp
            <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xs hover:shadow-xs transition-all duration-300">
                <div class="flex items-center gap-2 mb-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg border {{ $diffIconColor }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">{!! $diffIcon !!}</svg>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Diferencia</span>
                </div>
                <p class="text-lg font-bold font-mono tracking-tight {{ $diffColorClass }}">{{ $diffText }}</p>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-1 flex justify-between">
                    <span>Físico: S/ {{ number_format($caja->saldo_real, 2) }}</span>
                    <span class="font-semibold uppercase tracking-wider text-[8px]">{{ $diffLabel }}</span>
                </div>
            </div>
        @else
            <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800/80 border-dashed bg-slate-50/50 dark:bg-slate-950/20 shadow-2xs transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-center gap-2 mb-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-300/40 dark:border-slate-700/50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Saldo Real</span>
                </div>
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 italic">Sesión en curso</span>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">Registra el cierre para verificar</p>
                </div>
            </div>
        @endif
    </div>

    {{-- ============ PAYMENT BREAKDOWN ============ --}}
    <div class="space-y-3.5">
        <div class="flex items-center gap-2">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                </svg>
                Desglose de Ventas por Medio de Pago
            </h3>
        </div>

        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            @php
                $payments = [
                    ['name' => 'Efectivo', 'value' => $efectivo, 'color' => 'emerald', 'gradient' => 'border-t-2 border-t-emerald-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>', 'bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/15'],
                    ['name' => 'Yape', 'value' => $yape, 'color' => 'purple', 'gradient' => 'border-t-2 border-t-purple-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>', 'bg' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/15'],
                    ['name' => 'Plin', 'value' => $plin, 'color' => 'cyan', 'gradient' => 'border-t-2 border-t-cyan-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>', 'bg' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/15'],
                    ['name' => 'Transf.', 'value' => $transferencia, 'color' => 'blue', 'gradient' => 'border-t-2 border-t-blue-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>', 'bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/15'],
                    ['name' => 'Tarjeta', 'value' => $tarjeta, 'color' => 'orange', 'gradient' => 'border-t-2 border-t-orange-500', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>', 'bg' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/15'],
                    ['name' => 'Otro', 'value' => $otro, 'color' => 'slate', 'gradient' => 'border-t-2 border-t-slate-400', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>', 'bg' => 'bg-slate-200/50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-300/40 dark:border-slate-700/50'],
                ];
            @endphp

            @foreach($payments as $p)
                <div class="relative overflow-hidden p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900/80 text-center shadow-3xs hover:shadow-2xs transition-all duration-300 hover:-translate-y-0.5 {{ $p['gradient'] }}">
                    <div class="mx-auto mt-0.5 flex h-7.5 w-7.5 items-center justify-center rounded-lg border {{ $p['bg'] }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">{!! $p['icon'] !!}</svg>
                    </div>
                    <p class="text-[9px] font-bold text-slate-400 dark:text-slate-500 mt-2 uppercase tracking-wider">{{ $p['name'] }}</p>
                    <p class="text-xs font-black text-slate-800 dark:text-slate-200 mt-0.5 font-mono tracking-tight">S/ {{ number_format($p['value'], 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============ OBSERVATIONS ============ --}}
    <div class="space-y-3.5">
        <div class="flex items-center gap-2">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="h-4 w-4 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>
                </svg>
                Observaciones y Notas
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Apertura --}}
            <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-3xs">
                <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500 rounded-l-xl"></div>
                <div class="flex items-center gap-2 mb-3 pl-1">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Apertura</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-950/40 rounded-lg p-3 border border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed italic">
                        @if($aperturaObs !== '')
                            "{{ $aperturaObs }}"
                        @else
                            <span class="text-slate-400 dark:text-slate-500 not-italic">Sin observaciones registradas al abrir la caja.</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Cierre --}}
            <div class="relative overflow-hidden p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-3xs">
                <div class="absolute left-0 top-0 bottom-0 w-1 @if($isOpen) bg-slate-300 dark:bg-slate-600 @else bg-rose-500 @endif rounded-l-xl"></div>
                <div class="flex items-center gap-2 mb-3 pl-1">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Cierre</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-950/40 rounded-lg p-3 border border-slate-200/60 dark:border-slate-800/80">
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed italic">
                        @if($isOpen)
                            <span class="text-slate-400 dark:text-slate-500 not-italic">Esta caja sigue activa. La nota de cierre se registrará al finalizar la sesión.</span>
                        @elseif($cierreObs !== '')
                            "{{ $cierreObs }}"
                        @else
                            <span class="text-slate-400 dark:text-slate-500 not-italic">Sin observaciones registradas al cerrar la caja.</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============ CLOSE ACTION BANNER (if open and owner) ============ --}}
    @if($isOpen && $caja->user_id === auth()->id() && auth()->user()->can('cajas.cerrar'))
        <div class="pt-1">
            <div class="relative overflow-hidden rounded-xl border border-rose-500/10 dark:border-rose-900/35 bg-rose-500/5 dark:bg-rose-950/5 p-4 sm:p-5">
                <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-600/10 text-rose-600 dark:text-rose-400 border border-rose-600/15">
                            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-rose-900 dark:text-rose-350">¿Listo para finalizar operaciones?</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Realiza el arqueo de caja física y registra el cierre antes de salir.</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        x-on:click="close()"
                        wire:click="mountAction('cerrarCaja')"
                        class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 dark:bg-rose-700 dark:hover:bg-rose-600 rounded-xl shadow-xs transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                        </svg>
                        <span>Cerrar esta Caja</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
