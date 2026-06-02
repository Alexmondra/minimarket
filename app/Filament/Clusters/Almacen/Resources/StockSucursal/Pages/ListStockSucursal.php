<?php

namespace App\Filament\Clusters\Almacen\Resources\StockSucursal\Pages;

use App\Filament\Clusters\Almacen\Resources\StockSucursal\StockSucursalResource;
use App\Filament\Clusters\Compras\Resources\Compras\CompraResource;
use App\Models\Categoria;
use App\Models\ProductoPresentacion;
use App\Models\ProductoSucursal;
use App\Support\SucursalContext;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\WithPagination;

class ListStockSucursal extends Page
{
    use WithPagination;

    protected static string $resource = StockSucursalResource::class;

    protected string $view = 'filament.clusters.almacen.resources.stock-sucursal.pages.list-stock-sucursal';

    // Filters & View Mode
    public string $viewMode = 'cards'; // cards or list
    public string $search = '';
    public string $selectedCategoriaId = 'all';
    public string $selectedStockEstado = 'all'; // all, bajo, agotado, normal
    public ?int $selectedSucursalId = null;

    // Branches list
    public array $sucursales = [];

    // Presentation Lots Modal
    public bool $showLotesModal = false;
    public ?int $selectedProductoId = null;
    public ?int $selectedPresentacionId = null;
    public ?ProductoPresentacion $selectedPresentacion = null;
    public $lotesDetails = [];

    // Individual pricing edit modal
    public bool $showEditPriceModal = false;
    public ?int $editingProductoSucursalId = null;
    public string $editingLoteCodigo = '';
    public float $editingPrecio = 0.0;
    public float $editingPrecioMayorista = 0.0;
    public int $editingMinimoMayorista = 12;

    // General pricing edit modal
    public bool $showGeneralPriceModal = false;
    public float $generalPrecio = 0.0;
    public float $generalPrecioMayorista = 0.0;
    public int $generalMinimoMayorista = 12;

    // Stock Mínimo configuration modal
    public bool $showStockMinimoModal = false;
    public int $editingStockMinimo = 0;

    protected $queryString = [
        'viewMode' => ['except' => 'cards'],
        'search' => ['except' => ''],
        'selectedCategoriaId' => ['except' => 'all'],
        'selectedStockEstado' => ['except' => 'all'],
        'selectedSucursalId' => ['except' => ''],
    ];

    protected $listeners = [
        'ajusteGuardado' => '$refresh',
    ];

    public function mount(): void
    {
        $context = app(SucursalContext::class);
        $user = auth()->user();

        // Normalize session
        $context->normalizeSession($user);

        // Fetch allowed branches
        $this->sucursales = $context->allowedSucursales($user)
            ->map(fn ($s): array => [
                'id' => $s->id,
                'nombre' => $s->nombre_sucursal,
            ])
            ->toArray();

        // Active sucursal from top-bar selector prioritizes over local filter
        $activeId = $context->activeSucursalId();
        if ($activeId) {
            $this->selectedSucursalId = $activeId;
        } else {
            // Restore from query parameter if valid, otherwise keep null (meaning 'Todas')
            $qsId = request()->query('selectedSucursalId');
            if (is_numeric($qsId)) {
                $this->selectedSucursalId = (int) $qsId;
            } else {
                $this->selectedSucursalId = null;
            }
        }
    }

    public function updatedSelectedSucursalId($value): void
    {
        if ($value === 'all') {
            $this->selectedSucursalId = null;
        } else {
            $this->selectedSucursalId = (int) $value;
        }
        $this->resetPage();
    }

    public function getIsSucursalLockedProperty(): bool
    {
        return app(SucursalContext::class)->activeSucursalId() !== null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategoriaId(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStockEstado(): void
    {
        $this->resetPage();
    }

    public function getCategoriasProperty()
    {
        return Categoria::where('empresa_id', auth()->user()->empresa_id)
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();
    }

    public function getStatsProperty(): array
    {
        $activeId = app(SucursalContext::class)->activeSucursalId();
        $sucursalId = $activeId ?: $this->selectedSucursalId;
        $allowedIds = app(SucursalContext::class)->allowedSucursalIds();

        $baseQuery = ProductoSucursal::query()
            ->join('lote_presentacion', 'producto_sucursal.lote_presentacion_id', '=', 'lote_presentacion.id')
            ->whereHas('producto', function ($q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            });

        if ($sucursalId) {
            $baseQuery->where('producto_sucursal.sucursal_id', $sucursalId);
        } else {
            $baseQuery->whereIn('producto_sucursal.sucursal_id', $allowedIds->all());
        }

        $totalItems = (int) $baseQuery->sum('lote_presentacion.stock');

        $groupedQuery = ProductoSucursal::query()
            ->select('lote_presentacion.producto_presentacion_id')
            ->selectRaw('SUM(lote_presentacion.stock) as total_stock')
            ->selectRaw('MAX(producto_sucursal.stock_minimo) as max_minimo')
            ->join('lote_presentacion', 'producto_sucursal.lote_presentacion_id', '=', 'lote_presentacion.id')
            ->whereHas('producto', function ($q) {
                $q->where('empresa_id', auth()->user()->empresa_id);
            });

        if ($sucursalId) {
            $groupedQuery->where('producto_sucursal.sucursal_id', $sucursalId);
        } else {
            $groupedQuery->whereIn('producto_sucursal.sucursal_id', $allowedIds->all());
        }

        $grouped = $groupedQuery->groupBy('lote_presentacion.producto_presentacion_id')->get();

        $totalPresentations = $grouped->count();
        $lowStockCount = $grouped->filter(fn ($item) => $item->total_stock > 0 && $item->total_stock <= $item->max_minimo)->count();
        $outOfStockCount = $grouped->filter(fn ($item) => $item->total_stock == 0)->count();

        return [
            'total_items' => $totalItems,
            'total_presentations' => $totalPresentations,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
        ];
    }

    public function getExistenciasProperty()
    {
        $activeId = app(SucursalContext::class)->activeSucursalId();
        $sucursalId = $activeId ?: $this->selectedSucursalId;
        $allowedIds = app(SucursalContext::class)->allowedSucursalIds();

        if ($allowedIds->isEmpty()) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        }

        $query = ProductoSucursal::query()
            ->select(
                'producto_sucursal.producto_id',
                'lote_presentacion.producto_presentacion_id',
                'producto_presentacion.tipo_presentacion',
                'producto_presentacion.imagen',
                'productos.nombre as producto_nombre',
                'productos.categoria_id',
                'categorias.nombre as categoria_nombre'
            )
            ->selectRaw('SUM(lote_presentacion.stock) as total_stock')
            ->selectRaw('MAX(producto_sucursal.stock_minimo) as max_stock_minimo')
            ->selectRaw('MIN(producto_sucursal.precio) as min_precio')
            ->selectRaw('MAX(producto_sucursal.precio) as max_precio')
            ->join('lote_presentacion', 'producto_sucursal.lote_presentacion_id', '=', 'lote_presentacion.id')
            ->join('producto_presentacion', 'lote_presentacion.producto_presentacion_id', '=', 'producto_presentacion.id')
            ->join('productos', 'producto_sucursal.producto_id', '=', 'productos.id')
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('productos.empresa_id', auth()->user()->empresa_id);

        if ($sucursalId) {
            $query->where('producto_sucursal.sucursal_id', $sucursalId);
        } else {
            $query->whereIn('producto_sucursal.sucursal_id', $allowedIds->all());
        }

        $query->groupBy(
            'producto_sucursal.producto_id',
            'lote_presentacion.producto_presentacion_id',
            'producto_presentacion.tipo_presentacion',
            'producto_presentacion.imagen',
            'productos.nombre',
            'productos.categoria_id',
            'categorias.nombre'
        );

        // Sorting priority: Agotados (stock = 0) first, then Bajo Stock (stock <= min_stock), then Normal.
        $query->orderByRaw('
            CASE 
                WHEN SUM(lote_presentacion.stock) = 0 THEN 0
                WHEN SUM(lote_presentacion.stock) <= MAX(producto_sucursal.stock_minimo) THEN 1
                ELSE 2
            END ASC
        ')
        ->orderBy('productos.nombre', 'asc');

        if (! empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('productos.nombre', 'like', $searchTerm)
                    ->orWhere('producto_presentacion.tipo_presentacion', 'like', $searchTerm);
            });
        }

        if ($this->selectedCategoriaId !== 'all') {
            $query->where('productos.categoria_id', $this->selectedCategoriaId);
        }

        if ($this->selectedStockEstado !== 'all') {
            if ($this->selectedStockEstado === 'agotado') {
                $query->havingRaw('SUM(lote_presentacion.stock) = 0');
            } elseif ($this->selectedStockEstado === 'bajo') {
                $query->havingRaw('SUM(lote_presentacion.stock) <= MAX(producto_sucursal.stock_minimo)');
            } elseif ($this->selectedStockEstado === 'normal') {
                $query->havingRaw('SUM(lote_presentacion.stock) > MAX(producto_sucursal.stock_minimo)');
            }
        }

        return $query->paginate(12);
    }

    public function verLotes(int $productoId, int $presentacionId): void
    {
        $this->selectedProductoId = $productoId;
        $this->selectedPresentacionId = $presentacionId;

        $this->selectedPresentacion = ProductoPresentacion::with(['producto', 'unidadMedida'])
            ->find($presentacionId);

        $activeId = app(SucursalContext::class)->activeSucursalId();
        $sucursalId = $activeId ?: $this->selectedSucursalId;

        $query = ProductoSucursal::query()
            ->select('producto_sucursal.*')
            ->join('lote_presentacion', 'producto_sucursal.lote_presentacion_id', '=', 'lote_presentacion.id')
            ->join('lotes', 'lote_presentacion.lote_id', '=', 'lotes.id')
            ->where('lote_presentacion.producto_presentacion_id', $presentacionId)
            ->with(['lotePresentacion.lote', 'sucursal']);

        if ($sucursalId) {
            $query->where('producto_sucursal.sucursal_id', $sucursalId);
        } else {
            $allowedIds = app(SucursalContext::class)->allowedSucursalIds();
            $query->whereIn('producto_sucursal.sucursal_id', $allowedIds->all());
        }

        $this->lotesDetails = $query->get();

        $this->showLotesModal = true;
    }

    public function toggleActivo(int $productoSucursalId): void
    {
        $ps = ProductoSucursal::find($productoSucursalId);
        if ($ps) {
            $ps->update(['activo' => ! $ps->activo]);

            Notification::make()
                ->title($ps->activo ? 'Lote activado con éxito' : 'Lote desactivado con éxito')
                ->success()
                ->send();

            $this->verLotes($this->selectedProductoId, $this->selectedPresentacionId);
        }
    }

    public function openEditPrice(int $productoSucursalId): void
    {
        $ps = ProductoSucursal::with('lotePresentacion.lote')->find($productoSucursalId);
        if ($ps) {
            $this->editingProductoSucursalId = $productoSucursalId;
            $this->editingLoteCodigo = $ps->lotePresentacion?->lote?->codigo_lote ?? 'Sin código';
            $this->editingPrecio = (float) $ps->precio;
            $this->editingPrecioMayorista = (float) $ps->precio_mayorista;
            $this->editingMinimoMayorista = (int) $ps->minimo_mayorista;
            $this->showEditPriceModal = true;
        }
    }

    public function saveIndividualPrice(): void
    {
        $this->validate([
            'editingPrecio' => 'required|numeric|min:0',
            'editingPrecioMayorista' => 'required|numeric|min:0',
            'editingMinimoMayorista' => 'required|integer|min:1',
        ]);

        $ps = ProductoSucursal::find($this->editingProductoSucursalId);
        if ($ps) {
            DB::transaction(function () use ($ps) {
                $ps->update([
                    'precio' => $this->editingPrecio,
                    'precio_mayorista' => $this->editingPrecioMayorista,
                    'minimo_mayorista' => $this->editingMinimoMayorista,
                ]);
            });

            Notification::make()
                ->title('Precio de lote actualizado con éxito')
                ->success()
                ->send();

            $this->showEditPriceModal = false;
            $this->verLotes($this->selectedProductoId, $this->selectedPresentacionId);
        }
    }

    public function openGeneralPrice(): void
    {
        $first = collect($this->lotesDetails)->first();
        if ($first) {
            $this->generalPrecio = (float) $first->precio;
            $this->generalPrecioMayorista = (float) $first->precio_mayorista;
            $this->generalMinimoMayorista = (int) $first->minimo_mayorista;
        } else {
            $this->generalPrecio = 0.0;
            $this->generalPrecioMayorista = 0.0;
            $this->generalMinimoMayorista = 12;
        }
        $this->showGeneralPriceModal = true;
    }

    public function saveGeneralPrice(): void
    {
        $this->validate([
            'generalPrecio' => 'required|numeric|min:0',
            'generalPrecioMayorista' => 'required|numeric|min:0',
            'generalMinimoMayorista' => 'required|integer|min:1',
        ]);

        $ids = collect($this->lotesDetails)->pluck('id')->all();

        if (count($ids) > 0) {
            DB::transaction(function () use ($ids) {
                ProductoSucursal::whereIn('id', $ids)->update([
                    'precio' => $this->generalPrecio,
                    'precio_mayorista' => $this->generalPrecioMayorista,
                    'minimo_mayorista' => $this->generalMinimoMayorista,
                ]);
            });

            Notification::make()
                ->title('Precio general y escala aplicados a todos los lotes')
                ->success()
                ->send();

            $this->showGeneralPriceModal = false;
            $this->verLotes($this->selectedProductoId, $this->selectedPresentacionId);
        }
    }

    public function openStockMinimo(): void
    {
        $first = collect($this->lotesDetails)->first();
        $this->editingStockMinimo = $first ? (int) $first->stock_minimo : 0;
        $this->showStockMinimoModal = true;
    }

    public function saveStockMinimo(): void
    {
        $this->validate([
            'editingStockMinimo' => 'required|integer|min:0',
        ]);

        $ids = collect($this->lotesDetails)->pluck('id')->all();

        if (count($ids) > 0) {
            DB::transaction(function () use ($ids) {
                ProductoSucursal::whereIn('id', $ids)->update([
                    'stock_minimo' => $this->editingStockMinimo,
                ]);
            });

            Notification::make()
                ->title('Stock mínimo configurado con éxito')
                ->success()
                ->send();

            $this->showStockMinimoModal = false;
            $this->verLotes($this->selectedProductoId, $this->selectedPresentacionId);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('registrarCompra')
                ->label('Registrar Compra')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(CompraResource::getUrl('registrar'))
                ->openUrlInNewTab(false),

            Action::make('ajusteEntrada')
                ->label('Ajuste de Entrada')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('success')
                ->action('dispatchAbrirAjusteEntrada'),

            Action::make('ajusteSalida')
                ->label('Ajuste de Salida')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('danger')
                ->action('dispatchAbrirAjusteSalida'),
        ];
    }

    public function dispatchAbrirAjusteEntrada(): void
    {
        $this->dispatch('abrirAjusteEntrada', sucursalId: $this->selectedSucursalId)->to('almacen.ajuste-stock');
    }

    public function dispatchAbrirAjusteSalida(): void
    {
        $this->dispatch('abrirAjusteSalida', sucursalId: $this->selectedSucursalId)->to('almacen.ajuste-stock');
    }

    public function getFooter(): ?View
    {
        return view('filament.clusters.almacen.resources.stock-sucursal.pages.ajuste-stock-modal');
    }
}
