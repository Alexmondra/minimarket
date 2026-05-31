<?php

namespace App\Livewire\Escritorio;

use App\Support\Reportes\ReporteQueryBuilder;
use Livewire\Component;

class AlertasInteligentes extends Component
{
    public array $alertas = [];
    public bool $loaded = false;

    public function mount(): void
    {
        $this->loadAlertas();
    }

    public function loadAlertas(): void
    {
        $qb = app(ReporteQueryBuilder::class);

        $this->alertas = [];

        // Productos vencidos
        $vencidos = $qb->productosVencidos()->count();
        if ($vencidos > 0) {
            $this->alertas[] = [
                'type' => 'danger',
                'icon' => 'heroicon-o-x-circle',
                'title' => 'Productos Vencidos',
                'count' => $vencidos,
                'desc' => 'Hay productos con fecha de vencimiento pasada y stock disponible.',
                'color' => 'rose',
            ];
        }

        // Por vencer 7 días
        $porVencer7 = $qb->productosPorVencer(7)->count();
        if ($porVencer7 > 0) {
            $this->alertas[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-clock',
                'title' => 'Por Vencer (7 días)',
                'count' => $porVencer7,
                'desc' => 'Productos que vencen en la próxima semana.',
                'color' => 'amber',
            ];
        }

        // Por vencer 30 días
        $porVencer30 = $qb->productosPorVencer(30)->count();
        if ($porVencer30 > 0) {
            $this->alertas[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-calendar-days',
                'title' => 'Por Vencer (30 días)',
                'count' => $porVencer30,
                'desc' => 'Productos que vencen en el próximo mes.',
                'color' => 'orange',
            ];
        }

        // Stock crítico
        $stockCritico = $qb->productosBajoStock()->count();
        if ($stockCritico > 0) {
            $this->alertas[] = [
                'type' => 'warning',
                'icon' => 'heroicon-o-exclamation-triangle',
                'title' => 'Stock Crítico',
                'count' => $stockCritico,
                'desc' => 'Productos con stock igual o por debajo del mínimo.',
                'color' => 'amber',
            ];
        }

        // Cajas abiertas
        $cajasAbiertas = $qb->cajasAbiertas()->count();
        if ($cajasAbiertas > 0) {
            $this->alertas[] = [
                'type' => 'info',
                'icon' => 'heroicon-o-lock-open',
                'title' => 'Cajas Abiertas',
                'count' => $cajasAbiertas,
                'desc' => 'Sesiones de caja que permanecen abiertas.',
                'color' => 'emerald',
            ];
        }

        $this->loaded = true;
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="glass-card p-5 space-y-3">
            <div class="skeleton h-4 w-28"></div>
            @for ($i=0; $i<3; $i++)
                <div class="skeleton h-20 w-full"></div>
            @endfor
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.escritorio.alertas-inteligentes');
    }
}
