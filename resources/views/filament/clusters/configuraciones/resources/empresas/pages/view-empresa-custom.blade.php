<div class="space-y-6 bg-slate-50/30 dark:bg-[#080b14] p-6 rounded-3xl border border-slate-200/60 dark:border-[#151b2e] shadow-2xl text-slate-800 dark:text-slate-200">
    <!-- 1. HEADER CARD (DARK/LIGHT BRANDING GRADIENT) -->
    <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-indigo-50/90 via-slate-50/70 to-white/40 dark:from-[#171336] dark:via-[#0d101e] dark:to-[#0a0c14] p-6 md:p-8 shadow-xl border border-slate-200/80 dark:border-[#1d2745]/60 text-slate-800 dark:text-white">
        <!-- Background ambient glow blobs -->
        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-80 w-80 rounded-full bg-indigo-500/[0.03] dark:bg-indigo-500/5 blur-3xl"></div>
        <div class="absolute left-0 bottom-0 -ml-20 -mb-20 h-80 w-80 rounded-full bg-purple-500/[0.03] dark:bg-purple-500/5 blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <!-- Logo Container -->
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl bg-white p-3 shadow-lg border border-slate-200 dark:border-slate-700/50">
                    @if ($this->record->logo)
                        <img src="{{ asset('storage/' . $this->record->logo) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                    @else
                        <div class="flex h-full w-full items-center justify-center rounded-xl bg-slate-100 text-slate-800 font-black text-3xl">
                            {{ mb_substr($this->record->razon_social, 0, 1) }}
                        </div>
                    @endif
                </div>

                <!-- Brand Info -->
                <div class="text-center sm:text-left space-y-2">
                    <div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                            Activa
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                        {{ $this->record->razon_social }}
                    </h1>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                        <span class="text-purple-600 dark:text-[#a855f7] font-bold">RUC</span> <span class="text-slate-700 dark:text-slate-300 ml-1">{{ $this->record->ruc }}</span>
                    </p>
                </div>
            </div>

            <!-- Actions and Timestamps -->
            <div class="w-full md:w-auto flex flex-col sm:flex-row md:flex-col items-stretch sm:items-center md:items-end gap-4 shrink-0">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    @if ($this->canEdit())
                        <button wire:click="toggleEditingMode" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-slate-700 dark:text-slate-200 bg-white/85 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-900/60 active:bg-slate-200 dark:active:bg-slate-950 border border-slate-200 dark:border-slate-700/60 rounded-xl transition shadow-sm">
                            <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.082a4.5 4.5 0 0 1-2.012 1.257l-3.858 1.05a.75.75 0 0 1-.922-.922l1.05-3.858a4.5 4.5 0 0 1 1.257-2.012L17.8 7.893z" />
                            </svg>
                            Editar
                        </button>
                    @endif

                    <div x-data="{ open: false }" class="relative flex-1 sm:flex-initial">
                        <button @click="open = !open" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-white bg-[#5c4ce5] hover:bg-[#4b3cc4] active:bg-[#3c2eb4] rounded-xl transition shadow-sm">
                            <svg class="h-4.5 w-4.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                            <span>Más opciones</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1.5 z-10 text-slate-800 dark:text-slate-200">
                            <a href="#" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800">Ver Historial</a>
                            <a href="#" class="block px-4 py-2 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 text-rose-500 font-bold">Desactivar Emisión</a>
                        </div>
                    </div>
                </div>

                <!-- Date widgets stack -->
                <div class="hidden md:flex flex-col gap-2 w-full max-w-[240px]">
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-[#07090e] border border-slate-200 dark:border-[#141b2e] text-xs text-slate-700 dark:text-slate-300">
                        <div class="p-1.5 rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-[#a855f7] shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[0.62rem] font-bold text-slate-500 uppercase tracking-wider">Creada el</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $this->record->created_at ? $this->record->created_at->format('d/m/Y H:i') : '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-[#07090e] border border-slate-200 dark:border-[#141b2e] text-xs text-slate-700 dark:text-slate-300">
                        <div class="p-1.5 rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-[#a855f7] shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[0.62rem] font-bold text-slate-500 uppercase tracking-wider">Última actualización</p>
                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ $this->record->updated_at ? $this->record->updated_at->format('d/m/Y H:i') : '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. INFORMACIÓN GENERAL -->
    <div class="rounded-2xl border border-slate-200 dark:border-[#161c2d] bg-white dark:bg-[#0c101d] shadow-lg overflow-hidden animate-fade-in">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-[#141b2c]">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 dark:bg-[#1b1437] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100 dark:border-purple-500/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Información general</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-16 gap-y-0">
                <!-- Left Column -->
                <div class="divide-y divide-slate-100 dark:divide-[#141a29]">
                    <!-- RUC -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                            <span class="font-semibold">RUC</span>
                        </div>
                        <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $this->record->ruc }}</span>
                    </div>

                    <!-- Razón Social -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 16.5h1.5M13.5 16.5H15" />
                                </svg>
                            </div>
                            <span class="font-semibold">Razón Social</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-white">{{ $this->record->razon_social }}</span>
                    </div>

                    <!-- Dirección Fiscal -->
                    <div class="flex items-start justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm shrink-0">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Dirección Fiscal</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-white leading-relaxed text-right max-w-[280px] md:max-w-[360px]">{{ $this->record->direccion_fiscal ?? '— No declarada' }}</span>
                    </div>

                    <!-- Logo -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375 0 1 1-.75 0 .375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Logo</span>
                        </div>
                        @if ($this->record->logo)
                            <div class="flex items-center gap-2.5">
                                <img src="{{ asset('storage/' . $this->record->logo) }}" alt="Logo mini" class="h-6 w-6 rounded border border-slate-200 dark:border-slate-700 object-contain bg-white shrink-0">
                                <a href="{{ asset('storage/' . $this->record->logo) }}" download class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-0.5">
                                    <span>{{ basename($this->record->logo) }}</span>
                                </a>
                                <a href="{{ asset('storage/' . $this->record->logo) }}" download class="text-purple-600 dark:text-[#a855f7] hover:text-purple-700 dark:hover:text-purple-300 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </a>
                            </div>
                        @else
                            <span class="text-sm font-semibold text-slate-400">— Sin logo</span>
                        @endif
                    </div>

                    <!-- Incluido Tributo -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a1.5 1.5 0 0 0 2.122 0l4.75-4.75a1.5 1.5 0 0 0 0-2.122L10.54 3.659A2.25 2.25 0 0 0 9.568 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Incluido Tributo</span>
                        </div>
                        @if ($this->record->incluido_tributo)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                Si
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                                No
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Right Column -->
                <div class="divide-y divide-slate-100 dark:divide-[#141a29] lg:border-t-0 border-t border-slate-100 dark:border-[#141b2c]">
                    <!-- Entorno -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
                                </svg>
                            </div>
                            <span class="font-semibold">Entorno</span>
                        </div>
                        @if ($this->record->entorno)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-50 dark:bg-[#181335] text-purple-700 dark:text-indigo-400 border border-purple-200 dark:border-indigo-500/20">
                                Producción
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                                No
                            </span>
                        @endif
                    </div>

                    <!-- Fecha de Creación -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                            </div>
                            <span class="font-semibold">Fecha de creación</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-white">{{ $this->record->created_at ? $this->record->created_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>

                    <!-- Última actualización -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Última actualización</span>
                        </div>
                        <span class="text-sm font-semibold text-slate-800 dark:text-white">{{ $this->record->updated_at ? $this->record->updated_at->format('d/m/Y H:i') : '—' }}</span>
                    </div>

                    <!-- Estado -->
                    <div class="flex items-center justify-between py-4">
                        <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-sm">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-50 dark:bg-[#181335] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100/50 dark:border-purple-500/10">
                                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </div>
                            <span class="font-semibold">Estado</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50/10 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                            Activa
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. CONFIGURACIÓN DE LA EMPRESA (SUNAT & FIRMA) -->
    <div class="rounded-2xl border border-slate-200 dark:border-[#161c2d] bg-white dark:bg-[#0c101d] shadow-lg overflow-hidden animate-fade-in">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-[#141b2c]">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 dark:bg-[#1b1437] text-purple-600 dark:text-purple-400 shrink-0 border border-purple-100 dark:border-purple-500/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Configuración de la empresa</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-16 gap-y-0">
                <!-- Left Column -->
                <div class="divide-y divide-slate-100 dark:divide-[#141a29]">
                    <!-- Tipo de certificado -->
                    <div class="flex items-center justify-between py-4">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Tipo de certificado</span>
                        <span class="text-sm font-bold text-slate-800 dark:text-white">{{ $this->record->empresaConfig?->tipo_certificado ?? '— No configurado' }}</span>
                    </div>

                    <!-- Certificado -->
                    <div class="flex items-center justify-between py-4">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Certificado</span>
                        <div class="flex items-center gap-2">
                            @if ($this->record->empresaConfig?->certificado)
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 truncate mr-2">{{ basename($this->record->empresaConfig->certificado) }}</span>
                                <a href="{{ asset('storage/' . $this->record->empresaConfig->certificado) }}" download class="p-1.5 border border-slate-200 dark:border-slate-700/60 rounded-lg bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </a>
                            @else
                                <span class="text-sm font-semibold text-slate-400">— Sin certificado</span>
                            @endif
                        </div>
                    </div>

                    <!-- Certificado Pass -->
                    <div x-data="{ show: false }" class="flex items-center justify-between py-4">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Certificado Pass</span>
                        <div class="flex items-center gap-3">
                            <span x-text="show ? '{{ $this->record->empresaConfig?->certificado_pass ?? '' }}' : '••••••••'" class="text-sm font-semibold font-mono text-slate-800 dark:text-white"></span>
                            @if ($this->record->empresaConfig?->certificado_pass)
                                <button @click="show = !show" class="p-1.5 border border-slate-200 dark:border-slate-700/60 rounded-lg bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3-3.671-3.671m0-3.65a3 3 0 0 0-4.293 4.293m4.293-4.293-4.293 4.293" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- User SOL -->
                    <div class="flex items-center justify-between py-4">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">User SOL</span>
                        <span class="text-sm font-semibold text-slate-800 dark:text-white">{{ $this->record->empresaConfig?->user_sol ?? '— No configurado' }}</span>
                    </div>

                    <!-- Pass SOL -->
                    <div x-data="{ show: false }" class="flex items-center justify-between py-4 last:border-b-0">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Pass SOL</span>
                        <div class="flex items-center gap-3">
                            <span x-text="show ? '{{ $this->record->empresaConfig?->pass_sol ?? '' }}' : '••••••••'" class="text-sm font-semibold font-mono text-slate-800 dark:text-white"></span>
                            @if ($this->record->empresaConfig?->pass_sol)
                                <button @click="show = !show" class="p-1.5 border border-slate-200 dark:border-slate-700/60 rounded-lg bg-slate-50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3-3.671-3.671m0-3.65a3 3 0 0 0-4.293 4.293m4.293-4.293-4.293 4.293" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="divide-y divide-slate-100 dark:divide-[#141a29] lg:border-t-0 border-t border-slate-100 dark:border-[#141b2c]">
                    <!-- SUNAT Client ID -->
                    <div x-data="{ copied: false, val: '{{ $this->record->empresaConfig?->sunat_client_id ?? '' }}' }" class="flex items-center justify-between py-4">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">SUNAT Client ID</span>
                        <div class="flex items-center justify-between bg-slate-50 dark:bg-[#070a12] border border-slate-200 dark:border-[#1b223c] rounded-xl px-4 py-2 w-full max-w-[320px]">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 font-mono select-all truncate mr-2">{{ $this->record->empresaConfig?->sunat_client_id ?? '—' }}</span>
                            @if ($this->record->empresaConfig?->sunat_client_id)
                                <button @click="navigator.clipboard.writeText(val); copied = true; setTimeout(() => copied = false, 1500)" class="p-1.5 border border-slate-200 dark:border-slate-700/60 rounded-lg bg-white dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition shrink-0">
                                    <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125v-9.75A1.125 1.125 0 0 1 5 9.75h3.375m1.5 0H14.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-3.375m1.5 0H9.75M9 10.5h.008v.008H9V10.5Zm0 3.5h.008v.008H9V14Zm0 3.5h.008v.008H9v-.008Zm3-7h.008v.008h-.008V11Zm0 3.5h.008v.008h-.008V14.5Zm0 3.5h.008v.008h-.008v-.008Zm3-7h.008v.008h-.008V11Zm0 3.5h.008v.008h-.008V14.5Z" />
                                    </svg>
                                    <svg x-show="copied" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- SUNAT Client Secret -->
                    <div x-data="{ show: false }" class="flex items-center justify-between py-4 last:border-b-0">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">SUNAT Client Secret</span>
                        <div class="flex items-center justify-between bg-slate-50 dark:bg-[#070a12] border border-slate-200 dark:border-[#1b223c] rounded-xl px-4 py-2 w-full max-w-[320px]">
                            <span x-text="show ? '{{ $this->record->empresaConfig?->sunat_client_secret ?? '' }}' : '••••••••••••••••••••••••'" class="text-sm font-semibold text-slate-700 dark:text-slate-200 font-mono truncate mr-2 select-all"></span>
                            @if ($this->record->empresaConfig?->sunat_client_secret)
                                <button @click="show = !show" class="p-1.5 border border-slate-200 dark:border-slate-700/60 rounded-lg bg-white dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition shrink-0">
                                    <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3-3.671-3.671m0-3.65a3 3 0 0 0-4.293 4.293m4.293-4.293-4.293 4.293" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

