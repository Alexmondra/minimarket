@php($resumen = $this->resumen)

<div 
    x-data="{
        activeTab: 'cart',
        categoriesCollapsed: localStorage.getItem('pos_categories_collapsed') === '1',
        theme: localStorage.getItem('theme') || (document.documentElement.classList.contains('dark') ? 'dark' : 'light'),
        toggleCategories() {
            this.categoriesCollapsed = ! this.categoriesCollapsed;
            localStorage.setItem('pos_categories_collapsed', this.categoriesCollapsed ? '1' : '0');
        },
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
                    if ($wire.get('showSuccessModal')) {
                        $wire.call('cerrarSuccessModal');
                    } else {
                        $wire.call('cancelarVenta');
                    }
                }
            });
        }
    }"
    :class="{'pos-tab-catalog': activeTab === 'catalog', 'pos-tab-cart': activeTab === 'cart', 'pos-tab-payment': activeTab === 'payment'}"
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
            background-color: var(--pos-bg) !important;
            color: var(--pos-text-main) !important;
            display: flex !important;
            flex-direction: column !important;
            height: 100vh !important;
            max-height: 100vh !important;
            overflow: hidden !important;
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

        .pos-viewport header,
        .pos-viewport footer,
        .pos-viewport .pos-bottom-bar {
            flex-shrink: 0 !important;
        }

        .pos-viewport main {
            flex: 1 !important;
            min-height: 0 !important;
            overflow: hidden !important;
            padding: 1rem !important;
        }

        @media (max-width: 1023px) {
            .pos-col-catalog,
            .pos-col-cart,
            .pos-col-payment {
                display: none !important;
            }

            .pos-tab-catalog .pos-col-catalog {
                display: flex !important;
                flex-direction: column !important;
                height: 100% !important;
                min-height: 0 !important;
                gap: 1rem !important;
            }
            .pos-tab-cart .pos-col-cart {
                display: flex !important;
                flex-direction: column !important;
                height: 100% !important;
                min-height: 0 !important;
                gap: 1rem !important;
            }
            .pos-tab-payment .pos-col-payment {
                display: flex !important;
                flex-direction: column !important;
                height: auto !important;
                gap: 1rem !important;
            }

            .pos-main-grid {
                overflow-y: auto !important;
                display: flex !important;
                flex-direction: column !important;
                height: 100% !important;
            }
        }

        /* Viewport Lock for laptops/tablets in landscape */
        @media (min-width: 1024px) {
            .pos-viewport main {
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

        @media (min-width: 1280px) {
            .pos-main-grid.pos-categories-collapsed .pos-col-catalog {
                display: none !important;
            }

            .pos-main-grid.pos-categories-collapsed .pos-col-cart {
                grid-column: span 6 / span 6 !important;
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

        .pos-viewport .pos-comprobante-active {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 55%, #0ea5e9 100%) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
            color: #ffffff !important;
            box-shadow: 0 14px 30px -14px rgba(79, 70, 229, 0.8) !important;
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

        /* Hide standard browser spinners for number inputs in POS */
        .pos-viewport input[type="number"]::-webkit-inner-spin-button,
        .pos-viewport input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none !important;
            margin: 0 !important;
        }
        .pos-viewport input[type="number"] {
            -moz-appearance: textfield !important;
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

        <!-- System Stats (Caja, Fecha, Hora, Buscar) -->
        <div class="hidden md:flex items-center gap-6 text-sm text-blue-100/90">
            <!-- Botón Cerrar Caja -->
            <button 
                type="button" 
                wire:click="openCerrarCajaModal"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white rounded-lg transition shadow-md shadow-rose-900/20"
                title="Cerrar Caja Actual"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Cerrar Caja</span>
            </button>

            <!-- Fecha -->
            <div wire:ignore class="flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z" />
                </svg>
                <span id="pos-live-date">--/--/----</span>
            </div>

            <!-- Hora -->
            <div wire:ignore class="flex items-center gap-2">
                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="pos-live-time" class="font-mono">--:--:-- --</span>
            </div>

            <!-- Botón Buscar Venta -->
            <button 
                type="button" 
                wire:click="openBuscarVentaModal"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition shadow-md shadow-blue-900/20"
                title="Buscar Venta"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Buscar Venta</span>
            </button>
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

    <!-- Tabs Navigation Bar for Mobile/Tablet -->
    <div class="lg:hidden flex border-b pos-border bg-slate-100/80 dark:bg-slate-900/80 backdrop-blur-md sticky top-0 z-40 shrink-0">
        <button 
            type="button" 
            @click="activeTab = 'cart'" 
            :class="activeTab === 'cart' ? 'border-blue-600 text-blue-600 dark:border-emerald-500 dark:text-emerald-400 font-extrabold' : 'border-transparent pos-text-muted hover:text-slate-900 dark:hover:text-white'" 
            class="flex-1 py-3 text-center border-b-2 text-xs transition duration-150 flex items-center justify-center gap-1.5 focus:outline-none"
        >
            <span>🛒</span>
            <span>Carrito</span>
            <span class="px-1.5 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-[10px] font-bold">
                {{ count($cartItems) }}
            </span>
        </button>
        <button 
            type="button" 
            @click="activeTab = 'payment'" 
            :class="activeTab === 'payment' ? 'border-blue-600 text-blue-600 dark:border-emerald-500 dark:text-emerald-400 font-extrabold' : 'border-transparent pos-text-muted hover:text-slate-900 dark:hover:text-white'" 
            class="flex-1 py-3 text-center border-b-2 text-xs transition duration-150 flex items-center justify-center gap-1.5 focus:outline-none"
        >
            <span>💳</span>
            <span>Pago y Cliente</span>
            <span class="px-1.5 py-0.5 rounded-full bg-amber-500/10 text-amber-500 text-[10px] font-bold">
                S/ {{ number_format($resumen['totales']['total_neto'], 2) }}
            </span>
        </button>
        <button 
            type="button" 
            @click="activeTab = 'catalog'" 
            :class="activeTab === 'catalog' ? 'border-blue-600 text-blue-600 dark:border-emerald-500 dark:text-emerald-400 font-extrabold' : 'border-transparent pos-text-muted hover:text-slate-900 dark:hover:text-white'" 
            class="flex-1 py-3 text-center border-b-2 text-xs transition duration-150 flex items-center justify-center gap-1.5 focus:outline-none"
        >
            <span>📦</span>
            <span>Catálogo</span>
        </button>
    </div>

    <!-- 2. Main Content Body (Grid cols 12) -->
    <main class="p-5">
        <div class="pos-main-grid" :class="{ 'pos-categories-collapsed': categoriesCollapsed }">
            
            <!-- Column 1: CATEGORIES (Col span: 2) -->
            <div class="col-span-12 lg:col-span-2 pos-column pos-col-catalog relative z-10">
                <div class="pos-card p-4 flex flex-col h-full min-h-0">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <span class="text-xs font-black uppercase tracking-wider pos-text-muted block">Categorías</span>
                        <button
                            type="button"
                            @click="toggleCategories()"
                            class="hidden xl:inline-flex h-8 w-8 items-center justify-center rounded-xl border pos-border pos-hoverable pos-text-muted transition focus:outline-none"
                            title="Contraer categorias"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
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
            <div class="col-span-12 lg:col-span-4 pos-column pos-col-cart relative z-30">
                
                <!-- Search bar & Results -->
                <div class="pos-card p-4 flex gap-3 items-center relative z-50">
                    <button
                        type="button"
                        @click="toggleCategories()"
                        class="hidden xl:inline-flex h-11 items-center gap-2 rounded-xl border pos-border pos-hoverable px-3 text-xs font-black uppercase tracking-wider pos-text-muted transition focus:outline-none"
                        :title="categoriesCollapsed ? 'Mostrar categorias' : 'Ocultar categorias'"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path x-show="!categoriesCollapsed" stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            <path x-show="categoriesCollapsed" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <span x-text="categoriesCollapsed ? 'Categorias' : 'Ocultar'"></span>
                    </button>
                    <div 
                        class="relative flex-1"
                        x-data="{
                            selectedIndex: -1,
                            resultsCount: 0,
                            updateResultsCount() {
                                const conStock = $wire.productosResultados ? $wire.productosResultados.length : 0;
                                const sinStock = $wire.productosSinStockResultados ? $wire.productosSinStockResultados.length : 0;
                                const crearRapido = ($wire.searchProducto || '').length >= 2 && conStock === 0 && sinStock === 0 ? 1 : 0;

                                this.resultsCount = conStock + sinStock + crearRapido;
                                this.selectedIndex = this.resultsCount > 0 ? 0 : -1;
                            },
                            selectNext() {
                                if (this.resultsCount > 0) {
                                    this.selectedIndex = (this.selectedIndex + 1) % this.resultsCount;
                                    this.scrollToActive();
                                }
                            },
                            selectPrev() {
                                if (this.resultsCount > 0) {
                                    this.selectedIndex = (this.selectedIndex - 1 + this.resultsCount) % this.resultsCount;
                                    this.scrollToActive();
                                }
                            },
                            scrollToActive() {
                                this.$nextTick(() => {
                                    const activeEl = this.$el.querySelector('[data-index=\'' + this.selectedIndex + '\']');
                                    if (activeEl) {
                                        activeEl.scrollIntoView({ block: 'nearest' });
                                    }
                                });
                            },
                            selectCurrent() {
                                if (this.selectedIndex >= 0) {
                                    const activeEl = this.$el.querySelector('[data-index=\'' + this.selectedIndex + '\']');
                                    if (activeEl) {
                                        activeEl.click();
                                        this.selectedIndex = -1;
                                    }
                                } else {
                                    $wire.call('procesarEnterBuscador');
                                }
                            }
                        }"
                        x-init="
                            $watch('$wire.productosResultados', () => updateResultsCount());
                            $watch('$wire.productosSinStockResultados', () => updateResultsCount());
                            $watch('$wire.searchProducto', () => updateResultsCount());
                        "
                        @keydown.arrow-down.prevent="selectNext()"
                        @keydown.arrow-up.prevent="selectPrev()"
                        @keydown.enter.prevent="selectCurrent()"
                    >
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
                                wire:click="limpiarBusquedaProducto"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-450 hover:text-white"
                            >
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif

                        <!-- Product Search Dropdown Results inside relative container to lock width -->
                        @if($showProductoDropdown || (strlen($searchProducto) >= 2 && count($productosResultados) === 0 && count($productosSinStockResultados) === 0))
                            <div class="absolute left-0 right-0 z-50 mt-2 max-h-80 overflow-y-auto rounded-2xl border border-emerald-500/25 bg-emerald-50 dark:bg-slate-950 shadow-2xl shadow-emerald-950/20 divide-y divide-slate-200/80 dark:divide-slate-800 w-full ring-1 ring-emerald-500/10">
                                <div class="sticky top-0 z-10 flex items-center justify-between gap-3 bg-emerald-100 dark:bg-slate-900 px-4 py-2 border-b border-emerald-500/15">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Resultados</span>
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Usa ↑ ↓ y Enter</span>
                                </div>
                                @foreach($productosResultados as $producto)
                                    <button 
                                        type="button" 
                                        wire:click="agregarProducto({{ $producto['producto_presentacion_id'] }})"
                                        :class="{ 'bg-emerald-500 dark:bg-emerald-600 ring-2 ring-inset ring-emerald-700 dark:ring-emerald-300 shadow-inner shadow-emerald-900/20': selectedIndex === {{ $loop->index }} }"
                                        data-index="{{ $loop->index }}"
                                        class="group w-full px-4 py-3 text-left hover:bg-emerald-500/10 transition duration-150 flex items-center justify-between gap-4 text-slate-900 dark:text-white"
                                    >
                                        <div class="flex min-w-0 items-center gap-3">
                                            @if(!empty($producto['imagen_url']))
                                                <img src="{{ $producto['imagen_url'] }}" alt="{{ $producto['nombre'] }}" class="h-11 w-11 shrink-0 rounded-xl border border-white/70 object-cover shadow-sm dark:border-slate-700">
                                            @else
                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-lg">📦</div>
                                            @endif
                                            <div class="min-w-0 space-y-0.5">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-black text-slate-950 dark:text-white truncate">{{ $producto['nombre'] }}</span>
                                                    <span class="rounded-lg bg-emerald-100 text-emerald-700 dark:bg-slate-800 px-1.5 py-0.5 text-[9px] font-black dark:text-slate-300 border border-emerald-200 dark:border-slate-700">
                                                        {{ $producto['presentacion'] }}
                                                    </span>
                                                </div>
                                                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                                    Cod: {{ $producto['codigo'] }} | Stock: {{ number_format($producto['stock'], 0) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="rounded-lg bg-emerald-500/15 border border-emerald-500/30 px-3 py-1 text-sm font-black text-emerald-400">
                                            S/ {{ number_format($producto['precio'], 2) }}
                                        </div>
                                    </button>
                                @endforeach

                                @if(count($productosSinStockResultados) > 0)
                                    <div class="px-4 py-2 bg-amber-500/10 text-[10px] font-black uppercase tracking-wider text-amber-300">
                                        Producto registrado, sin stock en esta sucursal
                                    </div>
                                    @foreach($productosSinStockResultados as $producto)
                                        @php($sinStockIndex = count($productosResultados) + $loop->index)
                                        <button 
                                            type="button" 
                                            wire:click="abrirIngresoRapido({{ $producto['producto_presentacion_id'] }})"
                                            :class="{ 'bg-amber-500 dark:bg-amber-600 ring-2 ring-inset ring-amber-700 dark:ring-amber-300 shadow-inner shadow-amber-900/20': selectedIndex === {{ $sinStockIndex }} }"
                                            data-index="{{ $sinStockIndex }}"
                                            class="w-full px-4 py-3 text-left hover:bg-amber-500/10 transition duration-150 flex items-center justify-between gap-4 text-slate-900 dark:text-white"
                                        >
                                            <div class="flex min-w-0 items-center gap-3">
                                                @if(!empty($producto['imagen_url']))
                                                    <img src="{{ $producto['imagen_url'] }}" alt="{{ $producto['nombre'] }}" class="h-11 w-11 shrink-0 rounded-xl border border-white/70 object-cover shadow-sm dark:border-slate-700">
                                                @else
                                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-lg">📦</div>
                                                @endif
                                                <div class="min-w-0 space-y-0.5">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-black text-slate-950 dark:text-white truncate">{{ $producto['nombre'] }}</span>
                                                        <span class="rounded bg-amber-500/15 border border-amber-500/25 px-1.5 py-0.2 text-[9px] font-bold text-amber-200">
                                                            {{ $producto['presentacion'] }}
                                                        </span>
                                                    </div>
                                                    <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                                        Cod: {{ $producto['codigo'] ?: 'Sin codigo' }} | Stock: 0
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="rounded-lg bg-amber-500/15 border border-amber-500/30 px-3 py-1 text-xs font-black text-amber-300">
                                                Agregar rapido
                                            </div>
                                        </button>
                                    @endforeach
                                @endif

                                @if(strlen($searchProducto) >= 2 && count($productosResultados) === 0 && count($productosSinStockResultados) === 0)
                                    <button 
                                        type="button" 
                                        wire:click="abrirIngresoRapido"
                                        :class="{ 'bg-emerald-500 dark:bg-emerald-600 ring-2 ring-inset ring-emerald-700 dark:ring-emerald-300 shadow-inner shadow-emerald-900/20': selectedIndex === 0 }"
                                        data-index="0"
                                        class="w-full px-4 py-4 text-left hover:bg-emerald-500/10 transition duration-150 flex items-center justify-between gap-4 text-slate-900 dark:text-white"
                                    >
                                        <div class="space-y-1">
                                            <div class="text-sm font-black text-slate-950 dark:text-white">No esta registrado en inventario</div>
                                            <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Crear producto rapido con codigo: {{ $searchProducto }}</div>
                                        </div>
                                        <div class="rounded-lg bg-emerald-500/15 border border-emerald-500/30 px-3 py-1 text-xs font-black text-emerald-300">
                                            Crear y vender
                                        </div>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                    
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
                                    class="p-2.5 rounded-xl border pos-border bg-slate-100/40 dark:bg-slate-900/40 hover:border-blue-500 dark:hover:border-emerald-500 transition text-left flex flex-col justify-between h-28"
                                >
                                    <div class="flex gap-2">
                                        @if(!empty($prod['imagen_url']))
                                            <img src="{{ $prod['imagen_url'] }}" alt="{{ $prod['nombre'] }}" class="h-10 w-10 shrink-0 rounded-lg border pos-border object-cover shadow-inner">
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border pos-border bg-white/50 text-base dark:bg-slate-950/40">📦</div>
                                        @endif
                                        <div class="min-w-0">
                                            <h3 class="text-xs font-bold leading-tight line-clamp-2 pos-text">{{ $prod['nombre'] }}</h3>
                                            <p class="text-[9px] pos-text-muted mt-0.5 truncate">{{ $prod['presentacion'] }}</p>
                                        </div>
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
                                    @php($stockSobregirado = (float) ($item['cantidad'] ?? 0) > (float) ($item['stock'] ?? 0))
                                    @php($cantidadItem = (float) ($item['cantidad'] ?? 0))
                                    @php($precioItem = (float) ($item['precio'] ?? 0))
                                    <tr wire:key="cart-item-row-{{ $item['producto_presentacion_id'] }}-{{ $item['cantidad'] }}" class="align-middle pos-table-row transition-all duration-150 animate-add-item">
                                        <!-- Product info with mock icon -->
                                        <td class="py-2.5 pr-2">
                                            <div class="flex items-center gap-2">
                                                @if(!empty($item['imagen_url']))
                                                    <img src="{{ $item['imagen_url'] }}" alt="{{ $item['nombre'] }}" class="h-9 w-9 rounded-lg border pos-border object-cover shrink-0 shadow-inner">
                                                @else
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
                                                @endif
                                                <div class="min-w-0">
                                                    <span class="font-bold text-xs block leading-tight truncate pos-text">{{ $item['nombre'] }}</span>
                                                    <span class="mt-0.5 flex items-center gap-1.5 text-[10px] pos-text-muted">
                                                        <span class="truncate">{{ $item['presentacion'] }}</span>
                                                        @if($stockSobregirado)
                                                            <span x-data="{ show: false }" class="relative inline-flex">
                                                                <button
                                                                    type="button"
                                                                    x-on:click="show = ! show"
                                                                    x-on:click.outside="show = false"
                                                                    class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-amber-400/60 bg-amber-400/15 text-[10px] font-black text-amber-600 transition hover:bg-amber-400/25 dark:text-amber-300"
                                                                    title="Stock insuficiente"
                                                                >!</button>
                                                                <span
                                                                    x-cloak
                                                                    x-show="show"
                                                                    x-transition
                                                                    class="absolute left-5 top-1/2 z-30 w-52 -translate-y-1/2 rounded-xl border border-amber-400/30 bg-amber-50 px-3 py-2 text-left text-[10px] font-bold leading-snug text-amber-800 shadow-xl dark:bg-slate-950 dark:text-amber-200"
                                                                >
                                                                    Stock disponible: {{ number_format((float) ($item['stock'] ?? 0), 3) }}. La venta se permitira y el inventario quedara en 0.
                                                                </span>
                                                            </span>
                                                        @endif
                                                    </span>
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
                                                    inputmode="decimal"
                                                    pattern="[0-9]*[.]?[0-9]*"
                                                    step="0.001"
                                                    value="{{ $cantidadItem }}"
                                                    wire:change="actualizarCantidad({{ $index }}, $event.target.value)"
                                                    x-on:input="let v = $event.target.value.replace(/,/g, '.').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); let p = v.split('.'); p[0] = (p[0] || '').slice(0, 6); if (p[1] !== undefined) p[1] = p[1].slice(0, 3); $event.target.value = p[1] !== undefined ? p[0] + '.' + p[1] : p[0];"
                                                    class="w-8 text-center bg-transparent border-0 p-0 text-xs font-bold focus:ring-0 focus:outline-none pos-text"
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
                                        <td class="py-2.5 text-right w-24">
                                             <div class="flex items-center justify-end">
                                                 <span class="text-xs font-semibold pos-text mr-1">S/</span>
                                                  <input 
                                                      type="text" 
                                                      inputmode="decimal"
                                                      pattern="[0-9]*[.]?[0-9]*"
                                                      step="0.01" 
                                                      value="{{ number_format($precioItem, 2, '.', '') }}"
                                                      wire:change="actualizarPrecio({{ $index }}, $event.target.value)"
                                                      x-on:input="let v = $event.target.value.replace(/,/g, '.').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'); let p = v.split('.'); p[0] = (p[0] || '').slice(0, 6); if (p[1] !== undefined) p[1] = p[1].slice(0, 2); $event.target.value = p[1] !== undefined ? p[0] + '.' + p[1] : p[0];"
                                                      class="w-20 text-right bg-slate-100/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-lg px-2 py-1 text-xs font-bold focus:ring-1 focus:ring-blue-500 focus:outline-none pos-text"
                                                  >
                                             </div>
                                        </td>

                                        <!-- Line Total -->
                                        <td class="py-2.5 text-right text-xs font-extrabold text-blue-600 dark:text-emerald-400">
                                            S/{{ number_format($cantidadItem * $precioItem, 2) }}
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
            <div class="col-span-12 lg:col-span-3 pos-column pos-scrollable pos-col-payment pr-1 relative z-20">
                
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

                        @if($puntosDisponibles > 0)
                            @php($descuentoPuntosPreview = app(\App\Support\Ventas\PuntosService::class)->descuentoPorPuntos((int) $puntosCanjear))
                            <div class="rounded-xl border border-amber-400/30 bg-amber-400/10 p-2.5 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-300">
                                        {{ number_format($puntosDisponibles) }} pts disponibles
                                    </span>
                                    <button
                                        type="button"
                                        wire:click="$set('showPuntosInfoModal', true)"
                                        class="h-6 w-6 rounded-lg border border-amber-400/40 bg-amber-400/10 text-[11px] font-black text-amber-600 dark:text-amber-300 hover:bg-amber-400/20"
                                        title="Ver regla de puntos"
                                    >
                                        !
                                    </button>
                                </div>

                                <div class="grid grid-cols-[1fr_auto] gap-2 items-end">
                                    <div>
                                        <label class="block text-[10px] font-bold pos-text-muted mb-1">Puntos a descontar</label>
                                        <input
                                            type="text"
                                            inputmode="numeric"
                                            pattern="[0-9]*"
                                            wire:model.live.debounce.250ms="puntosCanjear"
                                            x-on:input="$event.target.value = $event.target.value.replace(/[^0-9]/g, '')"
                                            class="w-full pos-input rounded-xl py-2 px-3 text-sm font-black font-mono focus:outline-none"
                                            placeholder="0"
                                        >
                                    </div>
                                    <div class="rounded-xl bg-white/60 dark:bg-slate-950/50 border pos-border px-3 py-2 min-w-[92px] text-right">
                                        <span class="block text-[9px] font-bold pos-text-muted uppercase">Descuento</span>
                                        <span class="block text-sm font-black font-mono {{ $usarPuntos ? 'text-emerald-500' : 'text-amber-600 dark:text-amber-300' }}">S/ {{ number_format($descuentoPuntosPreview, 2) }}</span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    wire:click="toggleDescuentoPuntos"
                                    class="w-full rounded-xl px-3 py-2 text-[10px] font-black uppercase tracking-wide transition {{ $usarPuntos ? 'bg-rose-500 hover:bg-rose-600 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white' }}"
                                >
                                    {{ $usarPuntos ? 'Quitar descuento' : 'Aplicar descuento' }}
                                </button>
                            </div>
                        @else
                            <div class="rounded-xl border pos-border bg-slate-100/40 dark:bg-slate-900/40 px-3 py-2 text-[10px] font-bold pos-text-muted">
                                Este cliente aún no tiene puntos disponibles.
                            </div>
                        @endif
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
            <div class="col-span-12 lg:col-span-3 pos-column pos-scrollable pos-col-payment pr-1 relative z-10">
                 <!-- Comprobante Card -->
                 <div class="pos-card p-1.5 relative z-20 overflow-hidden border border-purple-500/15">
                     <div class="absolute -right-8 -top-8 h-16 w-16 rounded-full bg-purple-500/10 blur-xl"></div>
                     <div class="absolute -left-10 -bottom-10 h-16 w-16 rounded-full bg-blue-500/5 blur-xl"></div>
                     
                     <div class="relative grid grid-cols-3 gap-1 bg-slate-100/50 dark:bg-slate-950/40 rounded-xl p-1">
                         @foreach([
                             ['value' => 'TICKET', 'label' => 'Ticket', 'icon' => '🎫'],
                             ['value' => 'BOLETA', 'label' => 'Boleta', 'icon' => '🧾'],
                             ['value' => 'FACTURA', 'label' => 'Factura', 'icon' => '📄'],
                         ] as $comp)
                             <button
                                 type="button"
                                 wire:click="cambiarTipoComprobante('{{ $comp['value'] }}')"
                                 @class([
                                     'flex items-center justify-center gap-1.5 rounded-lg py-2 px-1 text-center transition-all duration-200 focus:outline-none',
                                     'pos-comprobante-active font-black' => $tipoComprobante === $comp['value'],
                                     'border-transparent text-slate-600 hover:text-purple-700 dark:text-slate-350 dark:hover:text-purple-300 hover:bg-white/40 dark:hover:bg-slate-900/40' => $tipoComprobante !== $comp['value'],
                                 ])
                             >
                                 <span class="text-sm shrink-0">{{ $comp['icon'] }}</span>
                                 <span class="text-[10px] font-black uppercase tracking-wide truncate">{{ $comp['label'] }}</span>
                             </button>
                         @endforeach
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
                    <div class="border-t pos-border pt-3 space-y-3" x-data="{ montoLocal: @entangle('montoRecibido'), totalNeto: {{ $resumen['totales']['total_neto'] }}, get montoNum() { let v = String(this.montoLocal ?? '').replace(/,/g, '.').replace(/[^0-9.]/g, ''); if (v === '' || v === '.') return 0; let p = v.split('.'); if (p.length > 2) v = p[0] + '.' + p.slice(1).join(''); return parseFloat(v) || 0; }, get vueltoLocal() { return this.montoNum - this.totalNeto; } }">
                        @if($medioPago === 'EFECTIVO')
                            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-3 shadow-inner dark:bg-emerald-500/10">
                                <label class="block font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-300 text-[10px] mb-2">Monto recibido</label>
                                <div class="relative rounded-2xl shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-emerald-500 text-sm font-black">S/</div>
                                    <input
                                        type="text"
                                        inputmode="decimal"
                                        pattern="[0-9]*[.,]?[0-9]*"
                                        x-model="montoLocal"
                                        class="w-full pos-input rounded-2xl py-3.5 pl-10 pr-3 text-xl font-black font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500/40"
                                        placeholder="0.00"
                                    >
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs font-bold rounded-xl bg-slate-100/70 dark:bg-slate-900/60 px-3 py-2">
                                <span class="pos-text-muted">Vuelto</span>
                                <template x-if="vueltoLocal >= 0">
                                    <span class="text-emerald-500 font-mono text-base font-black" x-text="'S/ ' + vueltoLocal.toFixed(2)"></span>
                                </template>
                                <template x-if="vueltoLocal < 0">
                                    <span class="text-rose-500 font-mono text-base font-black" x-text="'Falta S/ ' + Math.abs(vueltoLocal).toFixed(2)"></span>
                                </template>
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

        <!-- Mobile Navigation Buttons -->
        <div class="flex items-center gap-2 lg:hidden w-full sm:w-auto">
            <!-- If activeTab === 'catalog', show button to switch to cart -->
            <template x-if="activeTab === 'catalog'">
                <button 
                    type="button" 
                    @click="activeTab = 'cart'"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm flex items-center justify-center gap-2 transition focus:outline-none"
                >
                    <span>🛒 Ver Carrito</span>
                </button>
            </template>

            <!-- If activeTab === 'payment', show button to switch back to cart -->
            <template x-if="activeTab === 'payment'">
                <button 
                    type="button" 
                    @click="activeTab = 'cart'"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-350 hover:bg-slate-300 font-bold text-sm flex items-center justify-center gap-2 transition focus:outline-none"
                >
                    <span>⬅️ Volver al Carrito</span>
                </button>
            </template>
        </div>
        
        <!-- Submit (Registrar Venta) Button -->
        <button
            type="button"
            wire:click="guardarVenta"
            wire:loading.attr="disabled"
            wire:target="guardarVenta"
            @disabled(!$this->canSave)
            :class="{'hidden lg:flex': activeTab === 'catalog', 'flex': activeTab !== 'catalog'}"
            class="px-8 py-3 rounded-xl font-extrabold text-sm items-center gap-2 transition shadow-md focus:outline-none {{ $this->canSave ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-500/10' : 'bg-slate-200 dark:bg-slate-800 text-slate-400 cursor-not-allowed' }}"
        >
            🛒
            <span wire:loading.remove wire:target="guardarVenta">Registrar Venta</span>
            <span wire:loading wire:target="guardarVenta" class="flex items-center gap-1.5">
                <span class="h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                Cargando...
            </span>
        </button>
    </div>

    @if ($showPuntosInfoModal)
        <div
            @click.self="$wire.set('showPuntosInfoModal', false)"
            class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350"
            style="z-index: 99999 !important;"
        >
            <div class="pos-card p-5 max-w-sm w-full mx-4 space-y-4 shadow-2xl border pos-border bg-white/90 dark:bg-slate-900/90 rounded-2xl">
                <div class="flex items-center justify-between border-b pos-border pb-2">
                    <h3 class="text-sm font-black pos-text">Regla de puntos</h3>
                    <button type="button" wire:click="$set('showPuntosInfoModal', false)" class="pos-text-muted hover:text-rose-500 transition font-bold text-lg">&times;</button>
                </div>
                <div class="space-y-2 text-xs pos-text-muted leading-relaxed">
                    <p>El cliente gana 1 punto por cada S/ 1.00 de compra.</p>
                    <p>Cada punto equivale a S/ {{ number_format(\App\Support\Ventas\PuntosService::VALOR_DESCUENTO_POR_PUNTO, 2) }} de descuento.</p>
                    <p>Si ingresas más puntos de los disponibles o más de lo permitido por el total de la venta, el sistema lo ajusta automáticamente al máximo posible.</p>
                </div>
                <button
                    type="button"
                    wire:click="$set('showPuntosInfoModal', false)"
                    class="w-full rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2 text-xs font-black text-white transition"
                >
                    Entendido
                </button>
            </div>
        </div>
    @endif

    <!-- 4. Modal de Impresión / Éxito -->
    @if ($showSuccessModal && $createdDocumentoId)
        <div
            x-data
            x-init="setTimeout(() => $refs.ticketBtn.focus(), 100)"
            @keydown.enter.prevent="$refs.ticketBtn.click()"
            @keydown.escape.prevent="$wire.cerrarSuccessModal()"
            @click.self="$wire.cerrarSuccessModal()"
            class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350 cursor-pointer" style="z-index: 99999 !important;"
        >
            <div class="pos-card p-8 max-w-md w-full mx-4 space-y-6 text-center shadow-2xl border pos-border bg-white/90 dark:bg-slate-900/90 rounded-2xl">
                <!-- Checkmark Icon -->
                <div class="h-16 w-16 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto border border-emerald-500/30 shadow-inner">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-xl font-black pos-text">¡Venta Registrada!</h3>
                    <p class="text-xs pos-text-muted">
                        <kbd class="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-700 rounded text-[10px] font-bold">Enter</kbd> Ticket &nbsp;|&nbsp;
                        <kbd class="px-1.5 py-0.5 bg-slate-200 dark:bg-slate-700 rounded text-[10px] font-bold">Esc</kbd> Nueva Venta
                    </p>
                </div>

                <!-- Print Options -->
                <div class="grid grid-cols-2 gap-3">
                    <a
                        x-ref="ticketBtn"
                        href="{{ route('filament.documentos.ticket', ['documento' => $createdDocumentoId]) }}"
                        target="_blank"
                        class="px-4 py-3 rounded-xl border-2 border-blue-600 bg-blue-600/10 text-blue-600 dark:text-blue-400 dark:border-blue-500 hover:bg-blue-600/20 font-bold text-sm flex items-center justify-center gap-2 transition focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        🖨️ Ticket <span class="text-[10px] opacity-60">(Enter)</span>
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
wire:model.live.debounce.300ms="clienteDocumento"
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
                        <label class="block font-semibold pos-text-muted mb-1">Correo electrónico (Opcional)</label>
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
                        <label class="block font-semibold pos-text-muted mb-1">Correo electrónico</label>
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

    <!-- Modal de Ingreso Rapido POS -->
    @if ($showIngresoRapidoModal)
        <div 
            x-data
            x-init="setTimeout(() => $refs.cantidadInput?.focus(), 150)"
            wire:click.self="cerrarIngresoRapido"
            class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350"
            style="z-index: 99999 !important;"
        >
            <div class="pos-card p-6 max-w-lg w-full mx-4 space-y-5 text-left shadow-2xl border pos-border bg-white/95 dark:bg-slate-900/95 rounded-2xl">
                <div class="flex items-start justify-between gap-4 border-b pos-border pb-4">
                    <div>
                        <h3 class="text-base font-black uppercase tracking-wider pos-text text-slate-900 dark:text-white">
                            Ingreso rapido para venta
                        </h3>
                        <p class="text-[11px] pos-text-muted mt-1">
                            Se creara un lote ingreso-rapido sin vencimiento y se agregara al carrito.
                        </p>
                    </div>
                    <button type="button" wire:click="cerrarIngresoRapido" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xl leading-none">
                        &times;
                    </button>
                </div>

                <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-3 text-[11px] font-semibold text-amber-700 dark:text-amber-200">
                    Este ingreso es solo para vender rapido. Para compras completas usa el modulo de compras.
                </div>

                <div class="space-y-4">
                    @if ($ingresoRapidoCrearProducto)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Codigo de barra</label>
                                <input
                                    type="text"
                                    wire:model.live="ingresoRapidoCodigoBarra"
                                    class="w-full pos-input rounded-xl py-2.5 px-3 text-sm font-semibold focus:outline-none"
                                    placeholder="Opcional, editable"
                                >
                                @error('ingresoRapidoCodigoBarra')
                                    <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Presentacion nueva</label>
                                <input
                                    type="text"
                                    wire:model.live="ingresoRapidoPresentacionNombre"
                                    class="w-full pos-input rounded-xl py-2.5 px-3 text-sm font-semibold focus:outline-none"
                                    placeholder="Unidad, Botella 600ml, Caja..."
                                >
                                @error('ingresoRapidoPresentacionNombre')
                                    <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Buscar producto existente o escribir nuevo</label>
                            <div
                                class="relative"
                                x-data="{
                                    selectedQuickIndex: -1,
                                    quickResultsCount: 0,
                                    updateQuickResultsCount() {
                                        this.quickResultsCount = $wire.ingresoRapidoProductosResultados ? $wire.ingresoRapidoProductosResultados.length : 0;
                                        this.selectedQuickIndex = this.quickResultsCount > 0 ? 0 : -1;
                                    },
                                    selectQuickNext() {
                                        if (this.quickResultsCount > 0) {
                                            this.selectedQuickIndex = (this.selectedQuickIndex + 1) % this.quickResultsCount;
                                            this.scrollQuickToActive();
                                        }
                                    },
                                    selectQuickPrev() {
                                        if (this.quickResultsCount > 0) {
                                            this.selectedQuickIndex = (this.selectedQuickIndex - 1 + this.quickResultsCount) % this.quickResultsCount;
                                            this.scrollQuickToActive();
                                        }
                                    },
                                    scrollQuickToActive() {
                                        this.$nextTick(() => {
                                            const activeEl = this.$el.querySelector('[data-quick-index=\'' + this.selectedQuickIndex + '\']');
                                            if (activeEl) {
                                                activeEl.scrollIntoView({ block: 'nearest' });
                                            }
                                        });
                                    },
                                    selectQuickCurrent() {
                                        if (this.selectedQuickIndex >= 0) {
                                            const activeEl = this.$el.querySelector('[data-quick-index=\'' + this.selectedQuickIndex + '\']');
                                            if (activeEl) {
                                                activeEl.click();
                                            }
                                        }
                                    }
                                }"
                                x-init="$watch('$wire.ingresoRapidoProductosResultados', () => updateQuickResultsCount())"
                                @keydown.arrow-down.prevent="selectQuickNext()"
                                @keydown.arrow-up.prevent="selectQuickPrev()"
                                @keydown.enter.prevent="selectQuickCurrent()"
                            >
                                <input
                                    type="text"
                                    wire:model.live.debounce.250ms="ingresoRapidoProductoSearch"
                                    class="w-full pos-input rounded-xl py-2.5 px-3 pr-24 text-sm font-semibold focus:outline-none"
                                    placeholder="Busca y selecciona, o escribe el nombre nuevo..."
                                >
                                <div class="pointer-events-none absolute right-3 top-2.5 hidden sm:flex items-center gap-1 rounded-lg bg-emerald-500/10 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                                    ↑ ↓ Enter
                                </div>

                                @if(count($ingresoRapidoProductosResultados) > 0)
                                    <div class="absolute left-0 right-0 z-50 mt-2 max-h-56 overflow-y-auto rounded-2xl border border-emerald-500/25 bg-emerald-50 dark:bg-slate-950 shadow-2xl shadow-emerald-950/20 divide-y divide-slate-200/80 dark:divide-slate-800 ring-1 ring-emerald-500/10 overflow-hidden">
                                        <div class="sticky top-0 z-10 flex items-center justify-between gap-3 bg-emerald-100 dark:bg-slate-900 px-3 py-2 border-b border-emerald-500/15">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Productos encontrados</span>
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400">Selecciona uno existente</span>
                                        </div>
                                        @foreach($ingresoRapidoProductosResultados as $productoModal)
                                            <button
                                                type="button"
                                                wire:click="seleccionarProductoIngresoRapido({{ $productoModal['id'] }})"
                                                data-quick-index="{{ $loop->index }}"
                                                :class="{ 'bg-emerald-500 dark:bg-emerald-600 ring-2 ring-inset ring-emerald-700 dark:ring-emerald-300 shadow-inner shadow-emerald-900/20': selectedQuickIndex === {{ $loop->index }} }"
                                                class="group w-full px-3 py-3 text-left hover:bg-emerald-500/10 transition text-slate-900 dark:text-white flex items-center justify-between gap-3"
                                            >
                                                <div class="min-w-0">
                                                    <div class="text-xs font-black text-slate-950 dark:text-white truncate">{{ $productoModal['nombre'] }}</div>
                                                    <div class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">{{ $productoModal['codigo'] ?: 'Sin codigo interno' }}</div>
                                                </div>
                                                <span class="rounded-lg bg-emerald-500/10 px-2 py-1 text-[9px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-300 border border-emerald-500/20 opacity-80 group-hover:opacity-100">
                                                    Usar
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if($ingresoRapidoProductoId)
                                <div class="flex items-center justify-between rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-3 py-2">
                                    <div>
                                        <div class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-300">Se agregara como presentacion de</div>
                                        <div class="text-xs font-black pos-text">{{ $ingresoRapidoProductoNombre }}</div>
                                    </div>
                                    <button type="button" wire:click="limpiarProductoIngresoRapido" class="text-[10px] font-black text-emerald-700 dark:text-emerald-300 hover:underline">
                                        Cambiar
                                    </button>
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed pos-border bg-slate-50/60 dark:bg-slate-950/30 px-3 py-2">
                                    <div class="text-[10px] font-black uppercase tracking-wider pos-text-muted">Si no seleccionas uno existente</div>
                                    <div class="text-xs font-semibold pos-text mt-0.5">
                                        Se creara producto nuevo: <span class="font-black">{{ $ingresoRapidoProductoNombre ?: 'Escribe un nombre' }}</span>
                                    </div>
                                    @error('ingresoRapidoProductoNombre')
                                        <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Unidades por presentacion</label>
                                <input
                                    type="number"
                                    min="1"
                                    step="1"
                                    wire:model.live="ingresoRapidoPresentacionCantidad"
                                    class="w-full pos-input rounded-xl py-2.5 px-3 text-sm font-black focus:outline-none"
                                >
                                @error('ingresoRapidoPresentacionCantidad')
                                    <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                                @enderror
                            </div>

                            @if((int) $ingresoRapidoPresentacionCantidad > 1)
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Base para descomprimir</label>
                                    <select
                                        wire:model.live="ingresoRapidoPresentacionBaseId"
                                        class="w-full pos-select rounded-xl py-2.5 px-3 text-sm font-semibold focus:outline-none"
                                    >
                                        <option value="">Unidad automatica</option>
                                        @foreach($ingresoRapidoPresentacionesBase as $base)
                                            <option value="{{ $base['id'] }}">{{ $base['nombre'] }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-[10px] pos-text-muted">Solo aparece si contiene mas de 1 unidad.</p>
                                    @error('ingresoRapidoPresentacionBaseId')
                                        <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                                    @enderror
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed pos-border bg-slate-50/60 dark:bg-slate-950/30 px-3 py-2 flex items-center">
                                    <p class="text-[10px] font-semibold pos-text-muted">Para venta rapida normal deja 1. Si es caja/pack, sube la cantidad y podras elegir base.</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="rounded-xl border pos-border bg-slate-50/80 dark:bg-slate-950/40 px-4 py-3">
                            <div class="text-[10px] font-black uppercase tracking-wider pos-text-muted">Producto seleccionado</div>
                            <div class="mt-1 text-sm font-black pos-text">{{ $ingresoRapidoProductoNombre }}</div>
                            <div class="text-[11px] pos-text-muted">{{ $ingresoRapidoPresentacionNombre }}</div>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Cantidad</label>
                            <input
                                x-ref="cantidadInput"
                                type="number"
                                min="0.001"
                                step="0.001"
                                wire:model.live="ingresoRapidoCantidad"
                                class="w-full pos-input rounded-xl py-2.5 px-3 text-sm font-black focus:outline-none"
                            >
                            @error('ingresoRapidoCantidad')
                                <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Precio venta</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                wire:model.live="ingresoRapidoPrecioVenta"
                                class="w-full pos-input rounded-xl py-2.5 px-3 text-sm font-black focus:outline-none"
                            >
                            @error('ingresoRapidoPrecioVenta')
                                <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider pos-text-muted mb-1.5">Costo opcional</label>
                            <input
                                type="number"
                                min="0"
                                step="0.01"
                                wire:model.live="ingresoRapidoCosto"
                                class="w-full pos-input rounded-xl py-2.5 px-3 text-sm font-black focus:outline-none"
                                placeholder="0.00"
                            >
                            @error('ingresoRapidoCosto')
                                <span class="text-rose-500 text-[10px] block mt-1 font-semibold">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t pos-border">
                    <button
                        type="button"
                        wire:click="cerrarIngresoRapido"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl transition"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="guardarIngresoRapido"
                        wire:loading.attr="disabled"
                        wire:target="guardarIngresoRapido"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 text-white font-extrabold text-xs rounded-xl transition shadow-md shadow-emerald-500/10"
                    >
                        <span wire:loading.remove wire:target="guardarIngresoRapido">Guardar y agregar</span>
                        <span wire:loading wire:target="guardarIngresoRapido">Guardando...</span>
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

    <!-- Modal de Cerrar Caja -->
    @if ($showCerrarCajaModal)
        <div wire:click.self="$set('showCerrarCajaModal', false)" class="fixed inset-0 flex items-center justify-center bg-slate-950/70 backdrop-blur-md transition-all duration-350" style="z-index: 99999 !important;">
            <div class="pos-card p-6 max-w-lg w-full mx-4 space-y-5 text-left shadow-2xl border pos-border bg-white dark:bg-slate-900 rounded-2xl text-slate-800 dark:text-slate-200">
                <!-- Header con Candado -->
                <div class="flex items-center gap-3 border-b pos-border pb-3">
                    <div class="p-2 rounded-lg bg-rose-500/10 text-rose-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black uppercase tracking-wider pos-text text-slate-800 dark:text-white">Cerrar Sesión de Caja</h3>
                        <p class="text-[10px] pos-text-muted text-slate-500 dark:text-slate-400">Por favor, cuente el efectivo físico en caja y verifique la diferencia antes de cerrar.</p>
                    </div>
                </div>

                <!-- Dashboard de Resumen (Saldo Inicial y Esperado) -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Inicial -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 dark:text-blue-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Saldo Inicial</p>
                            <p class="text-sm font-black text-slate-800 dark:text-white">S/ {{ number_format($this->initialCajaBalance, 2) }}</p>
                        </div>
                    </div>

                    <!-- Esperado -->
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/5 border border-emerald-500/20 dark:border-emerald-500/10 flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-emerald-500 text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Saldo Esperado</p>
                            <p class="text-base font-black text-emerald-700 dark:text-emerald-300">S/ {{ number_format($this->expectedCajaBalance, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Input de Dinero Físico -->
                <div class="space-y-1.5" x-data="{ saldoLocal: @entangle('cerrarCajaSaldoReal'), saldoEsperado: {{ $this->expectedCajaBalance }}, get saldoNum() { let v = String(this.saldoLocal ?? '').replace(/,/g, '.').replace(/[^0-9.]/g, ''); if (v === '' || v === '.') return NaN; let p = v.split('.'); if (p.length > 2) v = p[0] + '.' + p.slice(1).join(''); let n = parseFloat(v); return isNaN(n) ? NaN : n; }, get difLocal() { if (this.saldoLocal === '' || this.saldoLocal === null) return null; if (isNaN(this.saldoNum)) return null; return parseFloat((this.saldoNum - this.saldoEsperado).toFixed(2)); } }">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Dinero físico en caja (Efectivo)</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-sm font-extrabold text-slate-400 dark:text-slate-500">S/</span>
                        <input
                            type="text"
                            inputmode="decimal"
                            pattern="[0-9]*[.,]?[0-9]*"
                            x-model="saldoLocal"
                            class="w-full text-sm font-black tracking-tight text-slate-800 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl py-2.5 pl-10 pr-4 transition shadow-inner"
                            placeholder="0.00"
                        >
                    </div>
                    @error('cerrarCajaSaldoReal')
                        <span class="text-rose-500 text-[10px] block mt-0.5 font-normal">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Panel Dinámico de Diferencia -->
                <div>
                    <template x-if="difLocal === null">
                        <div class="flex items-center gap-2.5 p-3.5 rounded-xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20 text-slate-400 dark:text-slate-500 justify-center text-[11px] font-semibold py-4">
                            <svg class="h-4 w-4 animate-pulse text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>Ingrese el dinero físico para calcular la diferencia</span>
                        </div>
                    </template>
                    <template x-if="difLocal !== null && difLocal > 0">
                        <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/20 dark:bg-amber-500/5 dark:border-amber-500/20 flex items-start gap-3 shadow-sm">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h4 class="text-[11px] font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider">Sobrante Detectado</h4>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-lg font-black text-amber-700 dark:text-amber-300" x-text="'+ S/ ' + difLocal.toFixed(2)"></span>
                                </div>
                                <p class="text-[9px] text-amber-605 dark:text-amber-400/90 pt-0.5 leading-normal">El dinero real supera el esperado. Explique la diferencia en observaciones.</p>
                            </div>
                        </div>
                    </template>
                    <template x-if="difLocal !== null && difLocal < 0">
                        <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 dark:bg-rose-500/5 dark:border-rose-500/20 flex items-start gap-3 shadow-sm">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h4 class="text-[11px] font-bold text-rose-800 dark:text-rose-400 uppercase tracking-wider">Faltante Detectado</h4>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-lg font-black text-rose-700 dark:text-rose-300" x-text="'S/ ' + Math.abs(difLocal).toFixed(2)"></span>
                                </div>
                                <p class="text-[9px] text-rose-605 dark:text-rose-400/90 pt-0.5 leading-normal">El dinero real es menor que el esperado. La diferencia quedará registrada.</p>
                            </div>
                        </div>
                    </template>
                    <template x-if="difLocal !== null && difLocal === 0">
                        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 dark:bg-emerald-500/5 dark:border-emerald-500/20 flex items-start gap-3 shadow-sm">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-500 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="space-y-0.5">
                                <h4 class="text-[11px] font-bold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">¡Caja Cuadrada!</h4>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-lg font-black text-emerald-700 dark:text-emerald-300">S/ 0.00</span>
                                </div>
                                <p class="text-[9px] text-emerald-605 dark:text-emerald-400/90 pt-0.5 leading-normal">El saldo coincide exactamente con el teórico.</p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Observaciones -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Observaciones</label>
                    <textarea 
                        wire:model="cerrarCajaObservaciones"
                        rows="2"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 dark:text-slate-200 transition resize-none" 
                        placeholder="Ingrese comentarios sobre el cierre de caja (opcional)..."
                        maxLength="500"
                    ></textarea>
                </div>

                <!-- Acciones del Modal -->
                <div class="flex justify-end gap-3 pt-3 border-t pos-border">
                    <button 
                        type="button" 
                        wire:click="$set('showCerrarCajaModal', false)"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 font-bold text-xs rounded-lg transition"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeCerrarCaja"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs rounded-lg transition shadow-md shadow-rose-500/10"
                    >
                        Confirmar y Cerrar Caja
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

            // --- INTUITIVE POS FOCUS REDIRECT ---
            const searchInput = document.getElementById('search-producto-input');
            if (searchInput) searchInput.focus();

            const focusSearchInput = () => {
                const active = document.activeElement;
                if (active && active.tagName !== 'INPUT' && active.tagName !== 'TEXTAREA' && active.tagName !== 'SELECT') {
                    const input = document.getElementById('search-producto-input');
                    if (input) input.focus();
                }
            };

            window.addEventListener('focus-search-producto', () => {
                const input = document.getElementById('search-producto-input');
                if (input) {
                    input.focus();
                    input.select();
                }
            });

            document.addEventListener('click', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
                setTimeout(focusSearchInput, 50);
            });
        });

        // For Livewire 3 request lifecycle (only if no input is focused)
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('request', ({ respond }) => {
                respond(() => {
                    const active = document.activeElement;
                    if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT')) return;
                    setTimeout(() => {
                        const input = document.getElementById('search-producto-input');
                        if (input) input.focus();
                    }, 50);
                });
            });
        });
    </script>
    {{-- Modal incluido dentro del div raíz de Livewire para asegurar el correcto renderizado y reactividad --}}
    @include('livewire.ventas.modals.buscar-venta')
</div>
