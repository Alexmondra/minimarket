<x-filament-panels::page @class(['escritorio-dashboard'])>
    {{-- Row 1: KPI Cards --}}
    <livewire:escritorio.kpi-cards />

    {{-- Row 2: Charts — daily sales + monthly sales --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <livewire:escritorio.ventas-por-dia-chart />
        <livewire:escritorio.ventas-mensuales-chart />
    </div>

    {{-- Row 3: Payment methods + Profit + Top products --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <livewire:escritorio.metodos-pago-chart />
        <livewire:escritorio.ganancias-chart />
        <livewire:escritorio.top-productos-chart />
    </div>

    {{-- Row 4: Alerts + Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-6 mt-6">
        <livewire:escritorio.alertas-inteligentes />
        <livewire:escritorio.actividad-reciente />
    </div>

    {{-- Row 5: Top Products table --}}
    <div class="mt-6">
        <livewire:escritorio.top-productos />
    </div>
</x-filament-panels::page>
