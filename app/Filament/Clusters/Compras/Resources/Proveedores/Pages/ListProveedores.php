<?php

namespace App\Filament\Clusters\Compras\Resources\Proveedores\Pages;

use App\Filament\Clusters\Compras\Resources\Proveedores\ProveedorResource;
use App\Models\Proveedor;
use App\Support\SucursalContext;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ListProveedores extends Page
{
    use WithPagination;

    protected static string $resource = ProveedorResource::class;

    protected string $view = 'filament.clusters.compras.resources.proveedores.pages.list-proveedores';

    public $search = '';
    public $estado = 'active';
    public $sortField = 'nombre';
    public $sortDirection = 'asc';

    public $showDeleteConfirmModal = false;
    public $proveedorToDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => 'active'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEstado(): void
    {
        $this->resetPage();
    }

    protected function applySucursalScope(Builder $query): Builder
    {
        $sucursalContext = app(SucursalContext::class);
        $activeId = $sucursalContext->activeSucursalId();

        $query->where(function (Builder $q) use ($sucursalContext, $activeId) {
            $sucursalContext->applyNullableToQuery($q);

            if ($activeId) {
                $q->orWhereIn('id', function ($subquery) use ($activeId) {
                    $subquery->select('proveedor_id')
                        ->from('compras')
                        ->where('sucursal_id', $activeId)
                        ->whereNull('deleted_at');
                });
            }
        });

        return $query;
    }

    public function getStatsProperty(): array
    {
        $empresaId = auth()->user()->empresa_id;

        $baseQuery = Proveedor::where('empresa_id', $empresaId);
        $baseQuery = $this->applySucursalScope($baseQuery);

        $total = (clone $baseQuery)->withTrashed()->count();
        $activos = (clone $baseQuery)->where('estado', true)->count();
        $inactivos = (clone $baseQuery)->where('estado', false)->count();

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $inactivos,
        ];
    }

    public function getProveedoresProperty()
    {
        $empresaId = auth()->user()->empresa_id;
        $query = Proveedor::where('empresa_id', $empresaId);

        $query = $this->applySucursalScope($query);

        if ($this->estado === 'active') {
        } elseif ($this->estado === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->withTrashed();
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                  ->orWhere('numero_documento', 'like', $searchTerm)
                  ->orWhere('razon_social', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm)
                  ->orWhere('telefono', 'like', $searchTerm)
                  ->orWhere('contacto_principal', 'like', $searchTerm);
            });
        }

        $query->withCount(['compras' => function ($q) {
            $sucursalContext = app(SucursalContext::class);
            $activeId = $sucursalContext->activeSucursalId();
            if ($activeId) {
                $q->where('sucursal_id', $activeId);
            } else {
                $sucursalContext->applyToQuery($q);
            }
        }]);

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

    public function confirmDelete(int $id): void
    {
        $proveedor = Proveedor::where('empresa_id', auth()->user()->empresa_id)
            ->findOrFail($id);

        if ($proveedor->compras()->exists()) {
            Notification::make()
                ->title('No se puede eliminar')
                ->body('Este proveedor tiene compras registradas. Elimínalas primero.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        $this->proveedorToDeleteId = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function delete(): void
    {
        if (!$this->proveedorToDeleteId) {
            return;
        }

        DB::transaction(function () {
            $proveedor = Proveedor::where('empresa_id', auth()->user()->empresa_id)
                ->findOrFail($this->proveedorToDeleteId);

            $proveedor->delete();

            Notification::make()
                ->title('Proveedor enviado a la papelera')
                ->success()
                ->send();
        });

        $this->showDeleteConfirmModal = false;
        $this->proveedorToDeleteId = null;
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $proveedor = Proveedor::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $proveedor->restore();

            Notification::make()
                ->title('Proveedor restaurado con éxito')
                ->success()
                ->send();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $proveedor = Proveedor::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $proveedor->forceDelete();

            Notification::make()
                ->title('Proveedor eliminado permanentemente')
                ->success()
                ->send();
        });
    }
}
