<?php

namespace App\Filament\Clusters\Almacen\Resources\Productos\Pages;

use App\Filament\Clusters\Almacen\Resources\Productos\ProductoResource;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class ListProductos extends Page
{
    use WithPagination;

    protected static string $resource = ProductoResource::class;

    protected string $view = 'filament.clusters.almacen.resources.productos.pages.list-productos';

    // Filters and sorting
    public $search = '';
    public $categoria_id = 'all';
    public $marca_id = 'all';
    public $stock_filtro = 'all'; // all, in, low, out
    public $estado = 'all'; // all, active, trashed
    public $sortField = 'nombre';
    public $sortDirection = 'asc';
    public $viewMode = 'grid'; // grid, table

    // Selected product for presentations slide panel/drawer
    public $selectedProductForPresentationsId = null;

    // Form fields for Create/Edit Product Modal
    public $productoId = null;
    public $nombre = '';
    public $categoriaId = null;
    public $marcaId = null;
    public $codigo_interno = '';
    public $descripcion = '';
    public $afecto_igv = true;
    public $activo = true;

    // Modal visibilities
    public $showModal = false;
    public $showDeleteConfirmModal = false;
    public $productoToDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoria_id' => ['except' => 'all'],
        'marca_id' => ['except' => 'all'],
        'stock_filtro' => ['except' => 'all'],
        'estado' => ['except' => 'all'],
        'viewMode' => ['except' => 'grid'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoriaId(): void
    {
        $this->resetPage();
    }

    public function updatingMarcaId(): void
    {
        $this->resetPage();
    }

    public function updatingStockFiltro(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    /**
     * Get dashboard KPIs
     */
    public function getStatsProperty(): array
    {
        $empresaId = auth()->user()->empresa_id;

        $total = Producto::where('empresa_id', $empresaId)->withTrashed()->count();
        $actives = Producto::where('empresa_id', $empresaId)->count();

        // Stock Crítico / Bajo (sum of stock <= 5 and > 0)
        $lowStock = Producto::where('empresa_id', $empresaId)
            ->whereExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('lote_presentacion as lp')
                    ->join('producto_presentacion as pp', 'lp.producto_presentacion_id', '=', 'pp.id')
                    ->whereColumn('pp.producto_id', 'productos.id')
                    ->whereNull('pp.deleted_at')
                    ->where('lp.stock', '>', 0);
            })
            ->whereRaw('(
                SELECT COALESCE(SUM(lp2.stock), 0)
                FROM lote_presentacion as lp2
                JOIN producto_presentacion as pp2 ON lp2.producto_presentacion_id = pp2.id
                WHERE pp2.producto_id = productos.id AND pp2.deleted_at IS NULL
            ) <= 5')
            ->count();

        return [
            'total' => $total,
            'actives' => $actives,
            'lowStock' => $lowStock,
        ];
    }

    /**
     * Fetch products list with filters, sorting, and eager loaded relationships
     */
    public function getProductosProperty()
    {
        $empresaId = auth()->user()->empresa_id;
        $query = Producto::where('productos.empresa_id', $empresaId);

        // Filter by state
        if ($this->estado === 'active') {
            // Default active
        } elseif ($this->estado === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->withTrashed();
        }

        // Filter by category
        if ($this->categoria_id !== 'all') {
            $query->where('categoria_id', (int) $this->categoria_id);
        }

        // Filter by brand
        if ($this->marca_id !== 'all') {
            $query->where('marca_id', (int) $this->marca_id);
        }

        // Filter by stock level
        if ($this->stock_filtro === 'out') {
            $query->where(function ($q) {
                $q->whereNotExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('lote_presentacion as lp')
                        ->join('producto_presentacion as pp', 'lp.producto_presentacion_id', '=', 'pp.id')
                        ->whereColumn('pp.producto_id', 'productos.id')
                        ->whereNull('pp.deleted_at')
                        ->where('lp.stock', '>', 0);
                });
            });
        } elseif ($this->stock_filtro === 'in') {
            $query->whereExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('lote_presentacion as lp')
                    ->join('producto_presentacion as pp', 'lp.producto_presentacion_id', '=', 'pp.id')
                    ->whereColumn('pp.producto_id', 'productos.id')
                    ->whereNull('pp.deleted_at')
                    ->where('lp.stock', '>', 0);
            });
        } elseif ($this->stock_filtro === 'low') {
            $query->where(function ($q) {
                $q->whereExists(function ($sub) {
                    $sub->selectRaw(1)
                        ->from('lote_presentacion as lp')
                        ->join('producto_presentacion as pp', 'lp.producto_presentacion_id', '=', 'pp.id')
                        ->whereColumn('pp.producto_id', 'productos.id')
                        ->whereNull('pp.deleted_at')
                        ->where('lp.stock', '>', 0);
                })->where(function ($inner) {
                    $inner->whereRaw('(
                        SELECT COALESCE(SUM(lp2.stock), 0)
                        FROM lote_presentacion as lp2
                        JOIN producto_presentacion as pp2 ON lp2.producto_presentacion_id = pp2.id
                        WHERE pp2.producto_id = productos.id AND pp2.deleted_at IS NULL
                    ) <= 5');
                });
            });
        }

        // Search text
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('productos.nombre', 'like', $searchTerm)
                  ->orWhere('productos.codigo_interno', 'like', $searchTerm)
                  ->orWhere('productos.descripcion', 'like', $searchTerm);
            });
        }

        // Eager load relationships for rendering image, stock, and price
        $query->with([
            'categoria',
            'marca',
            'presentaciones' => function ($q) {
                $q->whereNull('deleted_at')
                  ->with(['unidadMedida', 'lotePresentaciones', 'productoSucursales']);
            }
        ]);

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(12);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleViewMode(string $mode): void
    {
        if (in_array($mode, ['grid', 'table'])) {
            $this->viewMode = $mode;
        }
    }

    /**
     * Dropdown list of categories
     */
    public function getCategoriasProperty()
    {
        return Categoria::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Dropdown list of brands
     */
    public function getMarcasProperty()
    {
        return Marca::where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nombre')
            ->get();
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->productoId = null;
        $this->nombre = '';
        $this->categoriaId = $this->categorias->first()?->id ?? null;
        $this->marcaId = $this->marcas->first()?->id ?? null;
        $this->codigo_interno = '';
        $this->descripcion = '';
        $this->afecto_igv = true;
        $this->activo = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $producto = Producto::where('empresa_id', auth()->user()->empresa_id)
            ->withTrashed()
            ->findOrFail($id);

        $this->productoId = $producto->id;
        $this->nombre = $producto->nombre;
        $this->categoriaId = $producto->categoria_id;
        $this->marcaId = $producto->marca_id;
        $this->codigo_interno = $producto->codigo_interno;
        $this->descripcion = $producto->descripcion;
        $this->afecto_igv = (bool) $producto->afecto_igv;
        $this->activo = (bool) $producto->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'categoriaId' => 'required|exists:categorias,id',
            'marcaId' => 'required|exists:marcas,id',
            'codigo_interno' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:65535',
            'afecto_igv' => 'required|boolean',
            'activo' => 'required|boolean',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $empresaId = auth()->user()->empresa_id;

            // Generate slug if new or name changed
            $slug = Str::slug($this->nombre);
            $baseSlug = $slug;
            $counter = 1;
            while (Producto::where('slug', $slug)->when($this->productoId, fn($q) => $q->where('id', '!=', $this->productoId))->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            // Generate unique internal code if empty
            $codigo = $this->codigo_interno;
            if (empty($codigo)) {
                $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $this->nombre);
                $length = strlen($cleanName);
                $halfLength = max(1, (int) ceil($length / 2));
                $halfName = strtoupper(substr($cleanName, 0, $halfLength));

                if (strlen($halfName) > 8) {
                    $halfName = substr($halfName, 0, 8);
                }

                $randomDigits = str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT);
                $codigo = $halfName . $randomDigits;

                $baseCodigo = $codigo;
                $counter = 1;
                while (Producto::where('codigo_interno', $codigo)->exists()) {
                    $codigo = $baseCodigo . '-' . $counter;
                    $counter++;
                }
            }

            if ($this->productoId) {
                $producto = Producto::where('empresa_id', $empresaId)
                    ->withTrashed()
                    ->findOrFail($this->productoId);
                
                $producto->update([
                    'nombre' => $this->nombre,
                    'slug' => $slug,
                    'categoria_id' => $this->categoriaId,
                    'marca_id' => $this->marcaId,
                    'codigo_interno' => $codigo,
                    'descripcion' => $this->descripcion,
                    'afecto_igv' => $this->afecto_igv,
                    'activo' => $this->activo,
                ]);

                Notification::make()
                    ->title('Producto actualizado con éxito')
                    ->success()
                    ->send();
            } else {
                Producto::create([
                    'empresa_id' => $empresaId,
                    'nombre' => $this->nombre,
                    'slug' => $slug,
                    'categoria_id' => $this->categoriaId,
                    'marca_id' => $this->marcaId,
                    'codigo_interno' => $codigo,
                    'descripcion' => $this->descripcion,
                    'afecto_igv' => $this->afecto_igv,
                    'activo' => $this->activo,
                ]);

                Notification::make()
                    ->title('Producto creado con éxito')
                    ->success()
                    ->send();
            }
        });

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->productoToDeleteId = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function delete(): void
    {
        if (!$this->productoToDeleteId) {
            return;
        }

        DB::transaction(function () {
            $producto = Producto::where('empresa_id', auth()->user()->empresa_id)
                ->findOrFail($this->productoToDeleteId);

            $producto->delete();

            Notification::make()
                ->title('Producto enviado a la papelera')
                ->success()
                ->send();
        });

        $this->showDeleteConfirmModal = false;
        $this->productoToDeleteId = null;
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $producto = Producto::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $producto->restore();

            Notification::make()
                ->title('Producto restaurado con éxito')
                ->success()
                ->send();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $producto = Producto::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $producto->forceDelete();

            Notification::make()
                ->title('Producto eliminado permanentemente')
                ->success()
                ->send();
        });
    }

    /**
     * Show presentations slide drawer
     */
    public function verPresentaciones(int $id): void
    {
        $this->selectedProductForPresentationsId = $id;
    }
}
