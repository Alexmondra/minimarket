<div class="space-y-6 py-2">
    @php
        $aperturaObs = $caja->getObservacionApertura();
        $cierreObs = $caja->getObservacionCierre();
    @endphp

    <!-- Top Metadata -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-200 dark:border-slate-800">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sucursal</span>
                <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wide bg-indigo-500/10 px-2 py-0.5 rounded-md border border-indigo-500/20">
                    {{ $caja->sucursal?->nombre_sucursal }}
                </span>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Cajero: <strong class="text-slate-800 dark:text-slate-200 font-bold">{{ $caja->user?->name }}</strong>
            </p>
        </div>
        
        <div>
            @if($caja->estado)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider animate-pulse">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Abierta
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20 uppercase tracking-wider">
                    <span class="h-2 w-2 rounded-full bg-slate-500 dark:bg-slate-400"></span>
                    Cerrada
                </span>
            @endif
        </div>
    </div>

    <!-- Metrics Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Saldo Inicial -->
        <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/40 transition-all duration-300 hover:shadow-sm">
            <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Saldo Inicial</p>
            <p class="text-lg font-black text-blue-900 dark:text-blue-100 mt-1 font-mono">S/ {{ number_format($caja->saldo_inicial, 2) }}</p>
            <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold">Apertura: {{ $caja->fecha_apertura?->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Ventas Totales -->
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 transition-all duration-300 hover:shadow-sm">
            <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Ventas Netas</p>
            <p class="text-lg font-black text-emerald-900 dark:text-emerald-100 mt-1 font-mono">S/ {{ number_format($totalVentas, 2) }}</p>
            <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold">Solo ventas contadas</p>
        </div>

        <!-- Saldo Teórico -->
        <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900/40 transition-all duration-300 hover:shadow-sm">
            <p class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Saldo Esperado</p>
            <p class="text-lg font-black text-indigo-900 dark:text-indigo-100 mt-1 font-mono">S/ {{ number_format($caja->saldo_teorico ?? (float)($caja->saldo_inicial + $totalVentas), 2) }}</p>
            <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold">Inicial + Efectivo</p>
        </div>

        <!-- Saldo Real / Diferencia -->
        @if(!$caja->estado)
            @php
                $diff = (float)$caja->diferencia;
                $bgColor = $diff === 0.0 ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900/40' : ($diff > 0 ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-900/40' : 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900/40');
                $textColor = $diff === 0.0 ? 'text-emerald-700 dark:text-emerald-300' : ($diff > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-rose-700 dark:text-rose-300');
                $labelColor = $diff === 0.0 ? 'text-emerald-600 dark:text-emerald-400' : ($diff > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400');
            @endphp
            <div class="p-4 rounded-2xl border transition-all duration-300 hover:shadow-sm {{ $bgColor }}">
                <p class="text-[10px] font-bold {{ $labelColor }} uppercase tracking-wider">Diferencia</p>
                <p class="text-lg font-black {{ $textColor }} mt-1 font-mono">
                    {{ $diff > 0 ? '+' : '' }}S/ {{ number_format($diff, 2) }}
                </p>
                <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold">Real: S/ {{ number_format($caja->saldo_real, 2) }}</p>
            </div>
        @else
            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800/80 flex flex-col justify-between transition-all duration-300 hover:shadow-sm">
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo Real</p>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1 italic">Caja en curso...</span>
                <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold font-mono">Cerrar para registrar</p>
            </div>
        @endif
    </div>

    <!-- Payment Breakdown Section -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
            </svg>
            <span>Desglose de Ventas por Medio de Pago</span>
        </h3>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            <!-- Efectivo -->
            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/60 text-center shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-wide">Efectivo</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 font-mono">S/ {{ number_format($efectivo, 2) }}</p>
            </div>

            <!-- Yape -->
            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/60 text-center shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-wide">Yape</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 font-mono">S/ {{ number_format($yape, 2) }}</p>
            </div>

            <!-- Plin -->
            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/60 text-center shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 dark:bg-cyan-500/20 dark:text-cyan-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-wide">Plin</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 font-mono">S/ {{ number_format($plin, 2) }}</p>
            </div>

            <!-- Transferencia -->
            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/60 text-center shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5m16.5 0h-18" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-wide">Transf.</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 font-mono">S/ {{ number_format($transferencia, 2) }}</p>
            </div>

            <!-- Tarjeta -->
            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/60 text-center shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-slate-500/10 text-slate-600 dark:bg-slate-500/20 dark:text-slate-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-wide">Tarjeta</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 font-mono">S/ {{ number_format($tarjeta, 2) }}</p>
            </div>

            <!-- Otro -->
            <div class="p-3.5 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-200/80 dark:border-slate-800/60 text-center shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="mx-auto flex h-8 w-8 items-center justify-center rounded-xl bg-slate-500/10 text-slate-600 dark:bg-slate-500/20 dark:text-slate-400">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </div>
                <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 mt-2 uppercase tracking-wide">Otro</p>
                <p class="text-sm font-black text-slate-900 dark:text-slate-100 mt-0.5 font-mono">S/ {{ number_format($otro, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Observations Section -->
    <div class="space-y-3">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-2">
            <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
            </svg>
            <span>Observaciones y Comentarios</span>
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Apertura -->
            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/30 border border-slate-200/80 dark:border-slate-800/80 relative shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="absolute top-3 right-3 text-emerald-600 dark:text-emerald-400 font-bold uppercase text-[9px] tracking-wide bg-emerald-500/10 px-2 py-0.5 rounded-md border border-emerald-500/20">
                    Apertura
                </div>
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Comentarios al Iniciar</h4>
                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed mt-2.5 bg-white dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200/50 dark:border-slate-800/80 italic">
                    {{ $aperturaObs !== '' ? $aperturaObs : 'Sin observaciones registradas al abrir la caja.' }}
                </p>
            </div>

            <!-- Cierre -->
            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/30 border border-slate-200/80 dark:border-slate-800/80 relative shadow-sm transition-all duration-300 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700">
                <div class="absolute top-3 right-3 text-slate-500 dark:text-slate-400 font-bold uppercase text-[9px] tracking-wide bg-slate-500/10 px-2 py-0.5 rounded-md border border-slate-500/20">
                    Cierre
                </div>
                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Comentarios al Cerrar</h4>
                <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed mt-2.5 bg-white dark:bg-slate-950/60 p-3 rounded-xl border border-slate-200/50 dark:border-slate-800/80 italic">
                    {{ $cierreObs !== '' ? $cierreObs : 'Sin observaciones registradas al cerrar la caja.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Inline cerrar caja action button inside modal if open -->
    @if($caja->estado && $caja->user_id === auth()->id() && auth()->user()->can('cajas.cerrar'))
        <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800/80 flex justify-end">
            <button 
                type="button" 
                x-on:click="close()"
                wire:click="mountTableAction('cerrarCaja', {{ $caja->id }})"
                class="inline-flex items-center gap-2 px-5 py-3 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-600 dark:active:bg-rose-800 text-white font-extrabold rounded-2xl shadow-md shadow-rose-600/20 hover:shadow-lg hover:shadow-rose-600/35 dark:shadow-rose-950/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 text-xs focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 cursor-pointer"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
                <span>Cerrar esta Caja</span>
            </button>
        </div>
    @endif
</div>
