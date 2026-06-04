<x-filament-panels::page>
    <div class="space-y-6 animate-fade-in" x-data="{ activeTab: 'general' }">

        {{-- Premium Header --}}
        <div class="relative overflow-hidden p-6 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 shadow-xl shadow-amber-500/25 dark:from-amber-700 dark:to-amber-800">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-50"></div>
            <div class="relative flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/20 text-white ring-1 ring-white/30 backdrop-blur-sm">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">Nuevo Proveedor</h1>
                    <p class="text-sm text-white/70 mt-0.5 font-medium">Completa los datos para registrar un nuevo proveedor</p>
                </div>
            </div>
        </div>

        {{-- Tabs de navegación --}}
        <div class="flex gap-2 p-1.5 bg-amber-50/70 dark:bg-slate-900/60 rounded-2xl border border-amber-200/60 dark:border-slate-800/40" role="tablist">
            <button type="button" @click="activeTab = 'general'" :class="activeTab === 'general' ? 'bg-white dark:bg-slate-800 text-amber-700 dark:text-amber-400 shadow-sm ring-1 ring-amber-300/50 dark:ring-amber-700/50' : 'text-slate-500 hover:text-amber-700 dark:hover:text-amber-400'" class="flex-1 px-4 py-3 rounded-xl text-xs font-extrabold uppercase tracking-wider transition-all">
                🚛 General
            </button>
            <button type="button" @click="activeTab = 'fiscal'" :class="activeTab === 'fiscal' ? 'bg-white dark:bg-slate-800 text-amber-700 dark:text-amber-400 shadow-sm ring-1 ring-amber-300/50 dark:ring-amber-700/50' : 'text-slate-500 hover:text-amber-700 dark:hover:text-amber-400'" class="flex-1 px-4 py-3 rounded-xl text-xs font-extrabold uppercase tracking-wider transition-all">
                🏢 Fiscal
            </button>
            <button type="button" @click="activeTab = 'contacto'" :class="activeTab === 'contacto' ? 'bg-white dark:bg-slate-800 text-amber-700 dark:text-amber-400 shadow-sm ring-1 ring-amber-300/50 dark:ring-amber-700/50' : 'text-slate-500 hover:text-amber-700 dark:hover:text-amber-400'" class="flex-1 px-4 py-3 rounded-xl text-xs font-extrabold uppercase tracking-wider transition-all">
                📞 Contacto
            </button>
        </div>

        {{-- Formulario --}}
        <form wire:submit="save" class="space-y-6">

            {{-- TAB: GENERAL --}}
            <div x-show="activeTab === 'general'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="relative overflow-hidden rounded-2xl border border-amber-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-amber-50/30 dark:from-slate-900/50 dark:to-amber-950/10 shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 ring-1 ring-amber-300/50 dark:ring-amber-600/30">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Información General</h3>
                                <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Datos básicos del proveedor</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🚛 Nombre comercial <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="nombre" placeholder="Ej: Distribuidora Los Andes"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm @error('nombre') border-rose-300 bg-rose-50/50 dark:border-rose-800 dark:bg-rose-950/20 ring-2 ring-rose-500/20 @else border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400 @enderror text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600">
                                @error('nombre') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📄 Tipo doc. <span class="text-rose-500">*</span></label>
                                    <select wire:model="tipo_documento"
                                        class="w-full px-3 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                                        <option value="RUC">RUC</option>
                                        <option value="DNI">DNI</option>
                                        <option value="CE">Carné Ext.</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">N° documento <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="numero_documento" placeholder="20123456781"
                                        class="w-full px-3 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm @error('numero_documento') border-rose-300 bg-rose-50/50 dark:border-rose-800 dark:bg-rose-950/20 ring-2 ring-rose-500/20 @else border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400 @enderror text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 font-mono">
                                    @error('numero_documento') <p class="mt-1 text-[10px] font-bold text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: FISCAL --}}
            <div x-show="activeTab === 'fiscal'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="relative overflow-hidden rounded-2xl border border-amber-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-amber-50/30 dark:from-slate-900/50 dark:to-amber-950/10 shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 ring-1 ring-amber-300/50 dark:ring-amber-600/30">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Datos Fiscales</h3>
                                <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Información tributaria del proveedor</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🏢 Razón social</label>
                                <input type="text" wire:model="razon_social" placeholder="DISTRIBUIDORA LOS ANDES S.A.C."
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">🏷️ Rubro / Giro</label>
                                <input type="text" wire:model="rubro" placeholder="Abarrotes y Víveres"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📍 Dirección</label>
                                <input type="text" wire:model="direccion" placeholder="Av. Grau 456, Lima"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: CONTACTO --}}
            <div x-show="activeTab === 'contacto'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="relative overflow-hidden rounded-2xl border border-amber-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-amber-50/30 dark:from-slate-900/50 dark:to-amber-950/10 shadow-sm">
                    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500"></div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 ring-1 ring-amber-300/50 dark:ring-amber-600/30">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 3.75v4.5m0-4.5h-4.5m4.5 0-6 6m3 12c-8.284 0-15-6.716-15-15V4.5A2.25 2.25 0 0 1 4.5 2.25h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173l-1.293.97a1.062 1.062 0 0 0-.38 1.21 12.035 12.035 0 0 0 7.143 7.143c.441.162.928-.004 1.21-.38l.97-1.293a1.125 1.125 0 0 1 1.173-.417l4.423 1.106c.5.125.852.575.852 1.091V19.5a2.25 2.25 0 0 1-2.25 2.25h-2.25Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Contacto</h3>
                                <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Canales de comunicación</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📞 Teléfono</label>
                                <input type="text" wire:model="telefono" placeholder="999 111 001"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">✉️ Email</label>
                                <input type="email" wire:model="email" placeholder="ventas@proveedor.com"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">👤 Contacto principal</label>
                                <input type="text" wire:model="contacto_principal" placeholder="Carlos Mendoza"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📞 Tel. contacto</label>
                                <input type="text" wire:model="telefono_contacto" placeholder="999 111 002"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400">
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1.5">📝 Observaciones</label>
                            <textarea wire:model="observaciones" rows="3" placeholder="Notas adicionales sobre el proveedor..."
                                class="w-full px-4 py-2.5 rounded-xl border text-sm transition-all duration-200 shadow-sm border-slate-200 bg-white dark:bg-slate-900/60 dark:border-slate-700/80 text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 dark:focus:ring-amber-500/10 dark:focus:border-amber-400"></textarea>
                        </div>

                        <div class="mt-5 pt-5 border-t border-slate-200 dark:border-slate-700/60">
                            <label class="inline-flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model="estado"
                                    class="h-5 w-5 rounded-lg border-slate-300 dark:border-slate-700 text-amber-500 focus:ring-amber-500/20 dark:bg-slate-900 transition-all duration-200">
                                <div>
                                    <span class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Proveedor activo</span>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Los proveedores activos aparecen en las listas de selección</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Actions --}}
            <div class="flex items-center justify-between p-5 rounded-2xl border border-slate-200/60 dark:border-slate-800/30 bg-white/70 dark:bg-slate-900/30 shadow-sm">
                <a href="{{ route('filament.admin.resources.proveedores.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Cancelar
                </a>

                <button type="submit"
                    class="inline-flex items-center gap-2.5 px-7 py-3.5 text-sm font-extrabold text-white bg-gradient-to-r from-amber-500 to-amber-700 hover:from-amber-400 hover:to-amber-600 rounded-xl shadow-lg shadow-amber-500/25 active:scale-95 transition-all">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Guardar Proveedor
                </button>
            </div>

        </form>
    </div>
</x-filament-panels::page>
