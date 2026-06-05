<div class="space-y-6 bg-slate-50/30 dark:bg-[#080b14] p-6 rounded-3xl border border-slate-200/60 dark:border-[#151b2e] shadow-2xl text-slate-800 dark:text-slate-200">
    @php
        $config = $this->record->empresaConfig;
    @endphp

    {{-- ====== HEADER CARD ====== --}}
    <div class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-indigo-50/90 via-slate-50/70 to-white/40 dark:from-[#171336] dark:via-[#0d101e] dark:to-[#0a0c14] p-6 md:p-8 shadow-xl border border-slate-200/80 dark:border-[#1d2745]/60 text-slate-800 dark:text-white">
        <div class="absolute right-0 top-0 -mr-20 -mt-20 h-80 w-80 rounded-full bg-indigo-500/[0.03] dark:bg-indigo-500/5 blur-3xl"></div>
        <div class="absolute left-0 bottom-0 -ml-20 -mb-20 h-80 w-80 rounded-full bg-purple-500/[0.03] dark:bg-purple-500/5 blur-3xl"></div>

        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Logo --}}
                <div x-data="{ logoPreview: '{{ $this->record->logo ? asset("storage/" . $this->record->logo) : "" }}' }" class="relative shrink-0">
                    <div class="flex h-24 w-24 items-center justify-center rounded-2xl bg-white p-2 shadow-lg border border-slate-200 dark:border-slate-700/50 overflow-hidden">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" alt="Logo preview" class="max-h-full max-w-full object-contain rounded-xl">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="flex h-full w-full items-center justify-center rounded-xl bg-slate-100 text-slate-800 font-black text-3xl">
                                {{ mb_substr($this->record->razon_social, 0, 1) }}
                            </div>
                        </template>
                    </div>
                    <label for="logo-upload" class="absolute -bottom-2 -right-2 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-md hover:bg-slate-50 dark:hover:bg-slate-700 transition-all hover:scale-105 text-indigo-600 dark:text-indigo-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                        </svg>
                    </label>
                    <input id="logo-upload" type="file" wire:model="data.logo" accept="image/*" class="hidden" @change="const f = $event.target.files[0]; if (f) { const reader = new FileReader(); reader.onload = e => logoPreview = e.target.result; reader.readAsDataURL(f); }">
                </div>
                @error('data.logo') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror

                {{-- Info --}}
                <div class="text-center sm:text-left space-y-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400"></span>
                        Editando
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white">
                        {{ $this->record->razon_social }}
                    </h1>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                        <span class="text-purple-600 dark:text-[#a855f7] font-bold">RUC</span>
                        <span class="text-slate-700 dark:text-slate-300 ml-1">{{ $this->record->ruc }}</span>
                    </p>
                </div>
            </div>

            {{-- Status card --}}
            <div class="w-full md:w-auto flex flex-col gap-2 shrink-0">
                <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-white/80 dark:bg-[#07090e] border border-amber-200 dark:border-amber-500/20 text-xs text-slate-700 dark:text-slate-300">
                    <div class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100/50 dark:border-amber-500/10">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[0.62rem] font-bold text-slate-500 uppercase tracking-wider">Modo edición activo</p>
                        <p class="font-bold text-amber-600 dark:text-amber-400">Los cambios se guardarán al confirmar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== SECTION 1: AMBIENTE Y CONFIGURACIÓN FISCAL ====== --}}
    <div class="rounded-2xl border border-slate-200 dark:border-[#161c2d] bg-white dark:bg-[#0c101d] shadow-lg overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-[#141b2c]">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-500/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0m-9.75 0h9.75" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Ambiente y Configuración Fiscal</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Entorno --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Entorno de Producción SUNAT</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Actívalo para emitir comprobantes reales con validez tributaria ante SUNAT.</p>
                    <div class="flex items-center gap-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="data.entorno" class="sr-only peer">
                            <div class="w-12 h-6.5 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:start-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500"></div>
                        </label>
                        <span x-data="{ entorno: $wire.entando ?? false }" class="text-sm font-bold">
                            <span wire:ignore>
                                <span class="text-emerald-600 dark:text-emerald-400" wire:loading.remove wire:target="data.entorno">
                                    {{ $this->data['entorno'] ?? false ? 'Producción' : 'Desarrollo' }}
                                </span>
                            </span>
                        </span>
                    </div>
                    @error('data.entorno') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>

                {{-- Incluido Tributo --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Precios incluyen IGV</label>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Activo si los precios del catálogo ya contienen el 18% del impuesto.</p>
                    <div class="flex items-center gap-4">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="data.incluido_tributo" class="sr-only peer">
                            <div class="w-12 h-6.5 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:start-[3px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 dark:peer-checked:bg-indigo-500"></div>
                        </label>
                        <span class="text-sm font-bold">
                            <span class="{{ ($this->data['incluido_tributo'] ?? false) ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ ($this->data['incluido_tributo'] ?? false) ? 'IGV Incluido' : 'IGV Adicional' }}
                            </span>
                        </span>
                    </div>
                    @error('data.incluido_tributo') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ====== SECTION 2: IDENTIDAD Y PERFIL FISCAL ====== --}}
    <div class="rounded-2xl border border-slate-200 dark:border-[#161c2d] bg-white dark:bg-[#0c101d] shadow-lg overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-[#141b2c]">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-500/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 16.5h1.5m3 0h1.5" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Identidad y Perfil Fiscal</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-x-8 gap-y-6">
                {{-- RUC --}}
                <div class="space-y-1.5">
                    <label for="input-ruc" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                        Número de RUC
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <input id="input-ruc" type="text" wire:model="data.ruc" maxlength="11" placeholder="10XXXXXXXXX" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-10 pr-10 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">11 dígitos numéricos</p>
                    @error('data.ruc') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Razón Social --}}
                <div class="lg:col-span-2 space-y-1.5">
                    <label for="input-razon-social" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                        Razón Social
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 16.5h1.5m3 0h1.5" />
                            </svg>
                        </div>
                        <input id="input-razon-social" type="text" wire:model="data.razon_social" maxlength="255" placeholder="Razón social completa" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-10 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                    </div>
                    @error('data.razon_social') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Dirección Fiscal --}}
                <div class="lg:col-span-3 space-y-1.5">
                    <label for="input-direccion" class="block text-sm font-bold text-slate-700 dark:text-slate-300">
                        Dirección Fiscal
                    </label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </div>
                        <input id="input-direccion" type="text" wire:model="data.direccion_fiscal" maxlength="255" placeholder="Av. Principal N° 123, Distrito, Provincia" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-10 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                    </div>
                    @error('data.direccion_fiscal') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ====== SECTION 3: FIRMA DIGITAL Y CERTIFICADO ====== --}}
    <div class="rounded-2xl border border-slate-200 dark:border-[#161c2d] bg-white dark:bg-[#0c101d] shadow-lg overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-[#141b2c]">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-500/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Firma Digital y Certificado</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-6">
                {{-- Tipo Certificado --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300">Tipo de Certificado</label>
                    <div class="flex h-11 items-center rounded-xl bg-slate-50 dark:bg-[#070a12] px-4 ring-1 ring-inset ring-slate-200 dark:ring-slate-700/60 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        {{ $config?->tipo_certificado ?? 'Se detectará automáticamente' }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Extensión detectada del archivo cargado</p>
                </div>

                {{-- Certificado File Upload --}}
                <div x-data="{ certFile: '{{ $config?->certificado ? basename($config->certificado) : "" }}' }" class="space-y-1.5 lg:col-span-2">
                    <label for="cert-upload" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Certificado Digital</label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 p-4 rounded-xl bg-slate-50/80 dark:bg-[#070a12]/80 border border-dashed border-slate-300 dark:border-slate-700">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-400 dark:text-slate-500">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 truncate" x-text="certFile || 'Ningún archivo seleccionado'"></p>
                                <p class="text-xs text-slate-500" x-show="!certFile">Formatos: .pem, .pfx, .cer, .crt, .p12</p>
                            </div>
                        </div>
                        <label for="cert-upload" class="inline-flex shrink-0 items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 rounded-xl transition-all shadow-sm cursor-pointer">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ $config?->certificado ? 'Cambiar' : 'Seleccionar' }}
                        </label>
                    </div>
                    <input id="cert-upload" type="file" wire:model="data.certificado" accept=".pem,.pfx,.cer,.crt,.p12" class="hidden" @change="const f = $event.target.files[0]; if (f) certFile = f.name">
                    @error('data.certificado') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Certificado Pass --}}
                <div x-data="{ showPass: false }" class="space-y-1.5">
                    <label for="input-cert-pass" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Contraseña del Certificado</label>
                    <div class="relative">
                        <input id="input-cert-pass" :type="showPass ? 'text' : 'password'" wire:model="data.certificado_pass" placeholder="Dejar vacío para mantener actual" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-4 pr-11 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                            <svg x-show="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3-3.671-3.671m0-3.65a3 3 0 0 0-4.293 4.293m4.293-4.293-4.293 4.293" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Contraseña del archivo de certificado digital</p>
                    @error('data.certificado_pass') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ====== SECTION 4: CREDENCIALES Y CONEXIÓN SUNAT ====== --}}
    <div class="rounded-2xl border border-slate-200 dark:border-[#161c2d] bg-white dark:bg-[#0c101d] shadow-lg overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-[#141b2c]">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 shrink-0 border border-amber-100 dark:border-amber-500/10">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-slate-800 dark:text-white">Credenciales y Conexión SUNAT</h2>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-8 gap-y-6">
                {{-- User SOL --}}
                <div class="space-y-1.5">
                    <label for="input-user-sol" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Usuario SOL</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                        </div>
                        <input id="input-user-sol" type="text" wire:model="data.user_sol" placeholder="Ej: MODDATOS" maxlength="255" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-10 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Usuario secundario con permisos de emisión</p>
                    @error('data.user_sol') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- Pass SOL --}}
                <div x-data="{ showPass: false }" class="space-y-1.5">
                    <label for="input-pass-sol" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Contraseña SOL</label>
                    <div class="relative">
                        <input id="input-pass-sol" :type="showPass ? 'text' : 'password'" wire:model="data.pass_sol" placeholder="Dejar vacío para mantener actual" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-4 pr-11 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                            <svg x-show="!showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showPass" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3-3.671-3.671m0-3.65a3 3 0 0 0-4.293 4.293m4.293-4.293-4.293 4.293" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Contraseña del usuario secundario SOL</p>
                    @error('data.pass_sol') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- SUNAT Client ID --}}
                <div x-data="{ copied: false }" class="space-y-1.5">
                    <label for="input-client-id" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Client ID SUNAT</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>
                        </div>
                        <input id="input-client-id" type="text" wire:model="data.sunat_client_id" placeholder="Client ID" maxlength="255" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-10 pr-11 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                        <button type="button" @click="copied = true; navigator.clipboard.writeText('{{ $this->data['sunat_client_id'] ?? '' }}'); setTimeout(() => copied = false, 1500)" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                            <svg x-show="!copied" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125v-9.75A1.125 1.125 0 0 1 5 9.75h3.375m1.5 0H14.25c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-3.375m1.5 0H9.75M9 10.5h.008v.008H9V10.5Zm0 3.5h.008v.008H9V14Zm0 3.5h.008v.008H9v-.008Zm3-7h.008v.008h-.008V11Zm0 3.5h.008v.008h-.008V14.5Zm0 3.5h.008v.008h-.008v-.008Zm3-7h.008v.008h-.008V11Zm0 3.5h.008v.008h-.008V14.5Z" />
                            </svg>
                            <svg x-show="copied" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">ID obtenido desde el portal de SUNAT para APIs</p>
                    @error('data.sunat_client_id') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>

                {{-- SUNAT Client Secret --}}
                <div x-data="{ showSecret: false }" class="space-y-1.5">
                    <label for="input-client-secret" class="block text-sm font-bold text-slate-700 dark:text-slate-300">Client Secret SUNAT</label>
                    <div class="relative">
                        <input id="input-client-secret" :type="showSecret ? 'text' : 'password'" wire:model="data.sunat_client_secret" placeholder="Dejar vacío para mantener actual" class="block w-full rounded-xl border-0 bg-slate-50 dark:bg-[#070a12] py-3 pl-4 pr-11 text-sm font-semibold text-slate-900 dark:text-white ring-1 ring-inset ring-slate-300 dark:ring-slate-700 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 transition shadow-sm">
                        <button type="button" @click="showSecret = !showSecret" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                            <svg x-show="!showSecret" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showSecret" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.815 7.815 3 3m-3-3-3.671-3.671m0-3.65a3 3 0 0 0-4.293 4.293m4.293-4.293-4.293 4.293" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Secret obtenido desde el portal de SUNAT para APIs</p>
                    @error('data.sunat_client_secret') <p class="text-xs text-rose-500 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- ====== ACTION BUTTONS ====== --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 pt-2">
        <button type="button" wire:click="cancelEditing" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 text-sm font-bold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-800/80 active:bg-slate-200 dark:active:bg-slate-800 border border-slate-200 dark:border-slate-700/60 rounded-xl transition-all shadow-sm hover:shadow-md">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
            Cancelar
        </button>
        <button type="button" wire:click="save" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 active:from-indigo-700 active:to-purple-700 rounded-xl transition-all shadow-md hover:shadow-lg active:shadow-sm">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
            Guardar cambios
        </button>
    </div>
</div>
