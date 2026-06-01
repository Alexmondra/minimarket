<?php

namespace App\Filament\Clusters\Global\Resources\Categorias\Pages;

use App\Filament\Clusters\Global\Resources\Categorias\CategoriaResource;
use App\Models\Categoria;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ManageCategorias extends Page
{
    use WithPagination;

    protected static string $resource = CategoriaResource::class;

    protected string $view = 'filament.clusters.global.resources.categorias.pages.manage-categorias';

    public $search = '';
    public $estado = 'all'; // all, active, trashed
    public $sortField = 'nombre';
    public $sortDirection = 'asc';
    public $viewMode = 'grid'; // grid, table

    // Form fields for Create/Edit Modal
    public $categoriaId = null;
    public $nombre = '';
    public $descripcion = '';
    public $estado_campo = true; // boolean state toggle

    // Modal Visibility
    public $showModal = false;
    
    // Delete Confirmation Modal Visibility
    public $showDeleteConfirmModal = false;
    public $categoriaToDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => 'all'],
        'viewMode' => ['except' => 'grid'],
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
        
        $total = Categoria::where('empresa_id', $empresaId)->withTrashed()->count();
        $actives = Categoria::where('empresa_id', $empresaId)->count();
        $trashed = Categoria::where('empresa_id', $empresaId)->onlyTrashed()->count();

        return [
            'total' => $total,
            'actives' => $actives,
            'trashed' => $trashed,
        ];
    }

    public function getCategoriasProperty()
    {
        $empresaId = auth()->user()->empresa_id;
        $query = Categoria::where('empresa_id', $empresaId);

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

        // Count associated products for each category
        $query->withCount(['productos' => function ($q) {
            $q->where('empresa_id', auth()->user()->empresa_id);
        }]);

        return $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate(12); // Grid looks better with multiples of 3 or 4, e.g. 12
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

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->categoriaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado_campo = true;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $categoria = Categoria::where('empresa_id', auth()->user()->empresa_id)
            ->withTrashed()
            ->findOrFail($id);

        $this->categoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->descripcion = $categoria->descripcion;
        $this->estado_campo = (bool) $categoria->estado;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:65535',
            'estado_campo' => 'required|boolean',
        ];

        $this->validate($rules);

        DB::transaction(function () {
            $empresaId = auth()->user()->empresa_id;

            if ($this->categoriaId) {
                $categoria = Categoria::where('empresa_id', $empresaId)
                    ->withTrashed()
                    ->findOrFail($this->categoriaId);
                
                $categoria->update([
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado_campo,
                ]);

                Notification::make()
                    ->title('Categoría actualizada con éxito')
                    ->success()
                    ->send();
            } else {
                Categoria::create([
                    'empresa_id' => $empresaId,
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'estado' => $this->estado_campo,
                ]);

                Notification::make()
                    ->title('Categoría creada con éxito')
                    ->success()
                    ->send();
            }
        });

        $this->showModal = false;
    }

    public function confirmDelete(int $id): void
    {
        $this->categoriaToDeleteId = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function delete(): void
    {
        if (!$this->categoriaToDeleteId) {
            return;
        }

        DB::transaction(function () {
            $categoria = Categoria::where('empresa_id', auth()->user()->empresa_id)
                ->findOrFail($this->categoriaToDeleteId);

            $categoria->delete();

            Notification::make()
                ->title('Categoría enviada a la papelera')
                ->success()
                ->send();
        });

        $this->showDeleteConfirmModal = false;
        $this->categoriaToDeleteId = null;
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $categoria = Categoria::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $categoria->restore();

            Notification::make()
                ->title('Categoría restaurada con éxito')
                ->success()
                ->send();
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $categoria = Categoria::where('empresa_id', auth()->user()->empresa_id)
                ->onlyTrashed()
                ->findOrFail($id);

            $categoria->forceDelete();

            Notification::make()
                ->title('Categoría eliminada permanentemente')
                ->success()
                ->send();
        });
    }

    /**
     * Map category names to a representative emoji, background color, text color,
     * and styling variables for high fidelity UX.
     */
    public function getCategoryVisuals(string $name): array
    {
        $nameLower = mb_strtolower(trim($name), 'UTF-8');

        if (str_contains($nameLower, 'bebida') || str_contains($nameLower, 'gaseosa') || str_contains($nameLower, 'jugo') || str_contains($nameLower, 'tomar') || str_contains($nameLower, 'agua')) {
            return [
                'emoji' => '🥤',
                'bg' => 'bg-blue-50/70 dark:bg-blue-950/20',
                'border' => 'border-blue-200/50 dark:border-blue-800/40',
                'text' => 'text-blue-600 dark:text-blue-400',
                'icon_bg' => 'bg-blue-100 dark:bg-blue-900/40',
                'gradient' => 'from-blue-500 to-indigo-600',
            ];
        }
        if (str_contains($nameLower, 'lacte') || str_contains($nameLower, 'leche') || str_contains($nameLower, 'queso') || str_contains($nameLower, 'yogur') || str_contains($nameLower, 'mantequilla')) {
            return [
                'emoji' => '🥛',
                'bg' => 'bg-sky-50/70 dark:bg-sky-950/20',
                'border' => 'border-sky-200/50 dark:border-sky-800/40',
                'text' => 'text-sky-600 dark:text-sky-400',
                'icon_bg' => 'bg-sky-100 dark:bg-sky-900/40',
                'gradient' => 'from-sky-500 to-blue-600',
            ];
        }
        if (str_contains($nameLower, 'snack') || str_contains($nameLower, 'galleta') || str_contains($nameLower, 'dulce') || str_contains($nameLower, 'caramelo') || str_contains($nameLower, 'chocolate') || str_contains($nameLower, 'piqueo')) {
            return [
                'emoji' => '🍪',
                'bg' => 'bg-pink-50/70 dark:bg-pink-950/20',
                'border' => 'border-pink-200/50 dark:border-pink-800/40',
                'text' => 'text-pink-600 dark:text-pink-400',
                'icon_bg' => 'bg-pink-100 dark:bg-pink-900/40',
                'gradient' => 'from-pink-500 to-rose-600',
            ];
        }
        if (str_contains($nameLower, 'limpieza') || str_contains($nameLower, 'detergente') || str_contains($nameLower, 'jabon') || str_contains($nameLower, 'desinfectante') || str_contains($nameLower, 'lavavajilla')) {
            return [
                'emoji' => '🧹',
                'bg' => 'bg-teal-50/70 dark:bg-teal-950/20',
                'border' => 'border-teal-200/50 dark:border-teal-800/40',
                'text' => 'text-teal-600 dark:text-teal-400',
                'icon_bg' => 'bg-teal-100 dark:bg-teal-900/40',
                'gradient' => 'from-teal-500 to-cyan-600',
            ];
        }
        if (str_contains($nameLower, 'fruta') || str_contains($nameLower, 'verdura') || str_contains($nameLower, 'vegetal') || str_contains($nameLower, 'organico')) {
            return [
                'emoji' => '🍎',
                'bg' => 'bg-emerald-50/70 dark:bg-emerald-950/20',
                'border' => 'border-emerald-200/50 dark:border-emerald-800/40',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'icon_bg' => 'bg-emerald-100 dark:bg-emerald-900/40',
                'gradient' => 'from-emerald-500 to-teal-600',
            ];
        }
        if (str_contains($nameLower, 'abarrote') || str_contains($nameLower, 'cereal') || str_contains($nameLower, 'arroz') || str_contains($nameLower, 'fideo') || str_contains($nameLower, 'conserva') || str_contains($nameLower, 'aceite')) {
            return [
                'emoji' => '🌾',
                'bg' => 'bg-amber-50/70 dark:bg-amber-950/20',
                'border' => 'border-amber-200/50 dark:border-amber-800/40',
                'text' => 'text-amber-600 dark:text-amber-400',
                'icon_bg' => 'bg-amber-100 dark:bg-amber-900/40',
                'gradient' => 'from-amber-500 to-orange-600',
            ];
        }
        if (str_contains($nameLower, 'licor') || str_contains($nameLower, 'cerveza') || str_contains($nameLower, 'vino') || str_contains($nameLower, 'alcohol') || str_contains($nameLower, 'trago')) {
            return [
                'emoji' => '🍺',
                'bg' => 'bg-purple-50/70 dark:bg-purple-950/20',
                'border' => 'border-purple-200/60 dark:border-purple-800/40',
                'text' => 'text-purple-600 dark:text-purple-400',
                'icon_bg' => 'bg-purple-100 dark:bg-purple-900/40',
                'gradient' => 'from-purple-500 to-violet-600',
            ];
        }
        if (str_contains($nameLower, 'cuidado') || str_contains($nameLower, 'shampoo') || str_contains($nameLower, 'crema') || str_contains($nameLower, 'dental') || str_contains($nameLower, 'personal') || str_contains($nameLower, 'colgate')) {
            return [
                'emoji' => '🧴',
                'bg' => 'bg-rose-50/70 dark:bg-rose-950/20',
                'border' => 'border-rose-200/50 dark:border-rose-800/40',
                'text' => 'text-rose-600 dark:text-rose-400',
                'icon_bg' => 'bg-rose-100 dark:bg-rose-900/40',
                'gradient' => 'from-rose-500 to-pink-600',
            ];
        }
        if (str_contains($nameLower, 'carnes') || str_contains($nameLower, 'pollo') || str_contains($nameLower, 'res') || str_contains($nameLower, 'pescado') || str_contains($nameLower, 'embutido') || str_contains($nameLower, 'chancho')) {
            return [
                'emoji' => '🥩',
                'bg' => 'bg-red-50/70 dark:bg-red-950/20',
                'border' => 'border-red-200/50 dark:border-red-800/40',
                'text' => 'text-red-600 dark:text-red-400',
                'icon_bg' => 'bg-red-100 dark:bg-red-900/40',
                'gradient' => 'from-red-500 to-rose-600',
            ];
        }
        if (str_contains($nameLower, 'pan') || str_contains($nameLower, 'pasteleria') || str_contains($nameLower, 'torta') || str_contains($nameLower, 'queque') || str_contains($nameLower, 'panaderia')) {
            return [
                'emoji' => '🍞',
                'bg' => 'bg-yellow-50/70 dark:bg-yellow-950/20',
                'border' => 'border-yellow-200/50 dark:border-yellow-800/40',
                'text' => 'text-yellow-600 dark:text-yellow-400',
                'icon_bg' => 'bg-yellow-100 dark:bg-yellow-900/40',
                'gradient' => 'from-yellow-500 to-amber-600',
            ];
        }

        // Generic fallback values if no matches found
        $length = mb_strlen($name);
        $fallbacks = [
            [
                'emoji' => '📦',
                'bg' => 'bg-slate-50/70 dark:bg-slate-900/30',
                'border' => 'border-slate-200/50 dark:border-slate-800/40',
                'text' => 'text-slate-600 dark:text-slate-400',
                'icon_bg' => 'bg-slate-100 dark:bg-slate-855/60',
                'gradient' => 'from-slate-500 to-slate-700',
            ],
            [
                'emoji' => '🏷️',
                'bg' => 'bg-indigo-50/70 dark:bg-indigo-950/20',
                'border' => 'border-indigo-200/50 dark:border-indigo-800/40',
                'text' => 'text-indigo-600 dark:text-indigo-400',
                'icon_bg' => 'bg-indigo-100 dark:bg-indigo-900/40',
                'gradient' => 'from-indigo-500 to-violet-600',
            ],
            [
                'emoji' => '✨',
                'bg' => 'bg-violet-50/70 dark:bg-violet-950/20',
                'border' => 'border-violet-200/50 dark:border-violet-800/40',
                'text' => 'text-violet-600 dark:text-violet-400',
                'icon_bg' => 'bg-violet-100 dark:bg-violet-900/40',
                'gradient' => 'from-violet-500 to-purple-600',
            ],
        ];

        return $fallbacks[$length % count($fallbacks)];
    }
}
