@php($resumen = $this->resumen)

<div 
    x-data="{
        theme: localStorage.getItem('theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light'),
        init() {
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    let input = document.getElementById('search-producto-input');
                    if (input) input.focus();
                }
                if (e.key === 'F4') {
                    e.preventDefault();
                    $wire.call('toggleMedioPagoShortcut');
                }
                if (e.key === 'F8') {
                    e.preventDefault();
                    if ($wire.get('canSave')) {
                        $wire.call('guardarVenta');
                    }
                }
                if (e.key === 'Escape') {
                    e.preventDefault();
                    $wire.call('cancelarVenta');
                }
            });
        }
    }"
    class="pos-viewport min-h-screen font-sans antialiased transition-all duration-300"
>
    <style>
        /* Scoped style variables for local theme support without relying on global Tailwind dark mode */
        html:not(.dark) .pos-viewport {
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

        html.dark .pos-viewport {
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
            color: var(--pos-text-main) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-radius: 1rem !important; /* rounded-2xl */
            transition: all 0.3s ease !important;
        }

        html:not(.dark) .pos-viewport .pos-card {
            background-color: rgba(255, 255, 255, 0.75) !important;
            border: 1px solid rgba(226, 232, 240, 0.7) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
        }

        html.dark .pos-viewport .pos-card {
            background-color: rgba(17, 24, 39, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.07) !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.25) !important;
        }

        /* Viewport Lock for laptops/tablets in landscape */
        @media (min-width: 1024px) {
            .pos-viewport {
                display: flex !important;
                flex-direction: column !important;
                height: 100vh !important;
                max-height: 100vh !important;
                overflow: hidden !important;
            }

            .pos-viewport header,
            .pos-viewport footer,
            .pos-viewport .pos-bottom-bar {
                flex-shrink: 0 !important;
            }

            .pos-viewport main {
                flex: 1 !important;
                min-height: 0 !important;
                overflow: hidden !important;
                padding: 1.25rem !important;
            }

            .pos-main-grid {
                display: grid !important;
                grid-template-columns: repeat(12, minmax(0, 1fr)) !important;
                gap: 1.25rem !important;
                height: 100% !important;
                min-height: 0 !important;
            }

            .pos-column {
                display: flex !important;
                flex-direction: column !important;
                height: 100% !important;
                min-height: 0 !important;
                gap: 1rem !important;
            }

            .pos-scrollable {
                overflow-y: auto !important;
                flex: 1 !important;
                min-height: 0 !important;
            }
        }

        /* Micro-animations */
        @keyframes pulse-green {
            0% { background-color: rgba(16, 185, 129, 0); }
            50% { background-color: rgba(16, 185, 129, 0.15); }
            100% { background-color: rgba(16, 185, 129, 0); }
        }
        .animate-add-item {
            animation: pulse-green 0.5s ease-out;
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
        html.dark .pos-viewport ::-webkit-scrollbar-thumb {
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
        <div wire:ignore class="hidden md:flex items-center gap-8 text-sm text-blue-100/90">
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
                @click="theme = (theme === 'light' ? 'dark' : 'light'); localStorage.setItem('theme', theme); if(theme === 'dark') { document.documentElement.classList.add('dark'); } else { document.documentElement.classList.remove('dark'); }"
                class="p-2 rounded-xl bg-blue-950/40 border border-blue-800/60 hover:bg-blue-900 transition text-blue-200"
                title="Cambiar Tema"
            >
                <span x-show="theme === 'light'">
                    <!-- Moon Icon -->
                    <svg class="h-5 w-5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                    </svg>
                </span>
                <span x-show="theme === 'dark'">
                    <!-- Sun Icon -->
                    <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464-4.95a1 1 0 111.414 1.414L14.14 5.636a1 1 0 11-1.414-1.414l.793-.793zm-9 0a1 1 0 011.414 0l.793.793a1 1 0 11-1.414 1.414L4.536 5.636a1 1 0 010-1.414zm12.728 9.9a1 1 0 010 1.414l-.793.793a1 1 0 11-1.414-1.414l.793-.793a1 1 0 011.414 0zm-12.728 0a1 1 0 011.414-1.414l.793.793a1 1 0 11-1.414 1.414l-.793-.793zm11.728-3.9a1 1 0 011-1h1a1 1 0 110 2h-1a1 1 0 01-1-1zm-13 0a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                </span>
            </button>

            <!-- User Dropdown (Avatar) -->
            <div class="flex items-center gap-2.5 border-l border-blue-800/60 pl-4">
                <div class="h-9 w-9 rounded-full bg-blue-600 border-2 border-white flex items-center justify-center font-bold text-white text-sm shadow">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="hidden sm:block text-left text-xs">
                    <p class="font-bold leading-tight text-white">{{ Auth::user()->name }}</p>
                    <p class="text-blue-200 leading-none">{{ Auth::user()->name }}</p>
                </div>
            </div>
        </div>
    </header>

    <!-- 2. Main Content Body (Grid cols 12) -->
    <main class="p-5">
        <div class="pos-main-grid">
            
            <!-- Column 1: CATEGORIES (Col span: 2) -->
            <div class="col-span-12 lg:col-span-2 pos-column relative z-10">
                <div class="pos-card p-4 flex flex-col h-full min-h-0">
                    <span class="text-xs font-black uppercase tracking-wider pos-text-muted block mb-3">Categorías</span>
                    <nav class="space-y-1.5 overflow-y-auto flex-1 pr-1">
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
            <div class="col-span-12 lg:col-span-4 pos-column relative z-30">
                
                <!-- Search bar & Results -->
                <div class="pos-card p-4 flex gap-3 items-center relative z-50">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="search-producto-input"
                            wire:model.live.debounce.250ms="searchProducto"
                            placeholder="Buscar producto por código, nombre... (F2)"
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
                    <div class="pos-card p-4 space-y-3 relative z-30">
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
                                        <p class="text-[9px] pos-text-muted mt-0.5">{{ $prod['presentacion'] }}</p>
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
                <div class="pos-card p-5 flex flex-col flex-1 min-h-0 relative z-10">
                    <span class="text-xs font-black uppercase tracking-wider pos-text-muted block mb-3">Productos en el Carrito</span>
                    
                    <div class="overflow-y-auto flex-1 min-h-0 pr-1">
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
                                    <tr wire:key="cart-item-row-{{ $item['producto_presentacion_id'] }}-{{ $item['cantidad'] }}" class="align-middle pos-table-row transition-all duration-150 animate-add-item">
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
            <div class="col-span-12 lg:col-span-3 pos-column pos-scrollable pr-1 relative z-20">
                
                <!-- Cliente Card -->
                <div class="pos-card p-4 space-y-3 relative z-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-blue-600 dark:text-emerald-400 font-bold text-xs uppercase tracking-wide">
                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Cliente</span>
                        </div>
                    </div>

                    @if (!$clienteId)
                        <!-- Client Search Input & Dropdown -->
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input
                                        type="text"
                                        wire:model.live.debounce="searchCliente"
                                        wire:keydown.enter.prevent="buscarCliente"
                                        class="w-full pos-input rounded-xl py-2 px-3 text-xs font-semibold focus:outline-none"
                                        placeholder="Buscar por DNI/RUC (Enter)..."
                                    >
                                    @if($showClienteDropdown)
                                        <div class="absolute left-0 right-0 z-50 mt-2 max-h-64 overflow-y-auto rounded-xl border border-slate-750 bg-slate-900/95 shadow-2xl divide-y divide-slate-800">
                                            @foreach($clientesResultados as $cliente)
                                                <button
                                                    type="button"
                                                    wire:click="seleccionarCliente({{ $cliente['id'] }})"
                                                    class="w-full px-4 py-3 text-left hover:bg-emerald-500/10 transition text-white"
                                                >
                                                    <span class="block text-xs font-bold">{{ $cliente['nombre_completo'] ?: 'Cliente' }}</span>
                                                    <span class="block text-[10px] text-slate-400">{{ $cliente['tipo_documento'] }} {{ $cliente['documento'] }} @if($cliente['telefono']) · {{ $cliente['telefono'] }} @endif</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <!-- Buscar Button -->
                                <button 
                                    type="button" 
                                    wire:click="buscarCliente"
                                    class="px-3 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center"
                                    title="Buscar cliente"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </button>
                                <!-- Manual Add Button -->
                                <button 
                                    type="button" 
                                    wire:click="abrirRegistroManual"
                                    class="px-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center font-mono"
                                    title="Registrar cliente manualmente"
                                >
                                    +
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Selected Client Details Box -->
                        <div class="rounded-xl border pos-border bg-slate-100/40 dark:bg-slate-900/40 p-3.5 flex items-center justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <span class="rounded bg-blue-500/15 border border-blue-500/30 px-1.5 py-0.5 text-[10px] font-bold text-blue-600 dark:text-blue-400">
                                    {{ $clienteTipoDocumento }}: {{ $clienteDocumento }}
                                </span>
                                <h4 class="font-extrabold text-sm truncate pos-text">
                                    {{ $clienteRazonSocial ?: trim(($clienteNombre ?? '') . ' ' . ($clienteApellido ?? '')) }}
                                </h4>
                            </div>
                            <div class="flex gap-1 shrink-0">
                                <!-- Edit Button -->
                                <button 
                                    type="button" 
                                    wire:click="abrirEdicionCliente"
                                    class="p-2 border pos-border hover:bg-blue-500/10 text-blue-500 rounded-xl transition"
                                    title="Editar datos de contacto"
                                >
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <!-- Clear Button -->
                                <button 
                                    type="button" 
                                    wire:click="limpiarCliente"
                                    class="p-2 border pos-border hover:bg-rose-500/10 text-rose-500 rounded-xl transition"
                                    title="Deseleccionar cliente"
                                >
                                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Método de Pago Card -->
                <div class="pos-card p-4 space-y-3 relative z-10">
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

                        <button
                            type="button"
                            wire:click="cambiarMedioPago('OTRO')"
                            class="py-2.5 px-1 border rounded-xl flex flex-col items-center justify-center gap-1.5 transition text-center focus:outline-none {{ $medioPago === 'OTRO' ? 'pos-active-payment-transf font-bold' : 'pos-border pos-hoverable pos-text-muted' }}"
                        >
                            <span class="text-lg">✅</span>
                            <span class="text-[9px]">Otro</span>
                        </button>
                    </div>

                    @if($medioPago !== 'EFECTIVO')
                        <div>
                            <label class="block font-semibold pos-text-muted text-xs mb-1">Referencia de pago</label>
                            <input type="text" wire:model="referenciaPago" class="w-full pos-input rounded-xl py-2 px-3 text-xs focus:outline-none" placeholder="Operación, código o nota">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Column 4: Cajero image and Resumen (Col span: 3) -->
            <div class="col-span-12 lg:col-span-3 pos-column pos-scrollable pr-1 relative z-10">
                 <!-- Comprobante Card -->
                <div class="pos-card p-4 space-y-3 relative z-20">
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
                            <span>IGV ({{ number_format($porcentajeIgv, 2) }}%)</span>
                            <span class="pos-text">S/ {{ number_format($resumen['totales']['total_igv'], 2) }}</span>
                        </div>
                        <div class="flex justify-between pos-text-muted text-[10px]">
                            <span>Configuración</span>
                            <span class="pos-text">{{ $this->preciosIncluyenImpuesto ? 'Precios con IGV' : 'Precios sin IGV' }}</span>
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
    <div class="pos-bottom-bar px-6 py-4 border-t pos-border flex flex-wrap gap-4 items-center justify-between" style="background-color: var(--pos-hover-bg);">
        <!-- Cancel Button -->
        <button 
            type="button" 
            wire:click="cancelarVenta"
            class="px-6 py-3 rounded-xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm flex items-center gap-2 transition focus:outline-none"
        >
            ❌
            <span>Cancelar</span>
        </button>
        
        <!-- Submit (Registrar Venta) Button -->
        <button 
            type="button" 
            wire:click="guardarVenta"
            wire:loading.attr="disabled"
            @disabled(!$this->canSave)
            class="px-8 py-3 rounded-xl font-extrabold text-sm flex items-center gap-2 transition shadow-md focus:outline-none {{ $this->canSave ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-500/10' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}"
        >
            🛒
            <span wire:loading.remove wire:target="guardarVenta">Registrar Venta</span>
            <span wire:loading wire:target="guardarVenta" class="flex items-center gap-1.5">
                <span class="h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                Cargando...
            </span>
        </button>
    </div>

    <!-- 4. Modal de Impresión / Éxito -->
    @if ($showSuccessModal && $createdDocumentoId)
        <div class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350" style="z-index: 99999 !important;">
            <div class="pos-card p-8 max-w-md w-full mx-4 space-y-6 text-center shadow-2xl border pos-border bg-white/90 dark:bg-slate-900/90 rounded-2xl">
                <!-- Checkmark Icon -->
                <div class="h-16 w-16 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto border border-emerald-500/30 shadow-inner">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-black pos-text">¡Venta Registrada!</h3>
                    <p class="text-xs pos-text-muted">El comprobante ha sido creado con éxito. Selecciona una opción para continuar.</p>
                </div>

                <!-- Print Options -->
                <div class="grid grid-cols-2 gap-3">
                    <a 
                        href="{{ route('filament.documentos.ticket', ['documento' => $createdDocumentoId]) }}" 
                        target="_blank"
                        class="px-4 py-3 rounded-xl border border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-800 hover:bg-blue-600/5 font-bold text-sm flex items-center justify-center gap-2 transition"
                    >
                        🖨️ Ticket
                    </a>
                    <a 
                        href="{{ route('filament.documentos.pdf', ['documento' => $createdDocumentoId]) }}" 
                        target="_blank"
                        class="px-4 py-3 rounded-xl border border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-800 hover:bg-blue-600/5 font-bold text-sm flex items-center justify-center gap-2 transition"
                    >
                        📄 PDF A4
                    </a>
                </div>

                <!-- Close & New Sale -->
                <div class="pt-2">
                    <button 
                        type="button" 
                        wire:click="cerrarSuccessModal"
                        class="w-full px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-sm rounded-xl transition shadow-lg shadow-emerald-500/10 focus:outline-none"
                    >
                        Nueva Venta
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- 4b. Modal de Registro Manual de Cliente -->
    @if ($showRegistrarClienteModal)
        <div class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350" style="z-index: 99999 !important;">
            <div class="pos-card p-6 max-w-md w-full mx-4 space-y-4 shadow-2xl border pos-border bg-white/90 dark:bg-slate-900/90 rounded-2xl">
                <div class="flex items-center justify-between border-b pos-border pb-2">
                    <h3 class="text-base font-black pos-text flex items-center gap-2">
                        <span>👤 Registrar Cliente</span>
                    </h3>
                    <button type="button" wire:click="$set('showRegistrarClienteModal', false)" class="pos-text-muted hover:text-rose-500 transition font-bold text-lg">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold pos-text-muted mb-1">Tipo Documento</label>
                            <select 
                                wire:model.live="clienteTipoDocumento" 
                                class="w-full rounded-xl py-2 px-3 focus:ring-0 focus:outline-none pos-select"
                            >
                                <option value="DNI">DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">CE</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold pos-text-muted mb-1">Documento</label>
                            <input 
                                type="text" 
                                wire:model.live="clienteDocumento" 
                                class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none"
                                placeholder="N° Documento"
                            >
                        </div>
                    </div>

                    @if($clienteTipoDocumento === 'RUC')
                        <div>
                            <label class="block font-semibold pos-text-muted mb-1">Razón Social</label>
                            <input 
                                type="text" 
                                wire:model="clienteRazonSocial" 
                                class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                                placeholder="Razón Social de la Empresa"
                            >
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold pos-text-muted mb-1">Nombres</label>
                                <input 
                                    type="text" 
                                    wire:model="clienteNombre" 
                                    class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                                    placeholder="Nombres"
                                >
                            </div>
                            <div>
                                <label class="block font-semibold pos-text-muted mb-1">Apellidos</label>
                                <input 
                                    type="text" 
                                    wire:model="clienteApellido" 
                                    class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                                    placeholder="Apellidos"
                                >
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block font-semibold pos-text-muted mb-1">Teléfono (Opcional)</label>
                        <input 
                            type="text" 
                            wire:model="clienteTelefono" 
                            class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                            placeholder="Teléfono"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold pos-text-muted mb-1">Email (Opcional)</label>
                        <input 
                            type="email" 
                            wire:model="clienteEmail" 
                            class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                            placeholder="correo@ejemplo.com"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold pos-text-muted mb-1">Dirección (Opcional)</label>
                        <input 
                            type="text" 
                            wire:model="clienteDireccion" 
                            class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                            placeholder="Dirección fiscal o domiciliaria"
                        >
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t pos-border">
                    <button 
                        type="button" 
                        wire:click="$set('showRegistrarClienteModal', false)"
                        class="px-4 py-2 border pos-border rounded-xl text-xs font-bold pos-text-muted hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="registrarClienteManual"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-extrabold transition shadow-md"
                    >
                        Registrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- 4c. Modal de Edición de Cliente -->
    @if ($showEditarClienteModal)
        <div class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350" style="z-index: 99999 !important;">
            <div class="pos-card p-6 max-w-md w-full mx-4 space-y-4 shadow-2xl border pos-border bg-white/90 dark:bg-slate-900/90 rounded-2xl">
                <div class="flex items-center justify-between border-b pos-border pb-2">
                    <h3 class="text-base font-black pos-text flex items-center gap-2">
                        <span>✏️ Editar Datos de Contacto</span>
                    </h3>
                    <button type="button" wire:click="$set('showEditarClienteModal', false)" class="pos-text-muted hover:text-rose-500 transition font-bold text-lg">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Read-only section -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-100/60 dark:bg-slate-900/60 p-3 rounded-xl border pos-border">
                        <div>
                            <span class="block text-[10px] pos-text-muted font-bold uppercase">Tipo Doc</span>
                            <span class="font-extrabold pos-text">{{ $clienteTipoDocumento }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] pos-text-muted font-bold uppercase">Documento</span>
                            <span class="font-extrabold pos-text">{{ $clienteDocumento }}</span>
                        </div>
                        <div class="col-span-2 mt-1.5">
                            <span class="block text-[10px] pos-text-muted font-bold uppercase">Nombre o Razón Social</span>
                            <span class="font-extrabold pos-text block truncate">
                                {{ $clienteRazonSocial ?: trim(($clienteNombre ?? '') . ' ' . ($clienteApellido ?? '')) }}
                            </span>
                        </div>
                    </div>

                    <!-- Editable fields -->
                    <div>
                        <label class="block font-semibold pos-text-muted mb-1">Teléfono</label>
                        <input 
                            type="text" 
                            wire:model="clienteTelefono" 
                            class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                            placeholder="Teléfono"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold pos-text-muted mb-1">Email</label>
                        <input 
                            type="email" 
                            wire:model="clienteEmail" 
                            class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                            placeholder="correo@ejemplo.com"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold pos-text-muted mb-1">Dirección</label>
                        <input 
                            type="text" 
                            wire:model="clienteDireccion" 
                            class="w-full pos-input rounded-xl py-2 px-3 focus:outline-none" 
                            placeholder="Dirección del cliente"
                        >
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t pos-border">
                    <button 
                        type="button" 
                        wire:click="$set('showEditarClienteModal', false)"
                        class="px-4 py-2 border pos-border rounded-xl text-xs font-bold pos-text-muted hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="guardarEdicionCliente"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-extrabold transition shadow-md"
                    >
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Advertencia Lote Vencido (POS) -->
    @if ($showVencidoWarningModal)
        <div 
            x-data
            x-init="setTimeout(() => $refs.entendidoBtn.focus(), 150)"
            @keydown.window.enter.prevent="if ($refs.entendidoBtn) { $refs.entendidoBtn.click(); }"
            class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350" style="z-index: 99999 !important;"
        >
            <div class="pos-card p-6 max-w-md w-full mx-4 space-y-5 text-center shadow-2xl border pos-border bg-white/95 dark:bg-slate-900/95 rounded-2xl">
                <!-- Warning Icon -->
                <div class="h-16 w-16 bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center mx-auto border border-rose-500/30 shadow-inner">
                    <svg class="h-8 w-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-lg font-black text-rose-600 dark:text-rose-400">⚠️ ¡Advertencia: Lote Expirado!</h3>
                    <p class="text-xs pos-text font-medium leading-relaxed">
                        {!! $vencidoWarningMessage !!}
                    </p>
                </div>

                <div class="flex justify-center pt-2">
                    <!-- Entendido / Continuar Button -->
                    <button 
                        type="button" 
                        x-ref="entendidoBtn"
                        wire:click="confirmarAgregarProducto"
                        class="w-full px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs rounded-xl transition shadow-lg shadow-rose-500/10 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                    >
                        Entendido (Presione Enter)
                    </button>
                </div>
            </div>
        </div>
    @endif

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
