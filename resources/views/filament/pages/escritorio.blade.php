<x-filament-panels::page @class(['escritorio-dashboard'])>
    @vite(['resources/js/app.js'])

    @can('reportes.ver')
        {{-- ADMIN: KPI Cards --}}
        <livewire:escritorio.kpi-cards lazy />

        {{-- ADMIN: Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <livewire:escritorio.ventas-por-dia-chart lazy />
            <livewire:escritorio.ventas-mensuales-chart lazy />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <livewire:escritorio.metodos-pago-chart lazy />
            <livewire:escritorio.ganancias-chart lazy />
            <livewire:escritorio.top-productos-chart lazy />
        </div>

        {{-- ADMIN: Alerts + Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <livewire:escritorio.alertas-inteligentes lazy />
            <livewire:escritorio.actividad-reciente lazy />
        </div>

        {{-- ADMIN: Top Products table --}}
        <div class="mt-6">
            <livewire:escritorio.top-productos lazy />
        </div>
    @elsecan('ventas.crear')
        {{-- VENDEDOR: Acciones Rápidas --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-5">
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 text-white p-2.5 rounded-xl shadow-lg shadow-orange-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6h16.5M3.75 12h16.5m-16.5 6h16.5"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white">Acciones Rápidas</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">¿Qué deseas hacer hoy?</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                @can('ventas.crear')
                    <a href="{{ url('/admin/documentos/registrar') }}"
                       class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-all group">
                        <div class="bg-emerald-500 text-white p-2 rounded-lg shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300 group-hover:translate-x-0.5 transition-transform">Nueva Venta</span>
                    </a>
                @endcan
                @can('clientes.ver')
                    <a href="{{ url('/admin/clientes') }}"
                       class="flex items-center gap-3 p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-all group">
                        <div class="bg-blue-500 text-white p-2 rounded-lg shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-blue-700 dark:text-blue-300 group-hover:translate-x-0.5 transition-transform">Buscar Cliente</span>
                    </a>
                @endcan
                @can('productos.ver')
                    <a href="{{ url('/admin/stock-sucursal/stock-sucursals') }}"
                       class="flex items-center gap-3 p-4 rounded-xl bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/20 hover:bg-purple-100 dark:hover:bg-purple-500/20 transition-all group">
                        <div class="bg-purple-500 text-white p-2 rounded-lg shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-purple-700 dark:text-purple-300 group-hover:translate-x-0.5 transition-transform">Ver Productos</span>
                    </a>
                @endcan
            </div>
        </div>

        {{-- VENDEDOR: Alerts --}}
        <div class="mt-6">
            <livewire:escritorio.alertas-inteligentes lazy />
        </div>

        {{-- VENDEDOR: Recent Activity --}}
        <div class="mt-6">
            <livewire:escritorio.actividad-reciente lazy />
        </div>

        {{-- VENDEDOR: Top Products --}}
        <div class="mt-6">
            <livewire:escritorio.top-productos lazy />
        </div>
    @else
        {{-- USER MINIMO: solo bienvenida --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 text-center shadow-sm">
            <div class="text-slate-400 dark:text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                </svg>
                <p class="text-sm font-semibold">Bienvenido al sistema</p>
                <p class="text-xs mt-1">Consulta con el administrador para acceder a más funcionalidades.</p>
            </div>
        </div>
    @endcan
</x-filament-panels::page>

