<x-filament-panels::page>
    <div class="space-y-6 animate-fade-in">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            <div class="kpi-card kpi-indigo">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">📦 Total Lotes</span>
                    <div class="text-2xl sm:text-3xl font-black text-white">{{ $this->stats['total'] }}</div>
                </div>
            </div>
            <div class="kpi-card kpi-emerald">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">✅ Activos</span>
                    <div class="text-2xl sm:text-3xl font-black text-white">{{ $this->stats['activos'] }}</div>
                </div>
            </div>
            <div class="kpi-card kpi-amber">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">⏳ Por Vencer</span>
                    <div class="text-2xl sm:text-3xl font-black text-white">{{ $this->stats['por_vencer'] }}</div>
                </div>
            </div>
            <div class="kpi-card kpi-rose">
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider" style="color: rgba(255,255,255,0.75)">❌ Vencidos</span>
                    <div class="text-2xl sm:text-3xl font-black text-white">{{ $this->stats['vencidos'] }}</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        {{ $this->content }}
    </div>
</x-filament-panels::page>
