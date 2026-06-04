<x-filament-panels::page>
    <div class="proveedores-root space-y-6 animate-fade-in">

        {{-- Banner Informativo --}}
        <div class="relative overflow-hidden p-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-300/70 shadow-sm dark:from-amber-950/20 dark:to-orange-950/20 dark:border-amber-800/80">
            <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 to-orange-500/5 dark:from-amber-500/5 dark:to-orange-500/5 pointer-events-none"></div>
            <div class="flex items-start gap-3.5 relative">
                <div class="flex items-center justify-center p-2.5 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 shrink-0 ring-1 ring-amber-300/50 dark:ring-amber-700/50">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                </div>
                <div class="space-y-1">
                    <h4 class="text-xs font-bold tracking-wide text-amber-700 dark:text-amber-400 uppercase">
                        Gestión de Proveedores
                    </h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Administra los proveedores de tu empresa. Registra sus datos fiscales, contacto y realiza un seguimiento de tus compras.
                    </p>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="kpi-card kpi-amber">
                <div class="flex justify-between items-start">
                    <div class="space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">Total Proveedores</span>
                        <div class="text-3xl font-black text-white">
                            {{ $this->stats['total'] }}
                        </div>
                    </div>
                    <div class="p-2 bg-white/15 text-white rounded-xl ring-1 ring-white/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-emerald">
                <div class="flex justify-between items-start">
                    <div class="space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">Activos</span>
                        <div class="text-3xl font-black text-white">
                            {{ $this->stats['activos'] }}
                        </div>
                    </div>
                    <div class="p-2 bg-white/15 text-white rounded-xl ring-1 ring-white/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-orange">
                <div class="flex justify-between items-start">
                    <div class="space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">Inactivos</span>
                        <div class="text-3xl font-black text-white">
                            {{ $this->stats['inactivos'] }}
                        </div>
                    </div>
                    <div class="p-2 bg-white/15 text-white rounded-xl ring-1 ring-white/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botón Nuevo Proveedor --}}
        <div class="flex justify-end pt-2">
            <a href="{{ route('filament.admin.resources.proveedores.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-extrabold text-white bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 active:scale-95 transition-all shadow-lg shadow-amber-500/25 rounded-2xl">
                <span class="text-base">🚛</span>
                <span>Nuevo Proveedor</span>
            </a>
        </div>

        {{-- Tabla Premium --}}
        <div class="relative overflow-hidden rounded-2xl border border-amber-200/70 dark:border-slate-800/40 bg-gradient-to-br from-white to-amber-50/50 dark:from-slate-900/50 dark:to-amber-950/10 shadow-sm transition-all duration-300">
            <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 via-orange-400 to-amber-400 dark:from-amber-600 dark:via-orange-600 dark:to-amber-600"></div>

            <div class="p-5 border-b border-amber-100 dark:border-slate-800/40 flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-amber-50/40 dark:bg-slate-950/20">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-amber-400">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.602 10.602z" />
                        </svg>
                    </div>
                    <input type="text"
                           wire:model.live="search"
                           placeholder="Buscar proveedores por nombre, RUC, teléfono..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border-amber-200 bg-white dark:bg-slate-900/60 dark:border-slate-800 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 shadow-sm transition">
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5 p-1 bg-amber-100/60 dark:bg-slate-900/80 rounded-xl border border-amber-200/60 dark:border-slate-800/60">
                        <button type="button"
                                wire:click="$set('estado', 'active')"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'active' ? 'bg-white dark:bg-slate-800 text-amber-800 dark:text-white shadow-sm ring-1 ring-amber-300/50' : 'text-amber-600 hover:text-amber-800 dark:text-slate-400 dark:hover:text-slate-300' }}">
                            ● Activos
                        </button>
                        <button type="button"
                                wire:click="$set('estado', 'trashed')"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all {{ $estado === 'trashed' ? 'bg-white dark:bg-slate-800 text-rose-700 dark:text-white shadow-sm ring-1 ring-rose-300/50' : 'text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-slate-300' }}">
                            🗑 Papelera
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm">
                    <thead>
                        <tr class="bg-amber-100/60 dark:bg-slate-950/30">
                            <th class="py-4 px-6 font-bold uppercase tracking-wider text-[11px] text-amber-800 dark:text-amber-400 border-b border-amber-200/70 dark:border-slate-800/60">
                                <button type="button" wire:click="sortBy('nombre')" class="inline-flex items-center gap-1 hover:text-amber-900 dark:hover:text-amber-300">
                                    <span>🚛 Proveedor</span>
                                    @if ($sortField === 'nombre')
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}" />
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="py-4 px-6 font-bold uppercase tracking-wider text-[11px] text-amber-800 dark:text-amber-400 border-b border-amber-200/70 dark:border-slate-800/60">
                                <button type="button" wire:click="sortBy('tipo_documento')" class="inline-flex items-center gap-1 hover:text-amber-900 dark:hover:text-amber-300">
                                    <span>📄 Documento</span>
                                    @if ($sortField === 'tipo_documento')
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortDirection === 'asc' ? 'M4.5 15.75l7.5-7.5 7.5 7.5' : 'M19.5 8.25l-7.5 7.5-7.5-7.5' }}" />
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="py-4 px-6 font-bold uppercase tracking-wider text-[11px] text-amber-800 dark:text-amber-400 border-b border-amber-200/70 dark:border-slate-800/60">📞 Contacto</th>
                            <th class="py-4 px-6 font-bold uppercase tracking-wider text-[11px] text-amber-800 dark:text-amber-400 border-b border-amber-200/70 dark:border-slate-800/60 text-center">🛒 Compras</th>
                            <th class="py-4 px-6 font-bold uppercase tracking-wider text-[11px] text-amber-800 dark:text-amber-400 border-b border-amber-200/70 dark:border-slate-800/60 text-center">Estado</th>
                            <th class="py-4 px-6 font-bold uppercase tracking-wider text-[11px] text-amber-800 dark:text-amber-400 border-b border-amber-200/70 dark:border-slate-800/60 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sky-100/60 dark:divide-slate-800/40">
                        @forelse ($this->proveedores as $proveedor)
                            @php $isTrashed = $proveedor->trashed(); @endphp
                            <tr class="transition-all duration-150 {{ $isTrashed ? 'bg-rose-50/30 dark:bg-rose-950/10 border-l-4 border-l-rose-500' : 'hover:bg-amber-50/50 dark:hover:bg-amber-950/10 border-l-4 border-l-transparent hover:border-l-amber-400' }}">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/40 dark:to-orange-900/40 text-amber-600 dark:text-amber-400 ring-1 ring-amber-300/50 dark:ring-amber-800/60">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 dark:text-white leading-tight">
                                                {{ $proveedor->nombre }}
                                            </div>
                                            @if ($proveedor->razon_social)
                                                <div class="text-[10px] font-semibold text-slate-500 dark:text-slate-500 mt-0.5">
                                                    {{ $proveedor->razon_social }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-6">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'inline-flex self-start px-2.5 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider border',
                                            'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30' => $proveedor->tipo_documento === 'RUC',
                                            'bg-blue-100 text-blue-700 border-blue-300 dark:bg-blue-500/15 dark:text-blue-400 dark:border-blue-500/30' => $proveedor->tipo_documento === 'DNI',
                                            'bg-purple-100 text-purple-700 border-purple-300 dark:bg-purple-500/15 dark:text-purple-400 dark:border-purple-500/30' => $proveedor->tipo_documento === 'CE',
                                            'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-500/15 dark:text-slate-400 dark:border-slate-500/30' => $proveedor->tipo_documento === 'OTRO' || !$proveedor->tipo_documento,
                                        ])>
                                            {{ $proveedor->tipo_documento ?? '—' }}
                                        </span>
                                        <span class="text-xs font-mono font-bold text-slate-800 dark:text-slate-300">
                                            {{ $proveedor->numero_documento ?? '—' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="py-4 px-6">
                                    <div class="flex flex-col gap-1">
                                        @if ($proveedor->telefono)
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">📞 {{ $proveedor->telefono }}</span>
                                        @endif
                                        @if ($proveedor->email)
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">✉️ {{ $proveedor->email }}</span>
                                        @endif
                                        @if ($proveedor->contacto_principal)
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">👤 {{ $proveedor->contacto_principal }}</span>
                                        @endif
                                        @if (!$proveedor->telefono && !$proveedor->email && !$proveedor->contacto_principal)
                                            <span class="text-xs text-slate-400 dark:text-slate-600 italic">— Sin contacto</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 border border-amber-300 dark:bg-amber-500/15 dark:text-amber-400 dark:border-amber-500/30 shadow-sm">
                                        🛒 {{ $proveedor->compras_count ?? 0 }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-center">
                                    @if ($isTrashed)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-700 border border-rose-300 dark:bg-rose-500/15 dark:text-rose-400 dark:border-rose-500/30 shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Eliminado
                                        </span>
                                    @else
                                        @if ($proveedor->estado)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-300 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600 border border-slate-300 dark:bg-slate-500/15 dark:text-slate-400 dark:border-slate-500/30 shadow-sm">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2 flex-wrap">
                                        <a href="{{ route('filament.admin.resources.compras.index', ['proveedor_id' => $proveedor->id]) }}"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-500/10 hover:bg-amber-200 dark:hover:bg-amber-500/20 rounded-xl transition-all ring-1 ring-amber-300/50 dark:ring-amber-700/50 shadow-sm">
                                            🛒 Compras
                                        </a>

                                        @if ($isTrashed)
                                            <button type="button"
                                                    wire:click="restore({{ $proveedor->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-md shadow-emerald-500/15 active:scale-95 transition-all">
                                                ♻️ Restaurar
                                            </button>
                                            <button type="button"
                                                    wire:click="forceDelete({{ $proveedor->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-500 rounded-xl shadow-md shadow-red-500/15 active:scale-95 transition-all">
                                                🗑️ Eliminar
                                            </button>
                                        @else
                                            <a href="{{ route('filament.admin.resources.proveedores.edit', $proveedor->id) }}"
                                               class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 rounded-xl shadow-md shadow-amber-500/20 active:scale-95 transition-all">
                                                ✏️ Editar
                                            </a>
                                            <button type="button"
                                                    wire:click="confirmDelete({{ $proveedor->id }})"
                                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-md shadow-rose-500/15 active:scale-95 transition-all">
                                                🗑️ Eliminar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 px-6 text-center">
                                    <div class="max-w-md mx-auto space-y-4">
                                        <div class="inline-flex p-4 bg-amber-50 dark:bg-slate-900/60 rounded-2xl border border-amber-200/50 dark:border-slate-800/40 text-amber-400 shadow-sm">
                                            <span class="text-4xl">🔍</span>
                                        </div>
                                        <div class="space-y-1">
                                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                                {{ $estado === 'trashed' ? 'No hay proveedores eliminados' : 'No se encontraron proveedores' }}
                                            </h3>
                                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ $estado === 'trashed'
                                                    ? 'La papelera está vacía. Los proveedores eliminados aparecerán aquí.'
                                                    : (filled(trim($search))
                                                        ? 'No hay proveedores que coincidan con tu búsqueda.'
                                                        : 'Aún no has registrado ningún proveedor.') }}
                                            </p>
                                        </div>
                                        @if (filled(trim($search)))
                                            <button type="button"
                                                    wire:click="$set('search', '')"
                                                    class="inline-flex items-center gap-1.5 px-4.5 py-2 text-xs font-bold text-amber-700 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-xl shadow-sm transition dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300">
                                                <span>🔄</span>
                                                <span>Limpiar búsqueda</span>
                                            </button>
                                        @else
                                            <a href="{{ route('filament.admin.resources.proveedores.create') }}"
                                               class="inline-flex items-center gap-1.5 px-5 py-2.5 text-xs font-bold text-white bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 rounded-xl shadow-md transition">
                                                <span class="text-sm">🚛</span>
                                                <span>Nuevo Proveedor</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($this->proveedores->hasPages())
                <div class="px-6 py-4 border-t border-amber-100 dark:border-slate-800/40 bg-amber-50/30 dark:bg-slate-950/5">
                    {{ $this->proveedores->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div x-data="{ open: @entangle('showDeleteConfirmModal') }"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"
                 x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = false">
            </div>

            <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-amber-200 dark:border-slate-800 p-6 max-w-md w-full animate-fade-in"
                 x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-950/40 text-rose-600 border border-rose-300 dark:border-rose-900/40">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">¿Eliminar proveedor?</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Esta acción enviará el proveedor a la papelera.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button"
                            @click="open = false"
                            class="px-4.5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">
                        Cancelar
                    </button>
                    <button type="button"
                            wire:click="delete"
                            class="px-4.5 py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 rounded-xl shadow-md shadow-rose-500/15 active:scale-95 transition-all">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
