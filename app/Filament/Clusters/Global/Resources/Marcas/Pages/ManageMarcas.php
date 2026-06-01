<?php

namespace App\Filament\Clusters\Global\Resources\Marcas\Pages;

use App\Filament\Clusters\Global\Resources\Marcas\MarcaResource;
use App\Models\Marca;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ManageMarcas extends Page
{
    use WithPagination;

    protected static string $resource = MarcaResource::class;

    protected string $view = 'filament.clusters.global.resources.marcas.pages.manage-marcas';

    public $search = '';
    public $estado = 'all'; // all, active, trashed
    public $sortField = 'nombre';
    public $sortDirection = 'asc';

    // Form fields for Create/Edit Modal
    public $marcaId = null;
    public $nombre = '';
    public $descripcion = '';

    // Modal Visibility
    public $showModal = false;
    
    // Delete Confirmation Modal Visibility
    public $showDeleteConfirmModal = false;
    public $marcaToDeleteId = null;

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
        
        $total = Marca::where('empresa_id', $empresaId)->withTrashed()->count();
        $actives = Marca::where('empresa_id', $empresaId)->count();
        $trashed = Marca::where('empresa_id', $empresaId)->onlyTrashed()->count();

        return [
            'total' => $total,
            'actives' => $actives,
            'trashed' => $trashed,
        ];
    }

    public function getMarcasProperty()
    {
        $empresaId = auth()->user()->empresa_id;
        $query = Marca::where('empresa_id', $empresaId);

        if ($this->estado === 'active') {
            // Already active by default since we don't query trashed
        } elseif ($this->estado === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->withTrashed();
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                  ->orWhere('descripcion', 'like', $searchTerm);
            });
        }

        // Count associated products for each brand
        $query->withCount(['productos' => function ($q) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        }]);

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
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

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->marcaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $marca = Marca::where('empresa_id', auth()->user()->empresa_id)
            ->withTrashed()
            ->findOrFail($id);

        $this->marcaId = $marca->id;
        $this->nombre = $marca->nombre;
        $this->descripcion = $marca->descripcion;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:65535',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $empresaId = auth()->user()->empresa_id;

            if ($this->marcaId) {
                $marca = Marca::where('empresa_id', $empresaId)
                    ->withTrashed()
                    ->findOrFail($this->marcaId);
                
                $marca->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                ]);

                Notification::make()
                    ->title('Marca actualizada con éxito')
                    ->success()
                    ->send();
            } else {
                Marca::create([
                    'empresa_id' => $empresaId,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                ]);

                Notification::make()
                    ->title('Marca creada con éxito')
                    ->success()
                    ->send();
            }
        });

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->marcaToDeleteId = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function delete(): void
    {
        if (!$this->marcaToDeleteId) {
            return;
        }

        DB::transaction(function () {
            $marca = Marca::where('empresa_id', auth()->user()->empresa_id)
                ->findOrFail($this->marcaToDeleteId);

            $marca->delete();

            Notification::make()
                ->title('Marca enviada a la papelera')
                ->success()
                ->send();
        });

        $this->showDeleteConfirmModal = false;
        $this->marcaToDeleteId = null;
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $marca = Marca::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $marca->restore();

            Notification::make()
                ->title('Marca restaurada con éxito')
                ->success()
                ->send();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $marca = Marca::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $marca->forceDelete();

            Notification::make()
                ->title('Marca eliminada permanentemente')
                ->success()
                ->send();
        });
    }
}
