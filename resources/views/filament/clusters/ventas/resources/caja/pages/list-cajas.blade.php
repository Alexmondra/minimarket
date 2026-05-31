<x-filament-panels::page>
    @php
        $activeCaja = $this->activeCaja;
    @endphp

    <!-- Custom Premium Hero Header Panel -->
    <div class="relative overflow-hidden mb-6 p-6 rounded-3xl bg-white dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800/80 shadow-sm transition-all duration-300">
        <!-- Decorative blurred glow -->
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full blur-3xl opacity-20 @if($activeCaja) bg-emerald-500 @else bg-slate-500 @endif"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-5 w-full md:w-auto">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl shadow-sm transition duration-300 @if($activeCaja) bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-500/25 @else bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400 border border-slate-200/40 dark:border-slate-800 @endif">
                    <!-- Cash register icon -->
                    <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5M5.25 7.5h13.5m-12 9h10.5M5.25 15h13.5m-13.5-6h13.5m-13.5 3h13.5m-10.5 6h7.5M10.5 4.5V3a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v1.5M3.75 18.75v-15A2.25 2.25 0 0 1 6 1.5h12a2.25 2.25 0 0 1 2.25 2.25v15M3 18.75h18M6.75 15h.008v.008H6.75V15Zm0-3h.008v.008H6.75V12Zm0-3h.008v.008H6.75V9Zm3 6h.008v.008H9.75V15Zm0-3h.008v.008H9.75V12Zm0-3h.008v.008H9.75V9Zm3 6h.008v.008h-.008V15Zm0-3h.008v.008h-.008V12Zm0-3h.008v.008h-.008V9Zm3 6h.008v.008h-.008V15Zm0-3h.008v.008h-.008V12Zm0-3h.008v.008h-.008V9Z" />
                    </svg>
                </div>
                
                <div class="space-y-1">
                    @if($activeCaja)
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 uppercase tracking-wider animate-pulse">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Caja Activa
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                S/ {{ number_format($activeCaja->saldo_inicial, 2) }} inicial
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span>{{ $activeCaja->sucursal?->nombre_sucursal }}</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-normal">
                            Abierta por <strong class="text-slate-700 dark:text-slate-300">{{ $activeCaja->user?->name }}</strong> el <span class="font-semibold">{{ $activeCaja->fecha_apertura?->format('d/m/Y H:i') }}</span>
                        </p>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-500/10 text-slate-500 dark:text-slate-400 border border-slate-500/20 uppercase tracking-wider">
                                Sin Caja Activa
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-slate-800 dark:text-white">
                            No tienes una caja abierta
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Abre una sesión de caja en tu sucursal para poder registrar ventas.
                        </p>
                    @endif
                </div>
            </div>
            
            <div class="w-full md:w-auto flex justify-end shrink-0">
                @if($activeCaja)
                    <button 
                        type="button" 
                        wire:click="mountAction('cerrarCaja')"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-rose-600 hover:bg-rose-500 active:bg-rose-700 dark:bg-rose-700 dark:hover:bg-rose-600 dark:active:bg-rose-800 text-white font-extrabold rounded-2xl shadow-lg shadow-rose-600/20 hover:shadow-xl hover:shadow-rose-600/35 dark:shadow-rose-950/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 cursor-pointer animate-fade-in"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span>Cerrar Caja Activa</span>
                    </button>
                @else
                    <button 
                        type="button" 
                        wire:click="mountAction('abrirCaja')"
                        class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-600 dark:active:bg-emerald-800 text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-600/20 hover:shadow-xl hover:shadow-emerald-600/35 dark:shadow-emerald-950/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer animate-fade-in"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <span>Abrir Nueva Caja</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Main List Table -->
    {{ $this->table }}
</x-filament-panels::page>
