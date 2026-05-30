<x-filament-panels::page>
    <div class="mx-auto w-full max-w-lg mt-6">
        @if ($cajaAbiertaEnOtraSucursal)
            <!-- Bloqueo por caja abierta en otra sucursal -->
            <div class="rounded-2xl border border-rose-200 bg-rose-50/50 p-6 dark:border-rose-900/30 dark:bg-rose-950/10 text-center shadow-xl space-y-5">
                <!-- Icono de Bloqueo / Advertencia -->
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-450 border border-rose-200 dark:border-rose-800">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                    </svg>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-lg font-black text-rose-700 dark:text-rose-400">⚠️ Sesión de Caja Activa</h3>
                    <p class="text-sm text-slate-650 dark:text-slate-300 leading-relaxed">
                        No puedes abrir una nueva caja porque ya tienes una sesión abierta en la sucursal:
                        <br>
                        <span class="inline-block mt-2 px-3 py-1 font-black text-xs uppercase bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300 rounded-lg">
                            {{ $cajaAbiertaEnOtraSucursal->sucursal?->nombre_sucursal ?? 'Otra Sucursal' }}
                        </span>
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 pt-1">
                        Abierta el: {{ $cajaAbiertaEnOtraSucursal->fecha_apertura->format('d/m/Y H:i') }}
                    </p>
                </div>

                <div class="pt-2">
                    <a 
                        href="/admin/ventas/cajas" 
                        class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold py-3 text-sm transition dark:bg-slate-800 dark:hover:bg-slate-750"
                    >
                        Ver mis Cajas para Cerrar
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
        @else
            <!-- Formulario de Apertura de Caja Inline -->
            <div class="rounded-3xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 p-8 shadow-[0_0_50px_-12px_rgba(16,185,129,0.15)]">
                <form wire:submit.prevent="abrirCajaManual" class="space-y-6">
                    <!-- Header Icon y Título -->
                    <div class="text-center space-y-3">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/25 dark:text-emerald-400 shadow-inner">
                            <svg class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3M9 17h6M4 9h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2v-8a2 2 0 012-2zm2-5V4a2 2 0 012-2h8a2 2 0 012 2v2M8 12v1m4-1v1m4-1v1" />
                            </svg>
                        </div>

                        <div class="space-y-1">
                            <h2 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-wider">Apertura de Caja</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Establece el saldo de apertura para la sucursal activa:
                                <span class="inline-block mt-1 font-extrabold text-slate-800 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-[10px]">
                                    {{ $this->sucursalNombre }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Saldo Inicial Centrado y Grande -->
                    <div class="space-y-3">
                        <label class="block text-center text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo Inicial de Apertura</label>
                        <div class="flex items-center justify-center gap-2 bg-slate-50/50 dark:bg-slate-950/40 border-2 border-slate-200 dark:border-slate-800/80 rounded-2xl py-3 px-4 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 transition shadow-inner max-w-[240px] mx-auto">
                            <span class="text-3xl font-black text-slate-450 dark:text-slate-550 select-none">S/</span>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0"
                                id="saldoInicial" 
                                wire:model="saldoInicial"
                                class="w-full text-left text-3xl font-black tracking-tight text-emerald-600 dark:text-emerald-450 bg-transparent border-0 focus:ring-0 focus:outline-none p-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" 
                                placeholder="0.00"
                                required
                                autofocus
                            >
                        </div>
                        @error('saldoInicial')
                            <span class="text-rose-500 text-[10px] block mt-0.5 font-normal text-center">{{ $message }}</span>
                        @enderror

                        <!-- Botones de Presets Rápidos -->
                        <div class="flex justify-center gap-2 max-w-[280px] mx-auto">
                            <button type="button" wire:click="$set('saldoInicial', 0)" class="flex-1 py-1.5 px-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[10px] font-extrabold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition">
                                Sin Sencillo
                            </button>
                            <button type="button" wire:click="$set('saldoInicial', 50)" class="flex-1 py-1.5 px-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[10px] font-extrabold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition">
                                S/ 50
                            </button>
                            <button type="button" wire:click="$set('saldoInicial', 100)" class="flex-1 py-1.5 px-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[10px] font-extrabold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition">
                                S/ 100
                            </button>
                            <button type="button" wire:click="$set('saldoInicial', 200)" class="flex-1 py-1.5 px-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-[10px] font-extrabold rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 transition">
                                S/ 200
                            </button>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="space-y-1.5">
                        <label class="block text-slate-650 dark:text-slate-400 text-xs font-bold uppercase tracking-wider">Observaciones / Notas de Apertura</label>
                        <textarea 
                            id="observaciones" 
                            wire:model="observaciones"
                            rows="2"
                            class="w-full bg-slate-50/40 dark:bg-slate-950/30 border border-slate-300 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl py-2 px-3 text-xs font-bold text-slate-800 dark:text-slate-200 transition resize-none" 
                            placeholder="Ingrese notas adicionales de apertura (opcional)..."
                            maxLength="500"
                        ></textarea>
                        @error('observaciones')
                            <span class="text-rose-500 text-[10px] block mt-0.5 font-normal">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Botón de Envío -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold py-3.5 text-sm transition shadow-lg shadow-emerald-500/10 focus:outline-none"
                        >
                            <span wire:loading.remove wire:target="abrirCajaManual">Abrir Caja y Comenzar</span>
                            <span wire:loading wire:target="abrirCajaManual" class="flex items-center gap-1.5">
                                <span class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                Procesando Apertura...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-filament-panels::page>
