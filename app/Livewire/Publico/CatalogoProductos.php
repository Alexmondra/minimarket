<?php

namespace App\Livewire\Publico;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoSucursal;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Component;
use Throwable;

class CatalogoProductos extends Component
{
    public string $search = '';

    public string $categoriaId = '';

    public string $marcaId = '';

    public string $sucursalId = '';

    public int $limit = 12;

    public ?int $selectedProductId = null;

    public bool $showProductModal = false;

    public ?int $empresaId = null;

    protected ?bool $databaseReady = null;

    public function mount(): void
    {
        if (! $this->databaseReady()) {
            return;
        }

        $this->sucursalId = (string) (Sucursal::query()
            ->where('activo', true)
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->orderBy('nombre_sucursal')
            ->value('id') ?? '');
    }

    public function updatedSearch(): void
    {
        $this->resetListing();
    }

    public function updatedCategoriaId(): void
    {
        $this->resetListing();
    }

    public function updatedMarcaId(): void
    {
        $this->resetListing();
    }

    public function updatedSucursalId(): void
    {
        $this->resetListing();
    }

    public function selectCategoria(?int $categoriaId): void
    {
        $this->categoriaId = $categoriaId ? (string) $categoriaId : '';
        $this->resetListing();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoriaId = '';
        $this->marcaId = '';
        $this->limit = 12;
    }

    public function loadMore(): void
    {
        $this->limit += 12;
    }

    public function openProduct(int $productId): void
    {
        if (! $this->databaseReady()) {
            return;
        }

        $exists = Producto::query()
            ->where('activo', true)
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->whereKey($productId)
            ->exists();

        if (! $exists) {
            return;
        }

        $this->selectedProductId = $productId;
        $this->showProductModal = true;
    }

    public function closeProduct(): void
    {
        $this->selectedProductId = null;
        $this->showProductModal = false;
    }

    public function render(): View
    {
        if (! $this->databaseReady()) {
            return view('livewire.publico.catalogo-productos', [
                'categorias' => collect(),
                'marcas' => collect(),
                'productos' => collect(),
                'selectedProduct' => null,
                'selectedSucursal' => null,
                'sucursales' => collect(),
                'totalProductos' => 0,
            ]);
        }

        $sucursales = $this->sucursales();
        $productos = $this->productos();
        $preciosPorProducto = $this->preciosPorProducto($productos->pluck('id'));

        return view('livewire.publico.catalogo-productos', [
            'categorias' => $this->categorias(),
            'marcas' => $this->marcas(),
            'productos' => $productos
                ->map(fn (Producto $producto): array => $this->formatProduct($producto, $preciosPorProducto->get($producto->id, collect())))
                ->values(),
            'selectedProduct' => $this->selectedProduct(),
            'selectedSucursal' => $sucursales->firstWhere('id', (int) $this->sucursalId),
            'sucursales' => $sucursales,
            'totalProductos' => $this->totalProductos(),
        ]);
    }

    protected function resetListing(): void
    {
        $this->limit = 12;
    }

    protected function databaseReady(): bool
    {
        if ($this->databaseReady !== null) {
            return $this->databaseReady;
        }

        try {
            return $this->databaseReady = Schema::hasTable('productos')
                && Schema::hasTable('sucursales')
                && Schema::hasTable('producto_sucursal');
        } catch (Throwable) {
            return $this->databaseReady = false;
        }
    }

    protected function sucursales(): Collection
    {
        return Sucursal::query()
            ->where('activo', true)
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->orderBy('nombre_sucursal')
            ->get(['id', 'empresa_id', 'nombre_sucursal', 'direccion', 'telefono']);
    }

    protected function categorias(): Collection
    {
        return Categoria::query()
            ->where('estado', true)
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    protected function marcas(): Collection
    {
        return Marca::query()
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    protected function productos(): Collection
    {
        return $this->baseProductQuery()
            ->limit($this->limit)
            ->get();
    }

    protected function totalProductos(): int
    {
        return (clone $this->baseProductQuery())
            ->count();
    }

    protected function baseProductQuery(): Builder
    {
        $search = trim($this->search);
        $sucursalId = $this->selectedSucursalId();

        return Producto::query()
            ->where('activo', true)
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->whereHas('productoSucursales', function (Builder $query) use ($sucursalId): void {
                $query
                    ->where('activo', true)
                    ->whereHas('sucursal', fn (Builder $query) => $query->where('activo', true));

                if ($sucursalId) {
                    $query->where('sucursal_id', $sucursalId);
                }
            })
            ->with([
                'categoria:id,nombre',
                'marca:id,nombre',
                'presentaciones' => fn ($query) => $query
                    ->with('unidadMedida:id,nombre,abreviatura')
                    ->orderBy('id'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo_interno', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($this->categoriaId !== '', fn (Builder $query) => $query->where('categoria_id', (int) $this->categoriaId))
            ->when($this->marcaId !== '', fn (Builder $query) => $query->where('marca_id', (int) $this->marcaId))
            ->orderBy('nombre');
    }

    protected function preciosPorProducto(Collection $productIds): Collection
    {
        if ($productIds->isEmpty()) {
            return collect();
        }

        return ProductoSucursal::query()
            ->whereIn('producto_id', $productIds->all())
            ->where('activo', true)
            ->whereHas('sucursal', fn (Builder $query) => $query->where('activo', true))
            ->when($this->selectedSucursalId(), fn (Builder $query, int $sucursalId) => $query->where('sucursal_id', $sucursalId))
            ->with([
                'lotePresentacion.productoPresentacion.unidadMedida:id,nombre,abreviatura',
                'sucursal:id,nombre_sucursal',
            ])
            ->latest('id')
            ->get()
            ->groupBy('producto_id');
    }

    protected function selectedProduct(): ?array
    {
        if (! $this->showProductModal || ! $this->selectedProductId) {
            return null;
        }

        $producto = Producto::query()
            ->where('activo', true)
            ->when($this->empresaId, fn ($q) => $q->where('empresa_id', $this->empresaId))
            ->whereKey($this->selectedProductId)
            ->with([
                'categoria:id,nombre',
                'marca:id,nombre',
                'presentaciones' => fn ($query) => $query
                    ->with('unidadMedida:id,nombre,abreviatura')
                    ->orderBy('id'),
            ])
            ->first();

        if (! $producto) {
            return null;
        }

        $precios = $this->preciosPorProducto(collect([$producto->id]))->get($producto->id, collect());

        return $this->formatProduct($producto, $precios, includePresentations: true);
    }

    protected function selectedSucursalId(): ?int
    {
        return is_numeric($this->sucursalId) ? (int) $this->sucursalId : null;
    }

    protected function formatProduct(Producto $producto, Collection $precios, bool $includePresentations = false): array
    {
        $prices = $precios
            ->pluck('precio')
            ->filter(fn ($price): bool => $price !== null)
            ->map(fn ($price): float => (float) $price)
            ->values();

        $stock = $precios->sum(fn (ProductoSucursal $precio): int => $precio->stock);

        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'codigo' => $producto->codigo_interno,
            'descripcion' => $producto->descripcion,
            'categoria' => $producto->categoria?->nombre,
            'marca' => $producto->marca?->nombre,
            'imagen' => $this->imageForProduct($producto),
            'precio_minimo' => $prices->min(),
            'precio_maximo' => $prices->max(),
            'stock' => $stock,
            'presentaciones_count' => $producto->presentaciones->count(),
            'presentaciones' => $includePresentations ? $this->formatPresentations($producto, $precios) : [],
        ];
    }

    protected function imageForProduct(Producto $producto): ?string
    {
        $presentation = $producto->presentaciones
            ->filter(fn ($presentacion): bool => filled($presentacion->imagen))
            ->sortByDesc(fn ($presentacion): bool => $presentacion->unidadMedida?->abreviatura === 'und')
            ->first();

        return $presentation?->imagen_url;
    }

    protected function formatPresentations(Producto $producto, Collection $precios): Collection
    {
        $preciosPorPresentacion = $precios
            ->filter(fn (ProductoSucursal $precio): bool => $precio->lotePresentacion?->producto_presentacion_id !== null)
            ->groupBy(fn (ProductoSucursal $precio): int => $precio->lotePresentacion->producto_presentacion_id);

        return $producto->presentaciones
            ->map(function ($presentacion) use ($preciosPorPresentacion): array {
                $precios = $preciosPorPresentacion->get($presentacion->id, collect());
                $latest = $precios->sortByDesc('id')->first();
                $offer = $precios
                    ->pluck('lotePresentacion.precio_oferta')
                    ->filter(fn ($price): bool => $price !== null)
                    ->map(fn ($price): float => (float) $price)
                    ->min();

                return [
                    'id' => $presentacion->id,
                    'label' => trim(($presentacion->tipo_presentacion ?: 'Presentacion').' x '.$presentacion->cantidad.' '.($presentacion->unidadMedida?->abreviatura ?? 'und')),
                    'precio' => $latest?->precio !== null ? (float) $latest->precio : null,
                    'precio_mayorista' => $latest?->precio_mayorista !== null ? (float) $latest->precio_mayorista : null,
                    'minimo_mayorista' => $latest?->minimo_mayorista,
                    'precio_oferta' => $offer,
                    'stock' => $precios->sum(fn (ProductoSucursal $precio): int => $precio->stock),
                    'imagen' => $presentacion->imagen_url,
                ];
            })
            ->values();
    }
}
