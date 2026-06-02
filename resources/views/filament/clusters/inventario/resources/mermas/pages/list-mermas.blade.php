<x-filament-panels::page>
    <div class="space-y-6 animate-fade-in">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="kpi-card kpi-rose p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Registros Totales</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format($this->stats['totalRegistros']) }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Historial consolidado de incidencias</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-500/12 text-rose-600">
                        <x-heroicon-m-archive-box-x-mark class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-amber p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Unidades Retiradas</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format($this->stats['totalUnidades']) }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Suma de productos afectados</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500/12 text-amber-600">
                        <x-heroicon-m-cube-transparent class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-orange p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Impacto Estimado</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">S/ {{ number_format($this->stats['impactoEstimado'], 2) }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Calculado con costo de compra</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-500/12 text-orange-600">
                        <x-heroicon-m-banknotes class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <div class="kpi-card kpi-indigo p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Últimos 7 Días</p>
                        <p class="mt-2 text-3xl font-black text-slate-950">{{ number_format($this->stats['ultimosSieteDias']) }}</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500">Movimiento reciente de control</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-500/12 text-indigo-600">
                        <x-heroicon-m-chart-bar-square class="h-5 w-5" />
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card overflow-hidden rounded-[28px] border border-slate-200/80 bg-white/95 shadow-sm p-3 lg:p-4">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
