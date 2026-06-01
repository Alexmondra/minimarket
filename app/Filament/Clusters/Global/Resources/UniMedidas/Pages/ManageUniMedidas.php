<?php

namespace App\Filament\Clusters\Global\Resources\UniMedidas\Pages;

use App\Filament\Clusters\Global\Resources\UniMedidas\UniMedidaResource;
use App\Models\UniMedida;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ManageUniMedidas extends Page
{
    use WithPagination;

    protected static string $resource = UniMedidaResource::class;

    protected string $view = 'filament.clusters.global.resources.uni-medidas.pages.manage-uni-medidas';

    public string $search = '';
    public string $estado = 'all'; // all, active, inactive, trashed
    public string $sortField = 'nombre';
    public string $sortDirection = 'asc';

    public string $viewMode = 'cards'; // cards, list

    // Form fields for Create/Edit Modal
    public ?int $uniMedidaId = null;
    public string $nombre = '';
    public string $abreviatura = '';
    public bool $activo = true;

    // Modal Visibility
    public bool $showModal = false;
    
    // Delete Confirmation Modal Visibility
    public bool $showDeleteConfirmModal = false;
    public ?int $uniMedidaToDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => 'all'],
        'viewMode' => ['except' => 'cards'],
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
        $total = UniMedida::withTrashed()->count();
        $actives = UniMedida::where('activo', true)->count();
        $inactives = UniMedida::where('activo', false)->count();
        $trashed = UniMedida::onlyTrashed()->count();

        return [
            'total' => $total,
            'actives' => $actives,
            'inactives' => $inactives,
            'trashed' => $trashed,
        ];
    }

    public function getUniMedidasProperty()
    {
        $query = UniMedida::query();

        if ($this->estado === 'active') {
            $query->where('activo', true);
        } elseif ($this->estado === 'inactive') {
            $query->where('activo', false);
        } elseif ($this->estado === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->withTrashed();
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($searchTerm) {
                $q->where('nombre', 'like', $searchTerm)
                  ->orWhere('abreviatura', 'like', $searchTerm);
            });
        }

        // Count how many product presentations use each unit of measure
        $query->withCount('presentaciones');

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

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->uniMedidaId = null;
        $this->nombre = '';
        $this->abreviatura = '';
        $this->activo = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $uniMedida = UniMedida::withTrashed()->findOrFail($id);

        $this->uniMedidaId = $uniMedida->id;
        $this->nombre = $uniMedida->nombre;
        $this->abreviatura = $uniMedida->abreviatura;
        $this->activo = (bool) $uniMedida->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'abreviatura' => 'required|string|max:50',
            'activo' => 'required|boolean',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            if ($this->uniMedidaId) {
                $uniMedida = UniMedida::withTrashed()->findOrFail($this->uniMedidaId);
                $uniMedida->update([
                    'nombre' => $this->nombre,
                    'abreviatura' => $this->abreviatura,
                    'activo' => $this->activo,
                ]);

                Notification::make()
                    ->title('Unidad de medida actualizada con éxito')
                    ->success()
                    ->send();
            } else {
                UniMedida::create([
                    'nombre' => $this->nombre,
                    'abreviatura' => $this->abreviatura,
                    'activo' => $this->activo,
                ]);

                Notification::make()
                    ->title('Unidad de medida creada con éxito')
                    ->success()
                    ->send();
            }
        });

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->uniMedidaToDeleteId = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function delete(): void
    {
        if (!$this->uniMedidaToDeleteId) {
            return;
        }

        DB::transaction(function () {
            $uniMedida = UniMedida::findOrFail($this->uniMedidaToDeleteId);
            $uniMedida->delete();

            Notification::make()
                ->title('Unidad de medida enviada a la papelera')
                ->success()
                ->send();
        });

        $this->showDeleteConfirmModal = false;
        $this->uniMedidaToDeleteId = null;
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $uniMedida = UniMedida::onlyTrashed()->findOrFail($id);
            $uniMedida->restore();

            Notification::make()
                ->title('Unidad de medida restaurada con éxito')
                ->success()
                ->send();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $uniMedida = UniMedida::onlyTrashed()->findOrFail($id);
            $uniMedida->forceDelete();

            Notification::make()
                ->title('Unidad de medida eliminada permanentemente')
                ->success()
                ->send();
        });
    }
}
