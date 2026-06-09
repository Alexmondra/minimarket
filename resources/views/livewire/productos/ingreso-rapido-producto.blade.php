<div class="space-y-5">
    @if ($ultimoProducto)
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">{{ $ultimoProducto }} quedo listo para vender</p>
                        <p class="mt-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">{{ $ultimoPresentacion ?: 'Presentacion' }} · Precio S/ {{ number_format((float) $ultimoPrecio, 2) }}</p>
                    </div>
                </div>
                <a href="{{ $this->urlVenta }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-black text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-500">Ir al punto de venta</a>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="guardar" class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_360px]">
        <div class="space-y-5">
            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-orange-500/20">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-black tracking-tight text-slate-950 dark:text-white">Buscar o registrar producto</h2>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Escanea codigo de barra o escribe el nombre del producto.</p>
                    </div>
                </div>

                <div class="relative">
                    <input type="text" wire:model.live.debounce.350ms="busqueda" wire:keydown.arrow-down.prevent="highlightDown" wire:keydown.arrow-up.prevent="highlightUp" wire:keydown.enter.prevent="highlightEnter" autofocus placeholder="Codigo de barra o nombre del producto" class="h-14 w-full rounded-2xl border border-slate-300 bg-slate-50 px-5 pr-12 text-base font-black text-slate-900 shadow-inner outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 placeholder:text-slate-400 dark:border-slate-600 dark:bg-slate-950 dark:text-white dark:placeholder:text-slate-500">
                    <div class="absolute inset-y-0 right-4 flex items-center text-slate-400 dark:text-slate-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h16.5M3.75 9h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5" /></svg>
                    </div>
                </div>
                @error('codigoBarra') <p class="mt-2 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror

                @if ($productoSearchResults)
                    <div class="mt-3 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-950">
                        @foreach ($productoSearchResults as $index => $producto)
                            <button type="button" wire:click="seleccionarProducto({{ $producto['id'] }})" class="flex w-full items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-b-0 hover:bg-amber-50 dark:border-slate-800 dark:hover:bg-amber-500/10 {{ $highlightedIndex === $index ? 'bg-amber-100 ring-2 ring-inset ring-amber-500/40 dark:bg-amber-500/20 dark:ring-amber-400/50' : '' }}">
                                <span>
                                    <span class="block text-sm font-black text-slate-900 dark:text-white">{{ $producto['nombre'] }}</span>
                                    <span class="mt-0.5 block text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $producto['codigo_interno'] ?: 'Sin codigo interno' }} · {{ $producto['presentaciones_count'] }} presentacion(es)</span>
                                </span>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Elegir</span>
                            </button>
                        @endforeach
                    </div>
                @elseif (filled($busqueda) && ! $productoExistenteId && mb_strlen(trim($busqueda)) >= 2)
                    <div class="mt-3 rounded-2xl border border-dashed border-emerald-300 bg-emerald-50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/10">
                        <p class="text-sm font-black text-emerald-900 dark:text-emerald-100">No se encontro. Se registrara como producto nuevo.</p>
                        <p class="mt-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">Completa categoria, marca y presentacion debajo.</p>
                    </div>
                @endif
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-black text-slate-950 dark:text-white">Producto y presentacion</h3>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Selecciona la presentacion exacta o crea una nueva.</p>
                    </div>
                    @if ($productoExistenteId)
                        <button type="button" wire:click="limpiarProductoSeleccionado" class="h-10 rounded-xl border border-slate-200 px-3 text-xs font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">Cambiar</button>
                    @endif
                </div>

                @if ($productoExistenteId)
                    <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-500/20 dark:bg-blue-500/10">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-black text-blue-900 dark:text-blue-100">{{ $productoExistenteNombre }}</p>
                                <p class="mt-1 text-xs font-semibold text-blue-700 dark:text-blue-300">Producto existente. Ya no necesitas categoria ni marca.</p>
                            </div>
                            <button type="button" wire:click="abrirDetalleProducto" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-white px-3 text-xs font-black text-blue-700 ring-1 ring-blue-200 transition hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-500/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                Ver producto
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="button" wire:click="$set('crearNuevaPresentacion', false)" class="rounded-2xl border p-4 text-left transition {{ ! $crearNuevaPresentacion ? 'border-blue-400 bg-blue-50 ring-4 ring-blue-500/10 dark:border-blue-500/50 dark:bg-blue-500/10' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:bg-slate-800' }}">
                            <span class="block text-sm font-black text-slate-900 dark:text-white">Usar presentacion existente</span>
                            <span class="mt-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">Para reponer stock de una presentacion ya creada.</span>
                        </button>
                        <button type="button" wire:click="$set('crearNuevaPresentacion', true)" class="rounded-2xl border p-4 text-left transition {{ $crearNuevaPresentacion ? 'border-emerald-400 bg-emerald-50 ring-4 ring-emerald-500/10 dark:border-emerald-500/50 dark:bg-emerald-500/10' : 'border-slate-200 bg-slate-50 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950/50 dark:hover:bg-slate-800' }}">
                            <span class="block text-sm font-black text-slate-900 dark:text-white">Agregar nueva presentacion</span>
                            <span class="mt-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">Ej. caja, paquete, six pack, botella.</span>
                        </button>
                    </div>

                    @if (! $crearNuevaPresentacion)
                        <div class="mt-4 flex gap-2">
                            <select wire:model.live="presentacionExistenteId" class="h-12 flex-1 rounded-2xl border border-blue-300 bg-white px-4 text-sm font-black text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 dark:border-blue-500/30 dark:bg-slate-950 dark:text-white">
                                @foreach ($presentacionesDisponibles as $presentacion)
                                    <option value="{{ $presentacion['id'] }}">{{ $presentacion['nombre'] }} @if($presentacion['barras']) · {{ implode(', ', $presentacion['barras']) }} @endif</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="verInfoPresentacion" class="h-12 w-12 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-lg shadow-blue-500/30 transition hover:scale-105 hover:from-sky-400 hover:to-blue-500" title="Info de presentacion">
                                <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </button>
                        </div>
                        @error('presentacionExistenteId') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    @endif
                @else
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Nombre del producto</label>
                            <input type="text" wire:model="nombre" placeholder="Ej. Coca Cola 3L" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
                            @error('nombre') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Categoria</label>
                            <select wire:model="categoriaId" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"><option value="">Sin categoria</option>@foreach ($this->categorias as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach</select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Crear categoria rapido</label>
                            <input type="text" wire:model="nuevaCategoria" placeholder="Opcional" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Marca</label>
                            <select wire:model="marcaId" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"><option value="">Sin marca</option>@foreach ($this->marcas as $marca)<option value="{{ $marca->id }}">{{ $marca->nombre }}</option>@endforeach</select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Crear marca rapido</label>
                            <input type="text" wire:model="nuevaMarca" placeholder="Opcional" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
                        </div>
                    </div>
                @endif

                @if (! $productoExistenteId || $crearNuevaPresentacion)
                    <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/50">
                        <p class="mb-4 text-sm font-black text-slate-900 dark:text-white">Datos de la presentacion</p>
                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Unidad</label>
                                <select wire:model="unidadMedidaId" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">@foreach ($this->unidades as $unidad)<option value="{{ $unidad->id }}">{{ $unidad->nombre }} ({{ $unidad->abreviatura }})</option>@endforeach</select>
                                @error('unidadMedidaId') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Como se vende</label>
                                <input type="text" wire:model="tipoPresentacion" placeholder="Unidad, Caja x 12, Six pack" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
                                @error('tipoPresentacion') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Codigo de barra</label>
                                <input type="text" wire:model="codigoBarra" placeholder="Opcional" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
                                @error('codigoBarra') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Cantidad por presentacion</label>
                                <input type="number" min="1" wire:model.live="cantidadPresentacion" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white">
                                @error('cantidadPresentacion') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            </div>
                            @if ((int) $cantidadPresentacion > 1)
                                <div>
                                    <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Presentacion base</label>
                                    <select wire:model="presentacionBaseId" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"><option value="">Sin base</option>@foreach ($productoExistenteId ? $presentacionesDisponibles : [] as $pres)<option value="{{ $pres['id'] }}">{{ $pres['nombre'] }}</option>@endforeach</select>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </section>

            <section class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">Stock y lote</h3>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">El costo unitario se calcula solo con el total pagado.</p>
                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Cantidad ingresada</label>
                            <input type="number" min="1" wire:model.live.debounce.250ms="cantidadIngreso" class="h-14 w-full rounded-2xl border border-emerald-300 bg-emerald-50 px-4 text-2xl font-black text-emerald-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                            @error('cantidadIngreso') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Total pagado por este ingreso</label>
                            <div class="flex h-12 items-center rounded-2xl border border-slate-300 bg-white px-4 focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50">
                                <span class="mr-2 text-sm font-black text-slate-400">S/</span>
                                <input type="number" min="0" step="0.01" wire:model.live.debounce.250ms="totalPagado" placeholder="0.00" class="w-full border-0 bg-transparent p-0 text-sm font-black text-slate-900 outline-none focus:ring-0 dark:text-white">
                            </div>
                            @error('totalPagado') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">Costo unitario calculado: S/ {{ number_format((float) ($precioCompra ?? 0), 4) }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Lote</label><input type="text" wire:model="codigoLote" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></div>
                            <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Vencimiento</label><input type="date" wire:model="fechaVencimiento" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></div>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Stock minimo</label><input type="number" min="0" wire:model="stockMinimo" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></div>
                            <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Ubicacion</label><input type="text" wire:model="ubicacion" placeholder="Estante, pasillo..." class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">Precio para vender</h3>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Solo define precios de venta para esta sucursal.</p>
                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Precio venta</label>
                            <div class="flex h-14 items-center rounded-2xl border border-amber-300 bg-amber-50 px-4 focus-within:border-amber-500 focus-within:ring-4 focus-within:ring-amber-500/10 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <span class="mr-2 text-sm font-black text-amber-600 dark:text-amber-300">S/</span>
                                <input type="number" min="0" step="0.01" wire:model.live.debounce.300ms="precioVenta" placeholder="0.00" class="w-full border-0 bg-transparent p-0 text-2xl font-black text-amber-800 outline-none focus:ring-0 dark:text-amber-200">
                            </div>
                            @error('precioVenta') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Mayorista</label><input type="number" min="0" step="0.01" wire:model="precioMayorista" placeholder="Opcional" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></div>
                            <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Min. mayorista</label><input type="number" min="1" wire:model="minimoMayorista" class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white"></div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/50">
                            <p class="text-xs font-black uppercase tracking-wide text-slate-400">Ganancia estimada</p>
                            <div class="mt-2 flex items-end justify-between gap-3"><p class="text-2xl font-black {{ ($this->margen ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">S/ {{ number_format((float) ($this->margen ?? 0), 2) }}</p><p class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">{{ $this->margenPorcentaje !== null ? $this->margenPorcentaje.'%' : 'Sin margen' }}</p></div>
                            <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Por unidad · {{ $this->cantidadIngreso }} und</p>
                            @if ($this->margenTotal !== null)
                                <div class="mt-3 flex items-end justify-between gap-3 border-t border-slate-200 pt-3 dark:border-slate-700"><p class="text-lg font-black text-emerald-600 dark:text-emerald-400">S/ {{ number_format((float) $this->margenTotal, 2) }}</p><p class="text-xs font-bold text-slate-500 dark:text-slate-400">Vendiendo todo</p></div>
                            @endif
                            @if ($this->margenTotalMayorista !== null)
                                <div class="mt-2 flex items-end justify-between gap-3"><p class="text-base font-black text-indigo-600 dark:text-indigo-400">S/ {{ number_format((float) $this->margenTotalMayorista, 2) }}</p><p class="text-xs font-bold text-slate-500 dark:text-slate-400">Al por mayor · {{ $this->margenPorcentajeMayorista !== null ? $this->margenPorcentajeMayorista.'%' : '' }}</p></div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="xl:sticky xl:top-6 xl:self-start">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/50 dark:bg-slate-950 sm:p-6">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400 dark:text-slate-500">Resumen</p>
                <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Guardar y vender</h3>
                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-900"><p class="text-xs font-bold text-slate-400 dark:text-slate-500">Producto</p><p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ $nombre ?: 'Aun sin nombre' }}</p><p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">{{ $tipoPresentacion ?: 'Presentacion' }} · {{ $cantidadPresentacion }} unidad(es)</p></div>
                    <div class="grid grid-cols-2 gap-3"><div class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-950/20 dark:border dark:border-emerald-900/30"><p class="text-xs font-bold text-emerald-600 dark:text-emerald-300">Stock</p><p class="mt-1 text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ $cantidadIngreso }}</p></div><div class="rounded-2xl bg-amber-50 p-4 dark:bg-amber-950/20 dark:border dark:border-amber-900/30"><p class="text-xs font-bold text-amber-600 dark:text-amber-300">Precio</p><p class="mt-1 text-2xl font-black text-amber-700 dark:text-amber-300">S/ {{ number_format((float) ($precioVenta ?? 0), 2) }}</p></div></div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700/50 dark:bg-slate-900/80"><div class="flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400"><span>Lote</span><span class="text-slate-900 dark:text-white">{{ $codigoLote ?: 'Automatico' }}</span></div><div class="mt-2 flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400"><span>Modo</span><span class="text-right text-slate-900 dark:text-white">{{ $productoExistenteId ? ($crearNuevaPresentacion ? 'Nueva presentacion' : 'Reposicion') : 'Nuevo producto' }}</span></div></div>
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="guardar" class="mt-5 inline-flex min-h-13 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-4 text-sm font-black text-white shadow-xl shadow-orange-500/20 transition hover:scale-[1.01] hover:from-amber-400 hover:to-orange-500 disabled:cursor-not-allowed disabled:opacity-70"><span wire:loading.remove wire:target="guardar">Guardar producto listo</span><span wire:loading.flex wire:target="guardar" class="items-center gap-2"><span class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>Guardando...</span></button>
                <button type="button" wire:click="limpiarFormulario" class="mt-3 inline-flex h-11 w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">Limpiar pantalla</button>
            </div>
        </aside>
    </form>

    @if ($showProductoModal)
        <div wire:click.self="cerrarProductoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900 sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div><p class="text-xs font-black uppercase tracking-wide text-blue-500">Detalle del producto</p><h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">{{ $modalNombre }}</h3></div>
                    <button type="button" wire:click="cerrarProductoModal" class="h-10 w-10 rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">×</button>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Nombre</label><input type="text" wire:model="modalNombre" @disabled(! $editandoProductoModal) class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white dark:disabled:bg-slate-800"></div>
                    <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Categoria</label><select wire:model="modalCategoriaId" @disabled(! $editandoProductoModal) class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white dark:disabled:bg-slate-800"><option value="">Sin categoria</option>@foreach ($this->categorias as $categoria)<option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>@endforeach</select></div>
                    <div><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Marca</label><select wire:model="modalMarcaId" @disabled(! $editandoProductoModal) class="h-12 w-full rounded-2xl border border-slate-300 bg-white px-4 text-sm font-bold text-slate-900 outline-none disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white dark:disabled:bg-slate-800"><option value="">Sin marca</option>@foreach ($this->marcas as $marca)<option value="{{ $marca->id }}">{{ $marca->nombre }}</option>@endforeach</select></div>
                    <div class="sm:col-span-2"><label class="mb-1.5 block text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Descripcion</label><textarea wire:model="modalDescripcion" @disabled(! $editandoProductoModal) rows="4" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-900 outline-none disabled:bg-slate-100 disabled:text-slate-500 dark:border-slate-700 dark:bg-slate-950/50 dark:text-white dark:disabled:bg-slate-800" placeholder="Sin descripcion"></textarea></div>
                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 text-sm font-black text-slate-700 dark:border-slate-800 dark:text-slate-300"><input type="checkbox" wire:model="modalAfectoIgv" @disabled(! $editandoProductoModal) class="rounded border-slate-300 text-amber-600 focus:ring-amber-500"> Afecto a IGV</label>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarProductoModal" class="h-11 rounded-xl border border-slate-200 px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800">Cancelar</button>
                    @if ($editandoProductoModal)
                        <button type="button" wire:click="guardarProductoModal" class="h-11 rounded-xl bg-blue-600 px-5 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-500">Guardar cambios</button>
                    @else
                        <button type="button" wire:click="editarProductoModal" class="h-11 rounded-xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-900/10 transition hover:bg-slate-800 dark:bg-white dark:text-slate-950">Editar</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($showPresentacionInfoModal && $presentacionInfoData)
        <div wire:click.self="cerrarPresentacionInfoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-700 dark:bg-slate-900 sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-wide text-sky-500">Info de presentacion</p>
                        <h3 class="mt-1 text-xl font-black text-slate-950 dark:text-white">{{ $presentacionInfoData['tipo'] }}</h3>
                    </div>
                    <button type="button" wire:click="cerrarPresentacionInfoModal" class="h-10 w-10 rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">×</button>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between rounded-2xl bg-sky-50 p-4 dark:bg-sky-950/40"><span class="text-xs font-black uppercase tracking-wide text-sky-600 dark:text-sky-400">Tipo</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $presentacionInfoData['tipo'] }}</span></div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 dark:bg-slate-900"><span class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Unidad</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $presentacionInfoData['unidad'] }}</span></div>
                    <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 dark:bg-slate-900"><span class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Cantidad</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ $presentacionInfoData['cantidad'] }}</span></div>
                    @if (count($presentacionInfoData['barras']) > 0)
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4 dark:bg-slate-900"><span class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-slate-400">Codigos de barra</span><span class="text-sm font-black text-slate-900 dark:text-white">{{ implode(', ', $presentacionInfoData['barras']) }}</span></div>
                    @endif
                    <div class="flex items-center justify-between rounded-2xl bg-amber-50 p-4 dark:bg-amber-950/40"><span class="text-xs font-black uppercase tracking-wide text-amber-600 dark:text-amber-400">Precio venta</span><span class="text-sm font-black text-amber-800 dark:text-amber-200">S/ {{ number_format((float) ($presentacionInfoData['precio'] ?? 0), 2) }}</span></div>
                    @if ($presentacionInfoData['precio_mayorista'])
                        <div class="flex items-center justify-between rounded-2xl bg-indigo-50 p-4 dark:bg-indigo-950/40"><span class="text-xs font-black uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Mayorista</span><span class="text-sm font-black text-indigo-800 dark:text-indigo-200">S/ {{ number_format((float) $presentacionInfoData['precio_mayorista'], 2) }}</span></div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" wire:click="cerrarPresentacionInfoModal" class="h-11 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-6 text-sm font-black text-white shadow-lg shadow-blue-500/20 transition hover:from-sky-400 hover:to-blue-500">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>
