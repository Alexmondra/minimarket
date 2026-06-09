<?php

namespace App\Filament\Clusters\Global\Resources\Productos\Pages;

use App\Filament\Clusters\Global\Resources\Productos\ProductoResource;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UniMedida;
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

    protected string $view = 'filament.clusters.global.resources.productos.pages.list-productos';

    // Filters and sorting
    public $search = '';
    public $categoria_id = 'all';
    public $marca_id = 'all';
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


    // Modal visibilities
    public $showModal = false;
    public $showDeleteConfirmModal = false;
    public $productoToDeleteId = null;

    // Brand/Category creation modals from Product Form
    public $showAddCategoryModal = false;
    public $newCategoryNombre = '';
    public $showAddBrandModal = false;
    public $newBrandNombre = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoria_id' => ['except' => 'all'],
        'marca_id' => ['except' => 'all'],
        'estado' => ['except' => 'all'],
        'viewMode' => ['except' => 'grid'],
    ];

    protected $listeners = [
        'refreshPresentations' => '$refresh',
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

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    /**
     * Get global catalog dashboard KPIs
     */
    public function getStatsProperty(): array
    {
        $empresaId = auth()->user()->empresa_id;

        $totalProductos = Producto::where('empresa_id', $empresaId)->withTrashed()->count();
        
        $totalPresentaciones = ProductoPresentacion::whereHas('producto', function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId);
        })->count();

        $totalUnidades = UniMedida::where('activo', true)->count();

        return [
            'totalProductos' => $totalProductos,
            'totalPresentaciones' => $totalPresentaciones,
            'totalUnidades' => $totalUnidades,
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

        // Search text
        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('productos.nombre', 'like', $searchTerm)
                  ->orWhere('productos.codigo_interno', 'like', $searchTerm)
                  ->orWhere('productos.descripcion', 'like', $searchTerm);
            });
        }

        // Eager load only catalog relationships (no stock/branches)
        $query->with([
            'categoria',
            'marca',
            'presentaciones' => function ($q) {
                $q->whereNull('deleted_at')
                  ->with(['unidadMedida']);
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
        ];

        $this->validate($rules, [
            'nombre.required' => 'El nombre del producto es obligatorio.',
        ]);

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
                    'afecto_igv' => true,
                    'activo' => true,
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
                    'afecto_igv' => true,
                    'activo' => true,
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
        $producto = Producto::where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        // Verificar si el producto está asignado a alguna sucursal
        if ($producto->productoSucursales()->exists()) {
            Notification::make()
                ->title('No se puede eliminar')
                ->body('Este producto tiene presentaciones asignadas a una o más sucursales. Desasígnalo primero antes de eliminarlo.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

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

    public function openAddCategoryModal(): void
    {
        $this->resetErrorBag(['newCategoryNombre']);
        $this->newCategoryNombre = '';
        $this->showAddCategoryModal = true;
    }

    public function saveNewCategory(): void
    {
        $this->validate([
            'newCategoryNombre' => 'required|string|max:255',
        ], [
            'newCategoryNombre.required' => 'El nombre de la categoría es requerido.',
        ]);

        $categoria = Categoria::create([
            'empresa_id' => auth()->user()->empresa_id,
            'nombre' => $this->newCategoryNombre,
            'estado' => true,
        ]);

        $this->categoriaId = $categoria->id;
        $this->showAddCategoryModal = false;
        $this->newCategoryNombre = '';

        Notification::make()
            ->title('Categoría creada con éxito')
            ->success()
            ->send();
    }

    public function openAddBrandModal(): void
    {
        $this->resetErrorBag(['newBrandNombre']);
        $this->newBrandNombre = '';
        $this->showAddBrandModal = true;
    }

    public function saveNewBrand(): void
    {
        $this->validate([
            'newBrandNombre' => 'required|string|max:255',
        ], [
            'newBrandNombre.required' => 'El nombre de la marca es requerido.',
        ]);

        $marca = Marca::create([
            'empresa_id' => auth()->user()->empresa_id,
            'nombre' => $this->newBrandNombre,
        ]);

        $this->marcaId = $marca->id;
        $this->showAddBrandModal = false;
        $this->newBrandNombre = '';

        Notification::make()
            ->title('Marca creada con éxito')
            ->success()
            ->send();
    }

    /**
     * Show presentations slide drawer
     */
    public function verPresentaciones(int $id): void
    {
        $this->selectedProductForPresentationsId = $id;
    }
}
