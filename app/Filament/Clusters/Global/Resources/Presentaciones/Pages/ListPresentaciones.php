<?php

namespace App\Filament\Clusters\Global\Resources\Presentaciones\Pages;

use App\Filament\Clusters\Global\Resources\Presentaciones\PresentacionResource;
use App\Filament\Clusters\Global\Resources\Productos\ProductoResource;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentacionBarra;
use App\Models\UniMedida;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ListPresentaciones extends Page
{
    use WithPagination;
    use WithFileUploads;

    protected static string $resource = PresentacionResource::class;

    protected string $view = 'filament.clusters.global.resources.presentaciones.pages.list-presentaciones';

    public string $search = '';
    public string $estado = 'all'; // all, con_productos
    public string $sortField = 'tipo_presentacion';
    public string $sortDirection = 'asc';

    // Global Create Modal properties
    public bool $showModal = false;
    public ?int $producto_id = null;
    public string $tipo_presentacion = '';
    public int $cantidad = 1;
    public ?int $unidad_medida_id = null;
    public bool $es_pesable = false;
    public mixed $imagen = null;
    public array $barras = [];
    public string $nuevo_codigo_barra = '';

    // Product search autocomplete
    public string $searchProductTerm = '';
    public array $productSearchResults = [];

    // Rename Modal properties
    public bool $showRenameModal = false;
    public string $old_tipo_presentacion = '';
    public string $new_tipo_presentacion = '';

    // Delete Modal properties
    public bool $showDeleteConfirmModal = false;
    public string $tipo_presentacion_to_delete = '';
    public int $affected_products_count = 0;

    // View Products Modal properties
    public bool $showProductsModal = false;
    public string $selectedPresentacionTipo = '';
    public $productsList = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        $empresaId = auth()->user()->empresa_id;

        $totalTypes = ProductoPresentacion::whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->distinct('tipo_presentacion')
            ->count('tipo_presentacion');

        $totalPresentations = ProductoPresentacion::whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->count();

        return [
            'total_types' => $totalTypes,
            'total_presentations' => $totalPresentations,
        ];
    }

    public function getPresentacionesProperty()
    {
        $empresaId = auth()->user()->empresa_id;

        $query = ProductoPresentacion::query()
            ->selectRaw('MIN(producto_presentacion.id) as id, tipo_presentacion, COUNT(DISTINCT producto_id) as total_productos')
            ->selectRaw('MIN(unidad_medida_id) as unidad_medida_id')
            ->selectRaw('MIN(cantidad) as cantidad')
            ->selectRaw('MIN(es_pesable) as es_pesable')
            ->selectRaw('(SELECT pp2.imagen FROM producto_presentacion pp2 WHERE pp2.tipo_presentacion = producto_presentacion.tipo_presentacion AND pp2.imagen IS NOT NULL AND pp2.imagen != \'\' LIMIT 1) as imagen_ejemplo')
            ->whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->groupBy('tipo_presentacion');

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where('tipo_presentacion', 'like', $searchTerm);
        }

        if ($this->estado === 'con_productos') {
            $query->havingRaw('total_productos > 0');
        }

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->with('unidadMedida')
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

    // Product search autocomplete trigger
    public function updatedSearchProductTerm($value): void
    {
        $empresaId = auth()->user()->empresa_id;
        if (strlen($value) < 2) {
            $this->productSearchResults = [];
            return;
        }

        $this->productSearchResults = Producto::where('empresa_id', $empresaId)
            ->where(function ($q) use ($value) {
                $q->where('nombre', 'like', '%'.$value.'%')
                  ->orWhere('codigo_interno', 'like', '%'.$value.'%');
            })
            ->limit(10)
            ->get(['id', 'nombre', 'codigo_interno'])
            ->toArray();
    }

    public function selectProduct(int $id, string $nombre): void
    {
        $this->producto_id = $id;
        $this->searchProductTerm = $nombre;
        $this->productSearchResults = [];
    }

    public function getUnidadesMedidaProperty()
    {
        return UniMedida::where('activo', true)->orderBy('nombre')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        
        // Predeterminar la unidad de medida más común (ej. Unidad) si existe
        $unidadDefault = UniMedida::where('abreviatura', 'und')->first();
        if ($unidadDefault) {
            $this->unidad_medida_id = $unidadDefault->id;
        }

        $this->showModal = true;
    }

    public function resetForm(): void
    {
        $this->reset([
            'producto_id',
            'searchProductTerm',
            'productSearchResults',
            'tipo_presentacion',
            'cantidad',
            'unidad_medida_id',
            'es_pesable',
            'imagen',
            'barras',
            'nuevo_codigo_barra',
        ]);
        $this->cantidad = 1;
        $this->es_pesable = false;
        $this->resetErrorBag();
    }

    public function agregarCodigoBarra(): void
    {
        $this->nuevo_codigo_barra = trim($this->nuevo_codigo_barra);
        if (blank($this->nuevo_codigo_barra)) {
            return;
        }

        if (in_array($this->nuevo_codigo_barra, $this->barras)) {
            $this->addError('nuevo_codigo_barra', 'Este código ya está agregado.');
            return;
        }

        $exists = ProductoPresentacionBarra::where('codigo_barra', $this->nuevo_codigo_barra)->exists();
        if ($exists) {
            $this->addError('nuevo_codigo_barra', 'Este código de barras ya está asignado en el sistema.');
            return;
        }

        $this->barras[] = $this->nuevo_codigo_barra;
        $this->nuevo_codigo_barra = '';
        $this->resetErrorBag('nuevo_codigo_barra');
    }

    public function removerCodigoBarra(int $index): void
    {
        if (isset($this->barras[$index])) {
            unset($this->barras[$index]);
            $this->barras = array_values($this->barras);
        }
    }

    public function save(): void
    {
        if (filled($this->nuevo_codigo_barra)) {
            $this->agregarCodigoBarra();
            if ($this->getErrorBag()->has('nuevo_codigo_barra')) {
                return;
            }
        }

        $this->validate([
            'producto_id' => 'required|exists:productos,id',
            'tipo_presentacion' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'es_pesable' => 'boolean',
            'imagen' => $this->imagen ? 'image|max:2048' : 'nullable',
        ], [
            'producto_id.required' => 'Debe buscar y seleccionar un producto.',
            'tipo_presentacion.required' => 'El tipo de presentación es requerido.',
            'cantidad.required' => 'La cantidad es requerida.',
            'cantidad.integer' => 'La cantidad debe ser entera.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'unidad_medida_id.required' => 'La unidad de medida es requerida.',
        ]);

        DB::transaction(function () {
            $presentation = ProductoPresentacion::create([
                'producto_id' => $this->producto_id,
                'tipo_presentacion' => $this->tipo_presentacion,
                'cantidad' => $this->cantidad,
                'unidad_medida_id' => $this->unidad_medida_id,
                'es_pesable' => $this->es_pesable,
            ]);

            if ($this->imagen) {
                $path = $this->imagen->store('productos/presentaciones', 'public');
                $presentation->update(['imagen' => $path]);
            }

            foreach ($this->barras as $code) {
                ProductoPresentacionBarra::create([
                    'producto_presentacion_id' => $presentation->id,
                    'codigo_barra' => $code,
                ]);
            }
        });

        $this->showModal = false;
        $this->resetForm();
        Notification::make()
            ->title('Presentación creada y asociada con éxito')
            ->success()
            ->send();
    }

    public function openRenameModal(string $tipo): void
    {
        $this->resetErrorBag();
        $this->old_tipo_presentacion = $tipo;
        $this->new_tipo_presentacion = $tipo;
        $this->showRenameModal = true;
    }

    public function rename(): void
    {
        $this->validate([
            'new_tipo_presentacion' => 'required|string|max:255',
        ], [
            'new_tipo_presentacion.required' => 'El nuevo nombre es requerido.',
        ]);

        DB::transaction(function () {
            $empresaId = auth()->user()->empresa_id;
            ProductoPresentacion::where('tipo_presentacion', $this->old_tipo_presentacion)
                ->whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
                ->update(['tipo_presentacion' => $this->new_tipo_presentacion]);
        });

        $this->showRenameModal = false;
        Notification::make()
            ->title('Presentación renombrada con éxito')
            ->success()
            ->send();
    }

    public function confirmDelete(string $tipo): void
    {
        $empresaId = auth()->user()->empresa_id;
        $this->tipo_presentacion_to_delete = $tipo;

        $this->affected_products_count = ProductoPresentacion::where('tipo_presentacion', $tipo)
            ->whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
            ->count();

        $this->showDeleteConfirmModal = true;
    }

    public function delete(): void
    {
        if (!$this->tipo_presentacion_to_delete) {
            return;
        }

        DB::transaction(function () {
            $empresaId = auth()->user()->empresa_id;
            $presentations = ProductoPresentacion::where('tipo_presentacion', $this->tipo_presentacion_to_delete)
                ->whereHas('producto', fn ($q) => $q->where('empresa_id', $empresaId))
                ->get();

            foreach ($presentations as $p) {
                if ($p->imagen) {
                    Storage::disk('public')->delete($p->imagen);
                }
                $p->barras()->delete();
                $p->delete();
            }
        });

        $this->showDeleteConfirmModal = false;
        $this->tipo_presentacion_to_delete = '';
        Notification::make()
            ->title('Presentaciones eliminadas correctamente')
            ->success()
            ->send();
    }

    public function openProductsModal(string $tipo): void
    {
        $this->selectedPresentacionTipo = $tipo;
        $empresaId = auth()->user()->empresa_id;

        $this->productsList = Producto::query()
            ->where('empresa_id', $empresaId)
            ->whereHas('presentaciones', fn ($q) => $q->where('tipo_presentacion', $tipo))
            ->with(['categoria', 'marca'])
            ->get();

        $this->showProductsModal = true;
    }

    public function getProductEditUrl(int $id): string
    {
        return ProductoResource::getUrl('edit', ['record' => $id]);
    }
}
