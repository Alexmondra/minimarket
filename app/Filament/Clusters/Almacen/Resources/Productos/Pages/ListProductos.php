<?php

namespace App\Filament\Clusters\Almacen\Resources\Productos\Pages;

use App\Filament\Clusters\Almacen\Resources\Productos\ProductoResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListProductos extends ListRecords
{
    protected static string $resource = ProductoResource::class;

    #[Url(as: 'view')]
    public string $viewMode = 'table';

    public ?string $modalImageUrl = null;

    /** @var array{imagen: string, tipo: string}|null */
    public ?array $modalPresentaciones = [];

    public int $modalCurrentIndex = 0;

    protected string $view = 'filament.clusters.almacen.resources.productos.pages.list-productos';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleView')
                ->label($this->viewMode === 'cards' ? 'Vista Tabla' : 'Vista Cards')
                ->icon($this->viewMode === 'cards' ? 'heroicon-o-table-cells' : 'heroicon-o-squares-2x2')
                ->color('gray')
                ->action('toggleViewMode'),

            CreateAction::make()->label('Nuevo Producto'),
        ];
    }

    public function toggleViewMode(): void
    {
        $this->viewMode = $this->viewMode === 'cards' ? 'table' : 'cards';
        $this->resetTable();
    }

    public function getTable(): Table
    {
        $table = parent::getTable();

        if ($this->viewMode === 'cards') {
            $table->content(fn ($records): View => view(
                'filament.clusters.almacen.resources.productos.pages.cards-productos',
                ['records' => $records]
            ));
        } else {
            $table->content(null);
            $table->contentGrid(null);
        }

        return $table;
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with([
            'presentaciones',
            'presentaciones.unidadMedida',
        ]);
    }

    public function openProductImageModal(string $imagenUrl, ?string $presentaciones = null): void
    {
        $this->modalImageUrl = $imagenUrl;
        $this->modalPresentaciones = $presentaciones ? json_decode($presentaciones, true) : [];
        $this->modalCurrentIndex = 0;
    }

    public function cerrarModalImagen(): void
    {
        $this->modalImageUrl = null;
        $this->modalPresentaciones = [];
        $this->modalCurrentIndex = 0;
    }

    public function navegarPresentacion(int $direction): void
    {
        if (empty($this->modalPresentaciones)) {
            return;
        }
        $total = count($this->modalPresentaciones);
        $this->modalCurrentIndex = ($this->modalCurrentIndex + $direction + $total) % $total;
        $this->modalImageUrl = $this->modalPresentaciones[$this->modalCurrentIndex]['imagen'] ?? null;
    }

    public function renderProductImageModal()
    {
        if (! $this->modalImageUrl) {
            return '';
        }

        $total = count($this->modalPresentaciones);
        $current = $this->modalCurrentIndex;
        $currentTipo = $this->modalPresentaciones[$current]['tipo'] ?? '';

        return view('filament.tables.modals.productos-presentacion', [
            'imagenUrl' => $this->modalImageUrl,
            'totalPresentaciones' => $total,
            'currentIndex' => $current,
            'currentTipo' => $currentTipo,
            'hasMultiple' => $total > 1,
        ]);
    }
}
