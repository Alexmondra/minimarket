<section id="productos" class="border-y border-gray-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-[0.95fr_2.05fr]">
            <aside class="space-y-5">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Catalogo</p>
                    <h2 class="mt-2 text-2xl font-bold text-gray-950">Productos por sucursal</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Elige una sucursal para ver precios y disponibilidad actual.
                    </p>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <label for="public-sucursal" class="text-sm font-semibold text-gray-900">Sucursal</label>
                    <select
                        id="public-sucursal"
                        wire:model.live="sucursalId"
                        class="mt-2 h-11 w-full rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15"
                    >
                        @forelse ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}">{{ $sucursal->nombre_sucursal }}</option>
                        @empty
                            <option value="">Sin sucursales disponibles</option>
                        @endforelse
                    </select>

                    @if ($selectedSucursal?->direccion)
                        <p class="mt-2 text-xs leading-5 text-gray-500">{{ $selectedSucursal->direccion }}</p>
                    @endif
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <label for="public-search" class="text-sm font-semibold text-gray-900">Buscar</label>
                    <input
                        id="public-search"
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Nombre, codigo o descripcion"
                        class="mt-2 h-11 w-full rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15"
                    >

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <div>
                            <label for="public-marca" class="text-xs font-semibold uppercase tracking-wide text-gray-500">Marca</label>
                            <select
                                id="public-marca"
                                wire:model.live="marcaId"
                                class="mt-2 h-10 w-full rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-800 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15"
                            >
                                <option value="">Todas</option>
                                @foreach ($marcas as $marca)
                                    <option value="{{ $marca->id }}">{{ $marca->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <button
                                type="button"
                                wire:click="clearFilters"
                                class="mt-6 h-10 w-full rounded-md border border-gray-300 px-3 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50"
                            >
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-sm font-semibold text-gray-900">Categorias</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="selectCategoria(null)"
                            @class([
                                'rounded-md border px-3 py-2 text-xs font-semibold transition',
                                'border-emerald-600 bg-emerald-50 text-emerald-700' => $categoriaId === '',
                                'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50' => $categoriaId !== '',
                            ])
                        >
                            Todas
                        </button>

                        @foreach ($categorias as $categoria)
                            <button
                                type="button"
                                wire:click="selectCategoria({{ $categoria->id }})"
                                @class([
                                    'rounded-md border px-3 py-2 text-xs font-semibold transition',
                                    'border-emerald-600 bg-emerald-50 text-emerald-700' => $categoriaId === (string) $categoria->id,
                                    'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50' => $categoriaId !== (string) $categoria->id,
                                ])
                            >
                                {{ $categoria->nombre }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </aside>

            <div>
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-emerald-700">{{ $totalProductos }} productos encontrados</p>
                        <h3 class="mt-1 text-xl font-bold text-gray-950">Catalogo disponible</h3>
                    </div>
                    <p class="text-sm text-gray-500">
                        @if ($selectedSucursal)
                            {{ $selectedSucursal->nombre_sucursal }}
                        @else
                            Selecciona una sucursal
                        @endif
                    </p>
                </div>

                <div wire:loading.class="opacity-60" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($productos as $producto)
                        <article wire:key="public-product-{{ $producto['id'] }}" class="group overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                            <button type="button" wire:click="openProduct({{ $producto['id'] }})" class="block w-full text-left">
                                <div class="aspect-[4/3] bg-gray-100">
                                    @if ($producto['imagen'])
                                        <img src="{{ $producto['imagen'] }}" alt="{{ $producto['nombre'] }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#f7fee7,#ecfeff)] px-6 text-center">
                                            <span class="text-sm font-bold uppercase tracking-wide text-emerald-800">{{ $producto['categoria'] ?? 'Producto' }}</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-3 p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="line-clamp-2 text-base font-bold text-gray-950">{{ $producto['nombre'] }}</h4>
                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ $producto['marca'] ?? 'Sin marca' }}
                                                @if ($producto['codigo'])
                                                    <span class="text-gray-300">/</span> {{ $producto['codigo'] }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="shrink-0 rounded-md bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-800">
                                            {{ $producto['categoria'] ?? 'General' }}
                                        </span>
                                    </div>

                                    <div class="flex items-end justify-between gap-3 border-t border-gray-100 pt-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Precio</p>
                                            @if ($producto['precio_minimo'] !== null)
                                                <p class="text-lg font-black text-emerald-700">
                                                    S/ {{ number_format($producto['precio_minimo'], 2) }}
                                                    @if ($producto['precio_maximo'] !== null && $producto['precio_maximo'] > $producto['precio_minimo'])
                                                        <span class="text-sm font-semibold text-gray-500">- S/ {{ number_format($producto['precio_maximo'], 2) }}</span>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="text-sm font-semibold text-gray-500">Consultar</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Stock</p>
                                            <p @class([
                                                'text-sm font-bold',
                                                'text-emerald-700' => $producto['stock'] > 0,
                                                'text-gray-400' => $producto['stock'] <= 0,
                                            ])>
                                                {{ $producto['stock'] > 0 ? $producto['stock'] : 'Sin stock' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center sm:col-span-2 xl:col-span-3">
                            <p class="text-base font-semibold text-gray-800">Aun no hay productos para mostrar.</p>
                            <p class="mt-2 text-sm text-gray-500">Prueba con otra sucursal, categoria o busqueda.</p>
                        </div>
                    @endforelse
                </div>

                @if ($totalProductos > $productos->count())
                    <div class="mt-8 flex justify-center">
                        <button
                            type="button"
                            wire:click="loadMore"
                            wire:loading.attr="disabled"
                            class="rounded-md bg-gray-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-gray-800 disabled:cursor-wait disabled:opacity-60"
                        >
                            Ver mas productos
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($showProductModal && $selectedProduct)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/60 px-4 py-6 backdrop-blur-sm sm:py-10">
            <button type="button" wire:click="closeProduct" class="fixed inset-0 h-full w-full cursor-default" aria-label="Cerrar"></button>

            <article class="relative mx-auto grid max-w-5xl overflow-hidden rounded-lg bg-white shadow-2xl lg:grid-cols-[0.9fr_1.1fr]">
                <div class="bg-gray-100">
                    @if ($selectedProduct['imagen'])
                        <img src="{{ $selectedProduct['imagen'] }}" alt="{{ $selectedProduct['nombre'] }}" class="h-full min-h-72 w-full object-cover">
                    @else
                        <div class="flex h-full min-h-72 items-center justify-center bg-[linear-gradient(135deg,#f7fee7,#ecfeff)] px-8 text-center">
                            <span class="text-lg font-black uppercase tracking-wide text-emerald-800">{{ $selectedProduct['categoria'] ?? 'Producto' }}</span>
                        </div>
                    @endif
                </div>

                <div class="p-5 sm:p-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">{{ $selectedProduct['categoria'] ?? 'General' }}</p>
                            <h3 class="mt-2 text-2xl font-black text-gray-950">{{ $selectedProduct['nombre'] }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ $selectedProduct['marca'] ?? 'Sin marca' }}</p>
                        </div>

                        <button type="button" wire:click="closeProduct" class="rounded-md border border-gray-200 px-3 py-2 text-sm font-bold text-gray-600 transition hover:bg-gray-50">
                            Cerrar
                        </button>
                    </div>

                    @if ($selectedProduct['descripcion'])
                        <p class="mt-5 text-sm leading-6 text-gray-600">{{ $selectedProduct['descripcion'] }}</p>
                    @endif

                    <div class="mt-6 rounded-lg border border-gray-200">
                        <div class="border-b border-gray-200 px-4 py-3">
                            <p class="text-sm font-bold text-gray-950">Presentaciones y precios</p>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse ($selectedProduct['presentaciones'] as $presentacion)
                                <div class="grid gap-3 px-4 py-4 sm:grid-cols-[1fr_auto] sm:items-center">
                                    <div>
                                        <p class="font-semibold text-gray-950">{{ $presentacion['label'] }}</p>
                                        <p class="mt-1 text-xs text-gray-500">Stock: {{ $presentacion['stock'] > 0 ? $presentacion['stock'] : 'Sin stock' }}</p>
                                    </div>

                                    <div class="text-left sm:text-right">
                                        @if ($presentacion['precio_oferta'])
                                            <p class="text-xs font-semibold text-rose-600">Oferta S/ {{ number_format($presentacion['precio_oferta'], 2) }}</p>
                                        @endif

                                        @if ($presentacion['precio'] !== null)
                                            <p class="text-lg font-black text-emerald-700">S/ {{ number_format($presentacion['precio'], 2) }}</p>
                                        @else
                                            <p class="text-sm font-semibold text-gray-500">Consultar</p>
                                        @endif

                                        @if ($presentacion['precio_mayorista'])
                                            <p class="text-xs text-gray-500">Mayorista desde {{ $presentacion['minimo_mayorista'] }}: S/ {{ number_format($presentacion['precio_mayorista'], 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-4 py-6 text-sm text-gray-500">Sin presentaciones registradas.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </article>
        </div>
    @endif
</section>
