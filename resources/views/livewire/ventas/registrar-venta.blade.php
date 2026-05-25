@php($resumen = $this->resumen)

<div class="pos-viewport {{ $posTheme === 'light' ? 'pos-light' : 'pos-dark' }} min-h-screen font-sans antialiased transition-all duration-300">
    <style>
        /* Scoped style variables for local theme support without relying on global Tailwind dark mode */
        .pos-light {
            --pos-bg: #f1f5f9;
            --pos-card-bg: #ffffff;
            --pos-card-border: #e2e8f0;
            --pos-text-main: #0f172a;
            --pos-text-muted: #475569;
            --pos-topbar-bg: #1e3a8a;
            --pos-footer-bg: #0f172a;
            --pos-input-bg: #ffffff;
            --pos-input-border: #cbd5e1;
            --pos-hover-bg: #f8fafc;
            --pos-active-category-bg: #2563eb;
            --pos-active-category-text: #ffffff;
            --pos-table-row-hover: #f8fafc;
            --pos-bill-bg: #f8fafc;
        }

        .pos-dark {
            --pos-bg: #090d16;
            --pos-card-bg: #111827;
            --pos-card-border: #1f2937;
            --pos-text-main: #f3f4f6;
            --pos-text-muted: #9ca3af;
            --pos-topbar-bg: #030712;
            --pos-footer-bg: #030712;
            --pos-input-bg: #030712;
            --pos-input-border: #374151;
            --pos-hover-bg: #1f2937;
            --pos-active-category-bg: #10b981;
            --pos-active-category-text: #030712;
            --pos-table-row-hover: #1f2937;
            --pos-bill-bg: #030712;
        }

        .pos-viewport {
            position: fixed !important;
            inset: 0 !important;
            z-index: 9900 !important;
            overflow-y: auto !important;
            background-color: var(--pos-bg) !important;
            color: var(--pos-text-main) !important;
        }

        .pos-viewport .pos-card {
            background-color: var(--pos-card-bg) !important;
            border: 1px solid var(--pos-card-border) !important;
            color: var(--pos-text-main) !important;
        }

        .pos-viewport .pos-input,
        .pos-viewport .pos-select {
            background-color: var(--pos-input-bg) !important;
            border: 1px solid var(--pos-input-border) !important;
            color: var(--pos-text-main) !important;
        }

        .pos-viewport .pos-text {
            color: var(--pos-text-main) !important;
        }

        .pos-viewport .pos-text-muted {
            color: var(--pos-text-muted) !important;
        }

        .pos-viewport .pos-border {
            border-color: var(--pos-card-border) !important;
        }

        .pos-viewport .pos-hoverable:hover {
            background-color: var(--pos-hover-bg) !important;
        }

        .pos-viewport .pos-table-row:hover {
            background-color: var(--pos-table-row-hover) !important;
        }

        .pos-viewport .pos-active-category {
            background-color: var(--pos-active-category-bg) !important;
            color: var(--pos-active-category-text) !important;
        }

        /* Payment methods styling */
        .pos-viewport .pos-active-payment-efectivo {
            border-color: #10b981 !important;
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }
        .pos-viewport .pos-active-payment-tarjeta {
            border-color: #3b82f6 !important;
            background-color: rgba(59, 130, 246, 0.1) !important;
            color: #3b82f6 !important;
        }
        .pos-viewport .pos-active-payment-yape {
            border-color: #a855f7 !important;
            background-color: rgba(168, 85, 247, 0.1) !important;
            color: #a855f7 !important;
        }
        .pos-viewport .pos-active-payment-plin {
            border-color: #06b6d4 !important;
            background-color: rgba(6, 182, 212, 0.1) !important;
            color: #06b6d4 !important;
        }
        .pos-viewport .pos-active-payment-transf {
            border-color: #eab308 !important;
            background-color: rgba(234, 179, 8, 0.1) !important;
            color: #eab308 !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .pos-viewport.pos-dark ::-webkit-scrollbar-thumb {
            background: #374151;
        }
    </style>

    <!-- 1. Top Navigation Bar -->
    <header class="flex items-center justify-between px-6 py-3 text-white transition-all duration-300" style="background-color: var(--pos-topbar-bg);">
        <!-- Title & Icon -->
        <div class="flex items-center gap-3">
            <a href="/admin/documentos" class="p-2 rounded-xl bg-blue-950/40 border border-blue-800/60 hover:bg-blue-900 transition text-blue-200" title="Volver al Panel">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="p-2 bg-blue-600 rounded-xl text-white shadow">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.35 2.7M17 13l1.25 2.5M12 21a2 2 0 100-4 2 2 0 000 4zm7 0a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
            </div>
            <span class="text-xl font-extrabold tracking-tight">POS Minimarket</span>
        </div>

        <!-- System Stats (Caja, Fecha, Hora) -->
        <div class="hidden md:flex items-center gap-8 text-sm text-blue-100/90">
            <!-- Caja -->
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>Caja: <strong class="text-white">01</strong></span>
            </div>

            <!-- Fecha -->
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                </svg>
                <span id="pos-live-date">--/--/----</span>
            </div>

            <!-- Hora -->
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="pos-live-time" class="font-mono">--:--:-- --</span>
            </div>
        </div>

        <!-- Theme Switch & Cajero Info -->
        <div class="flex items-center gap-4">
            <!-- Theme Toggle Button -->
            <button 
                type="button" 
                wire:click="toggleTheme" 
                class="p-2 rounded-xl bg-blue-950/40 border border-blue-800/60 hover:bg-blue-900 transition text-blue-200"
                title="Cambiar Tema"
            >
                @if($posTheme === 'light')
                    <!-- Moon Icon -->
                    <svg class="h-5 w-5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                @else
                    <!-- Sun Icon -->
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464-4.95a1 1 0 111.414 1.414L14.14 5.636a1 1 0 11-1.414-1.414l.793-.793zm-9 0a1 1 0 011.414 0l.793.793a1 1 0 11-1.414 1.414L4.536 5.636a1 1 0 010-1.414zm12.728 9.9a1 1 0 010 1.414l-.793.793a1 1 0 11-1.414-1.414l.793-.793a1 1 0 011.414 0zm-12.728 0a1 1 0 011.414-1.414l.793.793a1 1 0 11-1.414 1.414l-.793-.793zm11.728-3.9a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zm-13 0a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                @endif
            </button>

            <!-- User Dropdown (Avatar) -->
            <div class="flex items-center gap-2.5 border-l border-blue-800/60 pl-4">
                <div class="h-9 w-9 rounded-full bg-blue-600 border-2 border-white flex items-center justify-center font-bold text-white text-sm shadow">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="hidden sm:block text-left text-xs">
                    <p class="font-bold leading-tight text-white">{{ Auth::user()->name }}</p>
                    <p class="text-blue-200 leading-none">Cajero</p>
                </div>
            </div>
        </div>
    </header>

    <!-- 2. Main Content Body (Grid cols 12) -->
    <main class="p-5">
        <div class="grid grid-cols-12 gap-5 items-start">
            
            <!-- Column 1: CATEGORIES (Col span: 2) -->
            <div class="col-span-12 lg:col-span-2 space-y-4">
                <div class="pos-card p-4">
                    <span class="text-xs font-black uppercase tracking-wider pos-text-muted block mb-3">Categorías</span>
                    <nav class="space-y-1.5">
                        <!-- Todos button -->
                        <button 
                            type="button" 
                            wire:click="seleccionarCategoria(null)"
                            class="w-full px-3.5 py-3 rounded-xl flex items-center gap-3 text-sm font-bold transition-all duration-150 {{ is_null($selectedCategoriaId) ? 'pos-active-category shadow-md' : 'pos-text-muted pos-hoverable' }}"
                        >
                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Todos</span>
                        </button>

                        @foreach($categorias as $cat)
                            <button 
                                type="button" 
                                wire:click="seleccionarCategoria({{ $cat['id'] }})"
                                class="w-full px-3.5 py-3 rounded-xl flex items-center gap-3 text-sm font-bold transition-all duration-150 {{ $selectedCategoriaId === $cat['id'] ? 'pos-active-category shadow-md' : 'pos-text-muted pos-hoverable' }}"
                            >
                                @if(stripos($cat['nombre'], 'bebida') !== false)
                                    🥤
                                @elseif(stripos($cat['nombre'], 'lacteo') !== false || stripos($cat['nombre'], 'lácteo') !== false)
                                    🥛
                                @elseif(stripos($cat['nombre'], 'pan') !== false)
                                    🍞
                                @elseif(stripos($cat['nombre'], 'snack') !== false)
                                    🍟
                                @elseif(stripos($cat['nombre'], 'huevo') !== false)
                                    🥚
                                @elseif(stripos($cat['nombre'], 'limpieza') !== false)
                                    🧼
                                @else
                                    📦
                                @endif
                                <span>{{ $cat['nombre'] }}</span>
                            </button>
                        @endforeach
                    </nav>
                </div>
            </div>

            <!-- Column 2: Search & Cart (Col span: 4) -->
            <div class="col-span-12 lg:col-span-4 space-y-4">
                
                <!-- Search bar & Results -->
                <div class="pos-card p-4 flex gap-3 items-center">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.250ms="searchProducto"
                            placeholder="Buscar producto por código, nombre..."
                            class="w-full rounded-xl py-3 pl-11 pr-10 text-sm font-semibold pos-input focus:outline-none transition-all duration-150"
                        >
                        @if(strlen($searchProducto) >= 2)
                            <button 
                                type="button" 
                                wire:click="$set('searchProducto', '')"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-450 hover:text-white"
                            >
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif

                        <!-- Product Search Dropdown Results inside relative container to lock width -->
                        @if($showProductoDropdown)
                            <div class="absolute left-0 right-0 z-50 mt-2 max-h-80 overflow-y-auto rounded-xl border border-slate-750 bg-slate-900/95 dark:bg-slate-950/95 shadow-2xl backdrop-blur-md divide-y divide-slate-800 w-full">
                                @foreach($productosResultados as $producto)
                                    <button 
                                        type="button" 
                                        wire:click="agregarProducto({{ $producto['producto_presentacion_id'] }})"
                                        class="w-full px-4 py-3 text-left hover:bg-emerald-500/10 transition duration-150 flex items-center justify-between gap-4 text-white"
                                    >
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-bold text-white">{{ $producto['nombre'] }}</span>
                                                <span class="rounded bg-slate-800 px-1.5 py-0.2 text-[9px] font-bold text-slate-300">
                                                    {{ $producto['presentacion'] }}
                                                </span>
                                            </div>
                                            <div class="text-[11px] text-slate-400">
                                                Cod: {{ $producto['codigo'] }} | Stock: {{ number_format($producto['stock'], 0) }}
                                            </div>
                                        </div>
                                        <div class="rounded-lg bg-emerald-500/15 border border-emerald-500/30 px-3 py-1 text-sm font-black text-emerald-400">
                                            S/ {{ number_format($producto['precio'], 2) }}
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <!-- Mock Barcode Scanner Button -->
                    <button type="button" class="p-3 bg-slate-200/50 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 border pos-border rounded-xl text-slate-500 dark:text-slate-300 focus:outline-none transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m-8 4v8m0-8h8m-8 8h8M4 12h16m0 0v-4m0 4v4m0-8h-4" />
                        </svg>
                    </button>
                </div>

                <!-- Catalog Grid (Shown if Category Filter is active) -->
                @if(!is_null($selectedCategoriaId))
                    <div class="pos-card p-4 space-y-3">
                        <div class="flex items-center justify-between border-b pos-border pb-2">
                            <span class="text-xs font-black uppercase tracking-wider pos-text-muted">
                                Catálogo: {{ \App\Models\Categoria::find($selectedCategoriaId)?->nombre }}
                            </span>
                            <button 
                                type="button" 
                                wire:click="seleccionarCategoria(null)" 
                                class="text-xs text-blue-500 hover:text-blue-600 font-bold"
                            >
                                Cerrar catálogo
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5 max-h-56 overflow-y-auto pr-1">
                            @forelse($this->productosCategoria as $prod)
                                <button 
                                    type="button"
                                    wire:click="agregarProducto({{ $prod['producto_presentacion_id'] }})"
                                    class="p-2.5 rounded-xl border pos-border bg-slate-100/40 dark:bg-slate-900/40 hover:border-blue-500 dark:hover:border-emerald-500 transition text-left flex flex-col justify-between h-24"
                                >
                                    <div>
                                        <h3 class="text-xs font-bold leading-tight line-clamp-2 pos-text">{{ $prod['nombre'] }}</h3>
                                    </div>
                                    <div class="flex justify-between items-end mt-1">
                                        <span class="text-[9px] font-bold {{ $prod['stock'] > 10 ? 'text-emerald-500' : 'text-amber-500' }}">
                                            Stock: {{ number_format($prod['stock'], 0) }}
                                        </span>
                                        <span class="text-xs font-extrabold text-blue-600 dark:text-emerald-400">S/ {{ number_format($prod['precio'], 2) }}</span>
                                    </div>
                                </button>
                            @empty
                                <div class="col-span-2 text-center text-xs pos-text-muted py-6">
                                    No hay productos en esta categoría.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                <!-- Cart Table Card -->
                <div class="pos-card p-5 space-y-4">
                    <span class="text-xs font-black uppercase tracking-wider pos-text-muted block">Productos en el Carrito</span>
                    
                    <div class="overflow-y-auto max-h-[26rem] pr-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b pos-border text-[10px] font-black uppercase tracking-wider pos-text-muted">
                                    <th class="pb-2">Producto</th>
                                    <th class="pb-2 text-center w-20">Cant.</th>
                                    <th class="pb-2 text-right w-16">P. Unit</th>
                                    <th class="pb-2 text-right w-16">Subtotal</th>
                                    <th class="pb-2 text-center w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                @forelse($cartItems as $index => $item)
                                    <tr class="align-middle pos-table-row transition-all duration-150">
                                        <!-- Product info with mock icon -->
                                        <td class="py-2.5 pr-2">
                                            <div class="flex items-center gap-2">
                                                <!-- Mock Product Icon -->
                                                <div class="h-9 w-9 rounded-lg bg-slate-100 dark:bg-slate-900 border pos-border flex items-center justify-center text-base shrink-0 shadow-inner">
                                                    @if(stripos($item['nombre'], 'agua') !== false)
                                                        🥤
                                                    @elseif(stripos($item['nombre'], 'gaseosa') !== false || stripos($item['nombre'], 'cola') !== false)
                                                        🥤
                                                    @elseif(stripos($item['nombre'], 'pan') !== false)
                                                        🍞
                                                    @elseif(stripos($item['nombre'], 'leche') !== false)
                                                        🥛
                                                    @elseif(stripos($item['nombre'], 'snack') !== false || stripos($item['nombre'], 'papa') !== false)
                                                        🍟
                                                    @elseif(stripos($item['nombre'], 'arroz') !== false)
                                                        🌾
                                                    @elseif(stripos($item['nombre'], 'huevo') !== false)
                                                        🥚
                                                    @else
                                                        📦
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="font-bold text-xs block leading-tight truncate pos-text">{{ $item['nombre'] }}</span>
                                                    <span class="text-[10px] pos-text-muted block mt-0.5">{{ $item['presentacion'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Quantity adjustments -->
                                        <td class="py-2.5 text-center">
                                            <div class="flex items-center justify-center bg-slate-100/80 dark:bg-slate-900/60 border pos-border rounded-lg p-0.5 w-18 mx-auto">
                                                <button 
                                                    type="button" 
                                                    wire:click="decrementarCantidad({{ $index }})" 
                                                    class="h-5 w-5 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 rounded flex items-center justify-center focus:outline-none"
                                                >
                                                    -
                                                </button>
                                                <input 
                                                    type="text" 
                                                    value="{{ $item['cantidad'] }}"
                                                    wire:change="actualizarCantidad({{ $index }}, $event.target.value)"
                                                    class="w-6 text-center bg-transparent border-0 p-0 text-xs font-bold focus:ring-0 focus:outline-none pos-text"
                                                >
                                                <button 
                                                    type="button" 
                                                    wire:click="incrementarCantidad({{ $index }})" 
                                                    class="h-5 w-5 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-500 dark:text-slate-400 rounded flex items-center justify-center focus:outline-none"
                                                >
                                                    +
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Unit Price -->
                                        <td class="py-2.5 text-right text-xs font-semibold pos-text">
                                            S/{{ number_format($item['precio'], 2) }}
                                        </td>

                                        <!-- Line Total -->
                                        <td class="py-2.5 text-right text-xs font-extrabold text-blue-600 dark:text-emerald-400">
                                            S/{{ number_format($item['cantidad'] * $item['precio'], 2) }}
                                        </td>

                                        <!-- Remove item -->
                                        <td class="py-2.5 text-center">
                                            <button 
                                                type="button" 
                                                wire:click="quitarItem({{ $index }})"
                                                class="text-rose-500 hover:text-rose-600 p-1 hover:bg-rose-500/10 rounded-lg transition"
                                                title="Eliminar"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center pos-text-muted">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg class="h-10 w-10 text-slate-350 dark:text-slate-650" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                <p class="text-sm font-semibold pos-text">Carrito Vacío</p>
                                                <p class="text-[11px] pos-text-muted max-w-xs">Usa el buscador o las categorías para empezar.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Cart Footer actions -->
                    <div class="flex items-center justify-between border-t pos-border pt-3">
                        <button 
                            type="button" 
                            wire:click="vaciarCarrito" 
                            @disabled(empty($cartItems))
                            class="px-3.5 py-1.5 border border-rose-500/30 hover:border-rose-500 text-rose-500 hover:bg-rose-500/5 text-xs font-bold uppercase rounded-lg transition disabled:opacity-40"
                        >
                            Vaciar Carrito
                        </button>
                        <span class="text-xs font-bold pos-text-muted">
                            {{ count($cartItems) }} productos
                        </span>
                    </div>
                </div>
            </div>

            <!-- Column 3: Cliente, Comprobante & Medio de Pago (Col span: 3) -->
            <div class="col-span-12 lg:col-span-3 space-y-4">
                
                <!-- Cliente Card -->
                <div class="pos-card p-4 space-y-3">
                    <div class="flex items-center gap-2 text-blue-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wide">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Cliente</span>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        @if($clienteTipoDocumento === 'RUC')
                            <div>
                                <label class="block font-semibold pos-text-muted mb-1">Razón Social</label>
                                <input type="text" wire:model="clienteRazonSocial" class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" placeholder="Ingresa razón social">
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block font-semibold pos-text-muted mb-1">Nombre</label>
                                    <input type="text" wire:model="clienteNombre" class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" placeholder="Nombre">
                                </div>
                                <div>
                                    <label class="block font-semibold pos-text-muted mb-1">Apellido</label>
                                    <input type="text" wire:model="clienteApellido" class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" placeholder="Apellido">
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold pos-text-muted mb-1">DNI/RUC</label>
                                <div class="flex">
                                    <select 
                                        wire:model.live="clienteTipoDocumento" 
                                        class="rounded-l-xl border-r-0 border-slate-350 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-[10px] font-bold pos-text-muted py-1.5 px-2 focus:ring-0 focus:outline-none"
                                    >
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="CE">CE</option>
                                    </select>
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.400ms="clienteDocumento" 
                                        class="w-full rounded-r-xl border border-slate-350 dark:border-slate-600 bg-transparent py-1.5 px-2 font-bold focus:outline-none text-[10px] pos-text"
                                        placeholder="Documento"
                                    >
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold pos-text-muted mb-1">Teléfono</label>
                                <input type="text" wire:model="clienteTelefono" class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" placeholder="Teléfono">
                            </div>
                        </div>
                    </div>

                    <!-- Client Action Buttons -->
                    <div class="grid grid-cols-2 gap-2 pt-1.5">
                        <button type="button" wire:click="updatedClienteDocumento" class="border border-green-600 text-green-600 hover:bg-green-500/5 py-2 px-1 rounded-lg text-[10px] font-bold flex items-center justify-center gap-1 transition focus:outline-none">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Buscar
                        </button>
                        <button 
                            type="button" 
                            wire:click="agregarCliente"
                            wire:loading.attr="disabled"
                            class="bg-green-600 hover:bg-green-500 text-white py-2 px-1 rounded-lg text-[10px] font-bold flex items-center justify-center gap-1 transition focus:outline-none"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Agregar
                        </button>
                    </div>

                    <!-- Loyalty points (Gold) -->
                    @if($clienteId && $puntosDisponibles > 0)
                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-3.5 flex justify-between items-center mt-2">
                            <div class="text-xs">
                                <p class="font-bold text-amber-500 leading-tight">Puntos: {{ $puntosDisponibles }}</p>
                                <p class="text-[9px] pos-text-muted mt-0.5">Canjear por descuento</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <input type="checkbox" wire:model.live="usarPuntos" id="usarPuntosChk" class="rounded border-amber-500 text-amber-500 focus:ring-amber-500 bg-transparent">
                                <label for="usarPuntosChk" class="text-xs font-bold uppercase text-amber-500 cursor-pointer">Usar</label>
                            </div>
                        </div>
                        @if($usarPuntos)
                            <div class="mt-2 text-xs space-y-1.5">
                                <input type="number" wire:model.live="puntosCanjear" class="w-full pos-input rounded-lg py-1.5 px-2 text-xs">
                                <p class="text-[10px] text-amber-400">Descuento: -S/ {{ number_format(app(\App\Support\Ventas\PuntosService::class)->descuentoPorPuntos($puntosCanjear), 2) }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Comprobante Card -->
                <div class="pos-card p-4 space-y-3">
                    <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 font-bold text-xs uppercase tracking-wide">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Comprobante</span>
                    </div>
                    <div>
                        <label class="block font-semibold pos-text-muted text-xs mb-1">Tipo de comprobante</label>
                        <select 
                            wire:model.live="tipoComprobante" 
                            wire:change="cambiarTipoComprobante($event.target.value)"
                            class="w-full rounded-xl py-2.5 px-3 text-xs font-bold pos-select focus:outline-none"
                        >
                            <option value="TICKET">Ticket de Venta</option>
                            <option value="BOLETA">Boleta de Venta</option>
                            <option value="FACTURA">Factura de Venta</option>
                        </select>
                    </div>
                </div>

                <!-- Método de Pago Card -->
                <div class="pos-card p-4 space-y-3">
                    <div class="flex items-center gap-2 text-blue-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wide">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span>Método de Pago</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <!-- Efectivo -->
                        <button 
                            type="button"
                            wire:click="cambiarMedioPago('EFECTIVO')"
                            class="py-2.5 px-1 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center focus:outline-none {{ $medioPago === 'EFECTIVO' ? 'pos-active-payment-efectivo font-bold' : 'pos-border pos-hoverable pos-text-muted' }}"
                        >
                            <span class="text-lg">💵</span>
                            <span class="text-[9px]">Efectivo</span>
                        </button>

                        <!-- Tarjeta -->
                        <button 
                            type="button"
                            wire:click="cambiarMedioPago('TARJETA')"
                            class="py-2.5 px-1 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center focus:outline-none {{ $medioPago === 'TARJETA' ? 'pos-active-payment-tarjeta font-bold' : 'pos-border pos-hoverable pos-text-muted' }}"
                        >
                            <span class="text-lg">💳</span>
                            <span class="text-[9px]">Tarjeta</span>
                        </button>

                        <!-- QR -->
                        <button 
                            type="button"
                            wire:click="cambiarMedioPago('YAPE')"
                            class="py-2.5 px-1 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center focus:outline-none {{ $medioPago === 'YAPE' ? 'pos-active-payment-yape font-bold' : 'pos-border pos-hoverable pos-text-muted' }}"
                        >
                            <span class="text-lg">📱</span>
                            <span class="text-[9px]">Yape / QR</span>
                        </button>

                        <!-- Transferencia -->
                        <button 
                            type="button"
                            wire:click="cambiarMedioPago('TRANSFERENCIA')"
                            class="py-2.5 px-1 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center focus:outline-none {{ $medioPago === 'TRANSFERENCIA' ? 'pos-active-payment-transf font-bold' : 'pos-border pos-hoverable pos-text-muted' }}"
                        >
                            <span class="text-lg">🏦</span>
                            <span class="text-[9px]">Transf.</span>
                        </button>

                        <!-- Billetera digital -->
                        <button 
                            type="button"
                            wire:click="cambiarMedioPago('PLIN')"
                            class="py-2.5 px-1 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center focus:outline-none {{ $medioPago === 'PLIN' ? 'pos-active-payment-plin font-bold' : 'pos-border pos-hoverable pos-text-muted' }}"
                        >
                            <span class="text-lg">💼</span>
                            <span class="text-[9px]">Plin / Wallet</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Column 4: Cajero image and Resumen (Col span: 3) -->
            <div class="col-span-12 lg:col-span-3 space-y-4">
                
                <!-- Cashier Mock Graphic Card -->
                <div class="pos-card overflow-hidden h-36 flex items-center justify-center relative bg-gradient-to-br from-blue-700 to-indigo-900 border-none shadow-lg">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_white_10%,_transparent_60%)]"></div>
                    <div class="text-center text-white z-10 space-y-1">
                        <svg class="h-9 w-9 mx-auto text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h4 class="font-black text-xs tracking-wider uppercase">Terminal de Minimarket</h4>
                        <p class="text-[9px] text-blue-200 leading-none">Cajero: {{ Auth::user()->name }}</p>
                    </div>
                </div>

                <!-- Resumen de Venta Card -->
                <div class="pos-card p-5 space-y-4 border-l-4 border-l-amber-500">
                    <span class="text-xs font-black uppercase tracking-wider pos-text-muted block">Resumen de Venta</span>
                    
                    <div class="space-y-2 text-xs font-bold">
                        <div class="flex justify-between pos-text-muted">
                            <span>Subtotal</span>
                            <span class="pos-text">S/ {{ number_format($resumen['totales']['total_bruto'], 2) }}</span>
                        </div>
                        @if($resumen['totales']['total_descuento'] > 0)
                            <div class="flex justify-between text-rose-500">
                                <span>Descuento</span>
                                <span>- S/ {{ number_format($resumen['totales']['total_descuento'], 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pos-text-muted">
                            <span>IGV (18%)</span>
                            <span class="pos-text">S/ {{ number_format($resumen['totales']['total_igv'], 2) }}</span>
                        </div>
                    </div>

                    <div class="border-t pos-border pt-3">
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="text-xs font-extrabold uppercase tracking-wide pos-text-muted">Total a Pagar</span>
                            <span class="text-2xl font-black text-amber-500 font-mono">S/ {{ number_format($resumen['totales']['total_neto'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Payment details -->
                    <div class="border-t pos-border pt-3 space-y-2.5">
                        @if($medioPago === 'EFECTIVO')
                            <div>
                                <label class="block font-semibold pos-text-muted text-[10px] mb-1">Monto recibido</label>
                                <div class="relative rounded-xl shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-bold">S/</div>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        wire:model.live="montoRecibido"
                                        class="w-full pos-input rounded-xl py-2 pl-8 pr-3 text-xs font-bold focus:outline-none"
                                        placeholder="0.00"
                                    >
                                </div>
                            </div>

                            <!-- Cash Shortcuts Grid -->
                            <div class="grid grid-cols-3 gap-1.5 pt-0.5">
                                <button 
                                    type="button" 
                                    wire:click="establecerPagoExacto"
                                    class="py-1 px-1 rounded-lg border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-[10px] font-bold text-center transition focus:outline-none"
                                >
                                    Exacto
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="agregarMontoEfectivo(10)"
                                    class="py-1 px-1 rounded-lg border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-[10px] font-bold text-center transition focus:outline-none"
                                >
                                    +10
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="agregarMontoEfectivo(20)"
                                    class="py-1 px-1 rounded-lg border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-[10px] font-bold text-center transition focus:outline-none"
                                >
                                    +20
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="agregarMontoEfectivo(50)"
                                    class="py-1 px-1 rounded-lg border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-[10px] font-bold text-center transition focus:outline-none"
                                >
                                    +50
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="agregarMontoEfectivo(100)"
                                    class="py-1 px-1 rounded-lg border pos-border hover:bg-slate-100 dark:hover:bg-slate-800 text-[10px] font-bold text-center transition focus:outline-none"
                                >
                                    +100
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="$set('montoRecibido', 0)"
                                    class="py-1 px-1 rounded-lg border border-rose-500/20 text-rose-500 hover:bg-rose-500/5 text-[10px] font-bold text-center transition focus:outline-none"
                                >
                                    Limpiar
                                </button>
                            </div>
                            
                            @php($vueltoCalculado = ((float) $montoRecibido) - $resumen['totales']['total_neto'])
                            <div class="flex justify-between items-center text-xs font-bold pt-1">
                                <span class="pos-text-muted">Vuelto</span>
                                @if($vueltoCalculado >= 0)
                                    <span class="text-emerald-500 font-mono text-sm">S/ {{ number_format($vueltoCalculado, 2) }}</span>
                                @else
                                    <span class="text-rose-500 font-mono text-sm">Falta S/ {{ number_format(abs($vueltoCalculado), 2) }}</span>
                                @endif
                            </div>
                        @else
                            <div class="py-2.5 rounded-xl text-center border pos-border bg-slate-50 dark:bg-slate-900/60">
                                <span class="text-[9px] font-bold pos-text-muted block uppercase">Pago Electrónico</span>
                                <span class="text-sm font-black text-emerald-500 font-mono block mt-1">S/ {{ number_format($resumen['totales']['total_neto'], 2) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- 3. Bottom Controls Panel Bar -->
    <div class="px-6 py-4 border-t pos-border flex flex-wrap gap-4 items-center justify-between" style="background-color: var(--pos-hover-bg);">
        <!-- Left: Action buttons (Imprimir, Guardar) -->
        <div class="flex items-center gap-3">
            <button 
                type="button" 
                class="px-5 py-3 rounded-xl border border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-800 hover:bg-blue-600/5 font-bold text-sm flex items-center gap-2 transition focus:outline-none"
            >
                🖨️
                <span>Imprimir</span>
            </button>
            <button 
                type="button" 
                wire:click="guardarVenta"
                wire:loading.attr="disabled"
                @disabled(!$this->canSave)
                class="px-5 py-3 rounded-xl border border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-800 hover:bg-blue-600/5 font-bold text-sm flex items-center gap-2 transition focus:outline-none disabled:opacity-55"
            >
                💾
                <span>Guardar venta</span>
            </button>
        </div>

        <!-- Right: Cobrar (Submit) & Cancelar -->
        <div class="flex items-center gap-3">
            <!-- Cancel Button -->
            <button 
                type="button" 
                wire:click="cancelarVenta"
                class="px-6 py-3 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm flex items-center gap-2 transition focus:outline-none"
            >
                ❌
                <span>Cancelar</span>
            </button>
            
            <!-- Submit (Cobrar) Button -->
            <button 
                type="button" 
                wire:click="guardarVenta"
                wire:loading.attr="disabled"
                @disabled(!$this->canSave)
                class="px-8 py-3 rounded-xl font-extrabold text-sm flex items-center gap-2 transition shadow-md focus:outline-none {{ $this->canSave ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-500/10' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}"
            >
                🛒
                <span wire:loading.remove wire:target="guardarVenta">Cobrar</span>
                <span wire:loading wire:target="guardarVenta" class="flex items-center gap-1.5">
                    <span class="h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    Cargando...
                </span>
            </button>
        </div>
    </div>

    <!-- 4. System Status Footer Bar -->
    <footer class="px-6 py-2.5 text-xs text-slate-400 flex items-center justify-between border-t pos-border" style="background-color: var(--pos-footer-bg);">
        <!-- Connected Status -->
        <div class="flex items-center gap-2 font-semibold">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-slate-350 font-bold">Conectado</span>
            <svg class="h-4 w-4 text-emerald-450" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a11 11 0 0115.82 0M2.828 7.93a15 15 0 0121.228 0" />
            </svg>
        </div>

        <!-- Last sync -->
        <div class="flex items-center gap-2">
            <svg class="h-3.5 w-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6H16" />
            </svg>
            <span id="pos-last-sync-time">Última sincronización: --/--/---- --:--:--</span>
        </div>

        <!-- Version & Details -->
        <div class="flex items-center gap-3">
            <span>Versión 1.0.0</span>
            <svg class="h-4 w-4 text-slate-500 hover:text-white cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg class="h-4 w-4 text-slate-500 hover:text-white cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
        </div>
    </footer>

    <!-- 5. Date & Time Dynamic Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dateEl = document.getElementById('pos-live-date');
            const timeEl = document.getElementById('pos-live-time');
            const syncEl = document.getElementById('pos-last-sync-time');

            const updateTime = () => {
                const now = new Date();
                
                // Format Date: DD/MM/YYYY
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = now.getFullYear();
                if (dateEl) dateEl.innerText = `${day}/${month}/${year}`;

                // Format Time: HH:MM:SS AM/PM
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                const strTime = `${String(hours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
                if (timeEl) timeEl.innerText = strTime;
            };

            const setSyncTime = () => {
                const now = new Date();
                const day = String(now.getDate()).padStart(2, '0');
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const year = now.getFullYear();
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                const formattedSync = `Última sincronización: ${day}/${month}/${year} ${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
                if (syncEl) syncEl.innerText = formattedSync;
            };

            updateTime();
            setSyncTime();
            setInterval(updateTime, 1000);
        });
    </script>
</div>
