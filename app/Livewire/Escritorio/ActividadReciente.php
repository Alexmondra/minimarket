<?php

namespace App\Livewire\Escritorio;

use App\Models\Documento;
use App\Models\SessioneCaja;
use App\Support\Reportes\ReporteQueryBuilder;
use Livewire\Component;
use Illuminate\Support\Collection;

class ActividadReciente extends Component
{
    public array $actividades = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->loadActividad();
    }

    public function loadActividad(): void
    {
        $qb = app(ReporteQueryBuilder::class);

        // Últimas ventas
        $ventas = $qb->ventasBase()
            ->with(['user', 'cliente', 'sucursal'])
            ->latest('fecha_emision')
            ->limit(10)
            ->get()
            ->map(fn (Documento $d) => [
                'type' => 'venta',
                'icon_color' => 'emerald',
                'title' => 'Venta registrada',
                'desc' => $d->tipo_comprobante . ' — S/ ' . number_format($d->total_neto, 2),
                'time' => $d->created_at?->diffForHumans(),
                'user' => $d->user?->name,
                'sucursal' => $d->sucursal?->nombre_sucursal,
            ]);

        // Últimas anulaciones
        $anulaciones = $qb->ventasAnuladasBase()
            ->with(['user', 'sucursal'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Documento $d) => [
                'type' => 'anulacion',
                'icon_color' => 'rose',
                'title' => 'Documento anulado',
                'desc' => $d->tipo_comprobante . ' — S/ ' . number_format($d->total_neto, 2),
                'time' => $d->updated_at?->diffForHumans(),
                'user' => $d->user?->name,
                'sucursal' => $d->sucursal?->nombre_sucursal,
            ]);

        // Últimos movimientos de caja
        $cajas = $qb->cajasBase()
            ->with(['user', 'sucursal'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function (SessioneCaja $c) {
                $isOpen = $c->estado && !$c->fecha_cierre;
                return [
                    'type' => $isOpen ? 'caja_apertura' : 'caja_cierre',
                    'icon_color' => $isOpen ? 'emerald' : 'amber',
                    'title' => $isOpen ? 'Caja abierta' : 'Caja cerrada',
                    'desc' => 'S/ ' . number_format($c->saldo_inicial, 2) . ($isOpen ? ' inicial' : ' — Saldo real: S/ ' . number_format($c->saldo_real ?? 0, 2)),
                    'time' => ($isOpen ? $c->fecha_apertura : $c->fecha_cierre)?->diffForHumans(),
                    'user' => $c->user?->name,
                    'sucursal' => $c->sucursal?->nombre_sucursal,
                ];
            });

        // Merge and sort by time (most recent first). We use created_at/updated_at as proxy
        $this->actividades = $ventas->concat($anulaciones)->concat($cajas)
            ->sortByDesc('time')
            ->take(4)
            ->values()
            ->all();

        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-4">
            <div class="skeleton h-4 w-28"></div>
            @for ($i=0; $i<5; $i++)
                <div class="flex gap-3">
                    <div class="skeleton h-8 w-8 rounded-full shrink-0"></div>
                    <div class="space-y-2 flex-1">
                        <div class="skeleton h-3 w-24"></div>
                        <div class="skeleton h-3 w-full"></div>
                    </div>
                </div>
            @endfor
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.escritorio.actividad-reciente');
    }
}
