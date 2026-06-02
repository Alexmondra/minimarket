<x-filament-panels::page @class(['escritorio-dashboard'])>
    {{-- Load Chart.js + chart registry for dashboard widgets --}}
    @vite(['resources/js/app.js'])

    {{-- Row 1: KPI Cards --}}
    <livewire:escritorio.kpi-cards lazy />

    {{-- Row 2: Charts — daily sales + monthly sales --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <livewire:escritorio.ventas-por-dia-chart lazy />
        <livewire:escritorio.ventas-mensuales-chart lazy />
    </div>

    {{-- Row 3: Payment methods + Profit + Top products --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <livewire:escritorio.metodos-pago-chart lazy />
        <livewire:escritorio.ganancias-chart lazy />
        <livewire:escritorio.top-productos-chart lazy />
    </div>

    {{-- Row 4: Alerts + Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-6 mt-6">
        <livewire:escritorio.alertas-inteligentes lazy />
        <livewire:escritorio.actividad-reciente lazy />
    </div>

    {{-- Row 5: Top Products table --}}
    <div class="mt-6">
        <livewire:escritorio.top-productos lazy />
    </div>
</x-filament-panels::page>
