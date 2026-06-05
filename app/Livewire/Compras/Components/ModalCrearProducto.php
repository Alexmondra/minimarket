<?php

namespace App\Livewire\Compras\Components;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UniMedida;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ModalCrearProducto extends Component
{
    public bool $showModal = false;

    // Datos del producto
    public ?int $empresaId = null;

    public ?string $nombre = null;

    public ?int $categoriaId = null;

    public ?int $marcaId = null;

    public ?string $codigoInterno = null;

    public bool $afectoIgv = true;

    // Datos de presentación
    public ?int $unidadMedidaId = null;

    public int $cantidadPorEmpaque = 1;

    public ?string $tipoPresentacion = null;

    public ?string $codigoBarra = null;

    // Nuevos: crear categoría/marca al vuelo
    public bool $showCrearCategoria = false;

    public ?string $nuevaCategoria = null;

    public bool $showCrearMarca = false;

    public ?string $nuevaMarca = null;

    protected $listeners = ['abrirModalCrearProducto' => 'abrir'];

    public function abrir(): void
    {
        $this->resetForm();
        $this->empresaId = Auth::user()?->empresa_id ?? 1;
        $this->showModal = true;
    }

    public function cerrar(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'nombre', 'categoriaId', 'marcaId', 'codigoInterno',
            'afectoIgv', 'unidadMedidaId', 'cantidadPorEmpaque',
            'tipoPresentacion', 'codigoBarra', 'showCrearCategoria',
            'nuevaCategoria', 'showCrearMarca', 'nuevaMarca',
        ]);

        $this->unidadMedidaId = UniMedida::where('abreviatura', 'und')->value('id');
    }

    public function toggleCrearCategoria(): void
    {
        $this->showCrearCategoria = ! $this->showCrearCategoria;
        $this->nuevaCategoria = null;
    }

    public function toggleCrearMarca(): void
    {
        $this->showCrearMarca = ! $this->showCrearMarca;
        $this->nuevaMarca = null;
    }

    public function getCategoriasProperty()
    {
        return Categoria::all();
    }

    public function getMarcasProperty()
    {
        return Marca::all();
    }

    public function getUnidadesMedidaProperty()
    {
        return UniMedida::where('activo', true)->get();
    }

    public function crearProducto(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'categoriaId' => 'nullable|exists:categorias,id',
            'marcaId' => 'nullable|exists:marcas,id',
            'codigoInterno' => 'nullable|string|max:100|unique:productos,codigo_interno',
            'unidadMedidaId' => 'required|exists:unidades_medida,id',
            'tipoPresentacion' => 'required|string|max:255',
            'cantidadPorEmpaque' => 'required|integer|min:1',
            'codigoBarra' => 'nullable|string|max:100',
        ]);

        $empresaId = $this->empresaId ?? Auth::user()?->empresa_id ?? 1;

        // Crear categoría si se indicó una nueva
        if ($this->showCrearCategoria && $this->nuevaCategoria) {
            $categoria = Categoria::create([
                'empresa_id' => $empresaId,
                'nombre' => $this->nuevaCategoria,
            ]);
            $this->categoriaId = $categoria->id;
        }

        // Crear marca si se indicó una nueva
        if ($this->showCrearMarca && $this->nuevaMarca) {
            $marca = Marca::create([
                'empresa_id' => $empresaId,
                'nombre' => $this->nuevaMarca,
            ]);
            $this->marcaId = $marca->id;
        }

        DB::transaction(function () use ($empresaId) {
            $producto = Producto::create([
                'empresa_id' => $empresaId,
                'categoria_id' => $this->categoriaId,
                'marca_id' => $this->marcaId,
                'codigo_interno' => $this->codigoInterno ?? 'PROD-'.strtoupper(substr(md5(uniqid()), 0, 8)),
                'nombre' => $this->nombre,
                'slug' => str($this->nombre)->slug(),
                'afecto_igv' => $this->afectoIgv,
                'activo' => true,
            ]);

            // Crear presentación básica
            $pres = ProductoPresentacion::create([
                'producto_id' => $producto->id,
                'unidad_medida_id' => $this->unidadMedidaId,
                'cantidad' => $this->cantidadPorEmpaque,
                'tipo_presentacion' => $this->tipoPresentacion,
            ]);

            if (filled($this->codigoBarra)) {
                $pres->barras()->create([
                    'codigo_barra' => trim($this->codigoBarra),
                ]);
            }

            Notification::make()
                ->title("Producto {$this->nombre} creado correctamente")
                ->success()
                ->send();

            $this->dispatch('productoCreado', productoId: $producto->id);
        });

        $this->cerrar();
    }

    public function render()
    {
        return view('livewire.compras.components.modal-crear-producto');
    }
}
