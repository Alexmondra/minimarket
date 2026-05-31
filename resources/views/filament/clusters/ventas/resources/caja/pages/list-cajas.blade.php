<x-filament-panels::page>
    @php
        $activeCaja = $this->activeCaja;
    @endphp

    {{-- ============ SLICK SAAS STATUS BANNER (Linear/shadcn style) ============ --}}
    <div 
        @if($activeCaja)
            wire:click="mountTableAction('verDetalles', {{ $activeCaja->id }})"
        @else
            wire:click="mountAction('abrirCaja')"
        @endif
        class="relative overflow-hidden mb-6 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-900/60 backdrop-blur-md shadow-sm transition-all duration-300 cursor-pointer hover:border-slate-300 dark:hover:border-slate-700 hover:shadow-md hover:-translate-y-0.5 group"
    >
        {{-- Decorative grid background patterns (Linear style) --}}
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:14px_24px] pointer-events-none opacity-50 dark:opacity-30"></div>
        
        <div class="relative z-10 p-4 sm:p-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Left: Info section with Pulse Indicator --}}
                <div class="flex items-center gap-4">
                    {{-- Status indicator dot container --}}
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border transition-all duration-300
                        @if($activeCaja)
                            bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400
                        @else
                            bg-slate-100 dark:bg-slate-800/50 border-slate-200 dark:border-slate-700/60 text-slate-400 dark:text-slate-500
                        @endif
                    ">
                        @if($activeCaja)
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                        @else
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-400 dark:bg-slate-600"></span>
                        @endif
                    </div>

                    {{-- Header status content --}}
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold tracking-tight text-slate-800 dark:text-slate-200">
                                @if($activeCaja)
                                    Caja Abierta &mdash; {{ $activeCaja->sucursal?->nombre_sucursal }}
                                @else
                                    Sin caja activa
                                @endif
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            @if($activeCaja)
                                Abierta por <strong class="text-slate-700 dark:text-slate-350 font-semibold">{{ $activeCaja->user?->name }}</strong>
                                el <span class="font-mono bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-[11px]">{{ $activeCaja->fecha_apertura?->format('d/m/Y H:i') }}</span>
                            @else
                                Abre una sesión de caja para comenzar a vender en tu sucursal.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Right: Actions with modern micro-animations --}}
                <div class="flex items-center gap-2 shrink-0 w-full md:w-auto">
                    @if($activeCaja)
                        {{-- Ver Detalle button --}}
                        <button
                            type="button"
                            wire:click.stop="mountTableAction('verDetalles', {{ $activeCaja->id }})"
                            class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-750 hover:bg-slate-50 dark:hover:bg-slate-850 rounded-xl shadow-xs transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            <span>Ver Detalle</span>
                        </button>

                        {{-- Cerrar Caja button --}}
                        <button
                            type="button"
                            wire:click.stop="mountAction('cerrarCaja')"
                            class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 dark:bg-rose-750 dark:hover:bg-rose-650 rounded-xl shadow-xs transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                            <span>Cerrar Caja</span>
                        </button>
                    @else
                        {{-- Abrir Caja button --}}
                        <button
                            type="button"
                            wire:click.stop="mountAction('abrirCaja')"
                            class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-700 dark:hover:bg-emerald-650 rounded-xl shadow-xs transition-all duration-200 hover:-translate-y-0.5 active:translate-y-0 cursor-pointer"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                            <span>Abrir Nueva Caja</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ============ MAIN TABLE ============ --}}
    {{ $this->table }}
</x-filament-panels::page>
