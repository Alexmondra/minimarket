<section id="productos" class="relative overflow-hidden bg-white dark:bg-slate-950">
    {{-- Subtle background texture --}}
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808006_1px,transparent_1px),linear-gradient(to_bottom,#80808006_1px,transparent_1px)] bg-[size:24px_24px] pointer-events-none"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
        {{-- ============ SECTION HEADER ============ --}}
        <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Catálogo</span>
                <h2 class="mt-2 text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                    Nuestros Productos
                </h2>
                <p class="mt-2 text-base text-slate-500 dark:text-slate-400">
                    {{ $totalProductos }} productos disponibles{{ $selectedSucursal ? ' en ' . $selectedSucursal->nombre_sucursal : '' }}
                </p>
            </div>

            {{-- Quick search --}}
            <div class="relative w-full sm:w-72">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4.5 w-4.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar productos..."
                    class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 outline-none transition focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                >
            </div>
        </div>

        {{-- ============ MAIN LAYOUT: Filters + Grid ============ --}}
        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            {{-- ============ FILTERS SIDEBAR ============ --}}
            <aside class="space-y-4">
                {{-- Sucursal selector card --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-900/50 backdrop-blur-sm p-4 shadow-sm">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sucursal</label>
                    <select
                        wire:model.live="sucursalId"
                        class="mt-2 h-11 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 text-sm font-semibold text-slate-900 dark:text-white outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                    >
                        @forelse ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_sucursal }}</option>
                        @empty
                            <option value="">Sin sucursales</option>
                        @endforelse
                    </select>
                    @if ($selectedSucursal?->direccion)
                        <p class="mt-2 text-[11px] text-slate-400 flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            {{ $selectedSucursal->direccion }}
                        </p>
                    @endif
                </div>

                {{-- Marca filter --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-900/50 backdrop-blur-sm p-4 shadow-sm">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Marca</label>
                    <select
                        wire:model.live="marcaId"
                        class="mt-2 h-11 w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 text-sm font-semibold text-slate-900 dark:text-white outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                    >
                        <option value="">Todas las marcas</option>
                        @foreach ($marcas as $marca)
                            <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Categories chips --}}
                <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-slate-50/80 dark:bg-slate-900/50 backdrop-blur-sm p-4 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Categorías</p>
                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="selectCategoria(null)"
                            @class([
                                'rounded-xl px-3.5 py-2 text-xs font-bold transition-all duration-200',
                                'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' => $categoriaId === '',
                                'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-600 dark:hover:text-emerald-400' => $categoriaId !== '',
                            ])
                        >
                            Todas
                        </button>
                        @foreach ($categorias as $categoria)
                            <button
                                type="button"
                                wire:click="selectCategoria({{ $categoria->id }})"
                                @class([
                                    'rounded-xl px-3.5 py-2 text-xs font-bold transition-all duration-200',
                                    'bg-emerald-600 text-white shadow-md shadow-emerald-500/20' => $categoriaId === (string) $categoria->id,
                                    'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-300 dark:hover:border-emerald-700 hover:text-emerald-600 dark:hover:text-emerald-400' => $categoriaId !== (string) $categoria->id,
                                ])
                            >
                                {{ $categoria->nombre }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Clear filters --}}
                @if($search || $categoriaId || $marcaId)
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 transition hover:border-slate-300 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800"
                    >
                        Limpiar todos los filtros
                    </button>
                @endif
            </aside>

            {{-- ============ PRODUCT GRID ============ --}}
            <div>
                <div wire:loading.class="opacity-60 transition-opacity duration-300" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($productos as $producto)
                        <article
                            wire:key="public-product-{{ $producto['id'] }}"
                            data-3d-tilt
                            class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-sm transition-all duration-300 hover:shadow-2xl hover:shadow-emerald-500/5 hover:border-emerald-200 dark:hover:border-emerald-800/50 hover:-translate-y-1 cursor-pointer"
                            wire:click="openProduct({{ $producto['id'] }})"
                        >
                            {{-- Image area --}}
                            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-slate-800">
                                {{-- Gradient overlay on hover --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10"></div>

                                @if ($producto['imagen'])
                                    <img
                                        src="{{ $producto['imagen'] }}"
                                        alt="{{ $producto['nombre'] }}"
                                        class="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                                        loading="lazy"
                                    >
                                @else
                                    {{-- Fallback with category-based gradient --}}
                                    @php
                                        $gradients = [
                                            'from-emerald-400 via-teal-400 to-cyan-500',
                                            'from-violet-400 via-purple-400 to-fuchsia-500',
                                            'from-amber-400 via-orange-400 to-rose-500',
                                            'from-sky-400 via-blue-400 to-indigo-500',
                                            'from-lime-400 via-green-400 to-emerald-500',
                                        ];
                                        $gradient = $gradients[$producto['id'] % count($gradients)];
                                    @endphp
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br {{ $gradient }} p-8 transition duration-500 group-hover:scale-110">
                                        <div class="text-center">
                                            <svg class="h-16 w-16 mx-auto text-white/90 drop-shadow-lg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                            </svg>
                                            <span class="block mt-2 text-xs font-black text-white/80 uppercase tracking-wider">
                                                {{ $producto['categoria'] ?? 'Producto' }}
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Category badge top-right --}}
                                <span class="absolute top-3 right-3 z-20 rounded-lg bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider shadow-sm">
                                    {{ $producto['categoria'] ?? 'General' }}
                                </span>

                                {{-- View icon on hover --}}
                                <div class="absolute inset-0 z-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm shadow-xl">
                                        <svg class="h-5 w-5 text-slate-700 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            {{-- Product info --}}
                            <div class="flex flex-col flex-1 p-4">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $producto['nombre'] }}
                                </h3>

                                <div class="mt-auto pt-3 flex items-center justify-between gap-2">
                                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $producto['marca'] ?? 'Sin marca' }}
                                        @if ($producto['codigo'])
                                            <span class="text-slate-300 dark:text-slate-600 mx-1">·</span>{{ $producto['codigo'] }}
                                        @endif
                                    </span>
                                    <span class="flex items-center gap-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                        Ver
                                        <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                    </span>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="sm:col-span-2 xl:col-span-3 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 p-16 text-center">
                            <div class="flex h-16 w-16 mx-auto items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 mb-4">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-black text-slate-700 dark:text-slate-300">No se encontraron productos</h3>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Prueba con otra sucursal, categoría o búsqueda.</p>
                        </div>
                    @endforelse
                </div>

                {{-- ============ LOAD MORE ============ --}}
                @if ($totalProductos > $productos->count())
                    <div class="mt-10 flex justify-center">
                        <button
                            type="button"
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="group inline-flex items-center gap-2 rounded-2xl border-2 border-slate-900 dark:border-white bg-slate-900 dark:bg-white px-8 py-4 text-base font-black text-white dark:text-slate-900 shadow-xl shadow-slate-900/10 transition-all duration-300 hover:shadow-2xl hover:shadow-slate-900/20 hover:-translate-y-1 active:translate-y-0 disabled:cursor-wait disabled:opacity-60"
                        >
                            <span>Cargar más productos</span>
                            <svg class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ PRODUCT DETAIL MODAL ============ --}}
    @if ($showProductModal && $selectedProduct)
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            x-data
            x-on:keydown.escape.window="$wire.closeProduct()"
        >
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-md transition-opacity"></div>

            {{-- Modal panel --}}
            <div class="relative min-h-full flex items-center justify-center p-4 sm:p-6">
                {{-- Click outside to close --}}
                <button type="button" wire:click="closeProduct" class="fixed inset-0 h-full w-full cursor-default" aria-label="Cerrar modal"></button>

                <div class="relative z-10 w-full max-w-4xl overflow-hidden rounded-3xl bg-white dark:bg-slate-900 shadow-2xl ring-1 ring-slate-200/50 dark:ring-slate-800/50">
                    {{-- Close button --}}
                    <button
                        type="button"
                        wire:click="closeProduct"
                        class="absolute top-4 right-4 z-30 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm border border-slate-200 dark:border-slate-700 shadow-lg transition-all hover:scale-110 hover:bg-slate-100 dark:hover:bg-slate-700"
                    >
                        <svg class="h-4 w-4 text-slate-700 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>

                    <div class="grid md:grid-cols-[1fr_1fr]">
                        {{-- Image side --}}
                        <div class="relative bg-slate-100 dark:bg-slate-800 min-h-[280px] md:min-h-[400px]">
                            @if ($selectedProduct['imagen'])
                                <img
                                    src="{{ $selectedProduct['imagen'] }}"
                                    alt="{{ $selectedProduct['nombre'] }}"
                                    class="absolute inset-0 h-full w-full object-cover"
                                >
                            @else
                                @php
                                    $gradients = [
                                        'from-emerald-400 via-teal-400 to-cyan-500',
                                        'from-violet-400 via-purple-400 to-fuchsia-500',
                                        'from-amber-400 via-orange-400 to-rose-500',
                                        'from-sky-400 via-blue-400 to-indigo-500',
                                        'from-lime-400 via-green-400 to-emerald-500',
                                    ];
                                    $gradient = $gradients[$selectedProduct['id'] % count($gradients)];
                                @endphp
                                <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br {{ $gradient }} p-8">
                                    <svg class="h-24 w-24 text-white/90 drop-shadow-2xl" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                    </svg>
                                    <span class="mt-4 text-lg font-black text-white/90 uppercase tracking-wider">
                                        {{ $selectedProduct['categoria'] ?? 'Producto' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Info side --}}
                        <div class="p-6 sm:p-8 flex flex-col justify-center">
                            <div class="mb-2">
                                <span class="inline-flex rounded-lg bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                    {{ $selectedProduct['categoria'] ?? 'General' }}
                                </span>
                            </div>

                            <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                                {{ $selectedProduct['nombre'] }}
                            </h3>

                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                @if ($selectedProduct['marca'])
                                    <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-400 font-semibold">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg>
                                        {{ $selectedProduct['marca'] }}
                                    </span>
                                @endif
                                @if ($selectedProduct['codigo'])
                                    <span class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400 font-mono text-xs bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded-lg">
                                        #{{ $selectedProduct['codigo'] }}
                                    </span>
                                @endif
                            </div>

                            @if ($selectedProduct['descripcion'])
                                <div class="mt-6 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/80 dark:border-slate-700/80">
                                    <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                        {{ $selectedProduct['descripcion'] }}
                                    </p>
                                </div>
                            @endif

                            @if (count($selectedProduct['presentaciones'] ?? []) > 0)
                                <div class="mt-6">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Presentaciones disponibles</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($selectedProduct['presentaciones'] as $presentacion)
                                            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 shadow-sm">
                                                <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                {{ $presentacion['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
