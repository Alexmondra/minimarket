<?php

namespace App\Livewire\Almacen;

use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\ProductoPresentacionBarra;
use App\Models\UniMedida;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Gestor interactivo de presentaciones y códigos de barra de un producto.
 * Permite listar en cuadrícula, crear, editar y eliminar presentaciones con modals.
 */
class ProductPresentationsManager extends Component
{
    use WithFileUploads;

    public ?Producto $record = null;

    // Control de UI
    public bool $showModal = false;

    public ?int $editingPresentationId = null;

    public bool $showProductModal = false;

    public bool $showDeleteModal = false;

    public ?int $presentationToDeleteId = null;

    /**
     * Indica si la presentación a eliminar tiene asignaciones a sucursales.
     */
    public bool $presentationHasSucursales = false;

    // Campos del Formulario (Presentación)
    public ?string $tipo_presentacion = null;

    public int $cantidad = 1;

    public ?int $unidad_medida_id = null;

    public ?int $presentacion_base_id = null;

    public bool $es_pesable = false;

    public mixed $imagen = null;

    public ?string $existingImagen = null;

    public array $barras = [];

    public ?string $nuevo_codigo_barra = null;

    // Campos del Formulario (Producto)
    public ?string $product_nombre = null;

    public ?int $product_categoria_id = null;

    public ?int $product_marca_id = null;

    public ?string $product_codigo_interno = null;

    public ?string $product_descripcion = null;



    // Búsqueda de autocompletado
    public string $searchBaseTerm = '';

    protected $listeners = [
        'refreshPresentations' => '$refresh',
    ];

    /**
     * Monta el componente e hidrata correctamente el modelo Producto.
     */
    public function mount(Producto $record): void
    {
        $this->record = $record;
    }

    /**
     * Define las reglas de validación básicas del formulario.
     */
    protected function rules(): array
    {
        return [
            'tipo_presentacion' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:1',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'presentacion_base_id' => 'nullable|exists:producto_presentacion,id',
            'es_pesable' => 'boolean',
            'imagen' => $this->imagen && ! is_string($this->imagen) ? 'nullable|image|max:2048' : 'nullable',
        ];
    }

    /**
     * Define los mensajes de validación legibles en español.
     */
    protected function messages(): array
    {
        return [
            'tipo_presentacion.required' => 'El tipo de presentación es requerido.',
            'cantidad.required' => 'La cantidad es requerida.',
            'cantidad.integer' => 'La cantidad debe ser un número entero.',
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'unidad_medida_id.required' => 'La unidad de medida es requerida.',
            'unidad_medida_id.exists' => 'La unidad de medida seleccionada no es válida.',
            'presentacion_base_id.exists' => 'La presentación base seleccionada no es válida.',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.max' => 'La imagen no debe pesar más de 2MB.',
        ];
    }

    /**
     * Abre el modal para crear una nueva presentación.
     */
    public function abrirCrear(): void
    {
        $this->resetForm();
        $this->editingPresentationId = null;

        // Predeterminar la unidad de medida más común (ej. Unidad) si existe
        $unidadDefault = UniMedida::where('abreviatura', 'und')->first();
        if ($unidadDefault) {
            $this->unidad_medida_id = $unidadDefault->id;
        }

        $this->showModal = true;
    }

    /**
     * Abre el modal para editar una presentación existente.
     */
    public function abrirEditar(int $id): void
    {
        $this->resetForm();
        $this->editingPresentationId = $id;

        $presentation = ProductoPresentacion::with('barras')->findOrFail($id);
        $this->tipo_presentacion = $presentation->tipo_presentacion;
        $this->cantidad = $presentation->cantidad;
        $this->unidad_medida_id = $presentation->unidad_medida_id;
        $this->presentacion_base_id = $presentation->presentacion_base_id;
        $this->es_pesable = (bool) $presentation->es_pesable;
        $this->existingImagen = $presentation->imagen_url;
        $this->barras = $presentation->barras->pluck('codigo_barra')->toArray();

        $this->showModal = true;
    }

    /**
     * Limpia las variables y el estado del formulario.
     */
    public function resetForm(): void
    {
        $this->reset([
            'tipo_presentacion',
            'cantidad',
            'unidad_medida_id',
            'presentacion_base_id',
            'es_pesable',
            'imagen',
            'existingImagen',
            'barras',
            'nuevo_codigo_barra',
            'searchBaseTerm',
        ]);
        $this->cantidad = 1;
        $this->es_pesable = false;
        $this->resetErrorBag();
    }

    /**
     * Cierra el modal de presentaciones y limpia los errores y el formulario.
     */
    public function cerrarModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Cierra el modal de producto.
     */
    public function cerrarProductModal(): void
    {
        $this->showProductModal = false;
    }

    /**
     * Agrega un código de barra digitado o escaneado a la lista en memoria.
     */
    public function agregarCodigoBarra(): void
    {
        $this->nuevo_codigo_barra = trim($this->nuevo_codigo_barra);

        if (blank($this->nuevo_codigo_barra)) {
            return;
        }

        // Validación de duplicados locales
        if (in_array($this->nuevo_codigo_barra, $this->barras)) {
            $this->addError('nuevo_codigo_barra', 'Este código ya está agregado en la presentación.');

            return;
        }

        // Validación de duplicados globales en Base de Datos
        $exists = ProductoPresentacionBarra::query()
            ->where('codigo_barra', $this->nuevo_codigo_barra)
            ->when($this->editingPresentationId, function ($query) {
                $query->where('producto_presentacion_id', '!=', $this->editingPresentationId);
            })
            ->exists();

        if ($exists) {
            $this->addError('nuevo_codigo_barra', 'Este código de barras ya está asignado a otro producto o presentación.');

            return;
        }

        $this->barras[] = $this->nuevo_codigo_barra;
        $this->nuevo_codigo_barra = '';
        $this->resetErrorBag('nuevo_codigo_barra');
    }

    /**
     * Remueve un código de barra de la lista en memoria.
     */
    public function removerCodigoBarra(int $index): void
    {
        if (isset($this->barras[$index])) {
            unset($this->barras[$index]);
            $this->barras = array_values($this->barras);
        }
    }

    /**
     * Valida y guarda directamente la presentación en la base de datos (Transacción segura).
     */
    public function guardar(): void
    {
        // Si el usuario escribió un código pero olvidó hacer clic en '+' o presionar Enter, lo agregamos automáticamente
        if (filled($this->nuevo_codigo_barra)) {
            $this->agregarCodigoBarra();
            if ($this->getErrorBag()->has('nuevo_codigo_barra')) {
                return;
            }
        }

        $this->validate();

        // Limpiar presentación base si la cantidad es menor o igual a 1
        if ($this->cantidad <= 1) {
            $this->presentacion_base_id = null;
        }

        DB::transaction(function () {
            $baseId = !empty($this->presentacion_base_id) ? $this->presentacion_base_id : null;

            // Guardar presentación
            if ($this->editingPresentationId) {
                $presentation = ProductoPresentacion::findOrFail($this->editingPresentationId);
                $presentation->update([
                    'tipo_presentacion' => $this->tipo_presentacion,
                    'cantidad' => $this->cantidad,
                    'unidad_medida_id' => $this->unidad_medida_id,
                    'presentacion_base_id' => $baseId,
                    'es_pesable' => $this->es_pesable,
                ]);
            } else {
                $presentation = ProductoPresentacion::create([
                    'producto_id' => $this->record->id,
                    'tipo_presentacion' => $this->tipo_presentacion,
                    'cantidad' => $this->cantidad,
                    'unidad_medida_id' => $this->unidad_medida_id,
                    'presentacion_base_id' => $baseId,
                    'es_pesable' => $this->es_pesable,
                ]);
            }

            // Gestionar imagen
            if ($this->imagen && ! is_string($this->imagen)) {
                // Eliminar imagen anterior si existe
                if ($presentation->imagen) {
                    Storage::disk('public')->delete($presentation->imagen);
                }
                $path = $this->imagen->store('productos/presentaciones', 'public');
                $presentation->update(['imagen' => $path]);
            }

            // Sincronizar códigos de barras
            if (empty($this->barras)) {
                ProductoPresentacionBarra::where('producto_presentacion_id', $presentation->id)->delete();
            } else {
                ProductoPresentacionBarra::where('producto_presentacion_id', $presentation->id)
                    ->whereNotIn('codigo_barra', $this->barras)
                    ->delete();

                foreach ($this->barras as $code) {
                    ProductoPresentacionBarra::firstOrCreate([
                        'producto_presentacion_id' => $presentation->id,
                        'codigo_barra' => $code,
                    ]);
                }
            }

            Notification::make()
                ->title($this->editingPresentationId ? 'Presentación actualizada' : 'Presentación agregada')
                ->success()
                ->send();
        });

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('refreshPresentations');
    }

    /**
     * Abre el modal de confirmación de eliminación.
     * Bloquea la eliminación si la presentación está asignada a alguna sucursal.
     */
    public function confirmDelete(int $id): void
    {
        $presentation = ProductoPresentacion::findOrFail($id);

        // Verificar si la presentación está asignada a alguna sucursal (a través de lotes)
        $tieneSucursales = $presentation->productoSucursales()->exists()
            || $presentation->lotePresentaciones()->exists();

        if ($tieneSucursales) {
            Notification::make()
                ->title('No se puede eliminar')
                ->body('Esta presentación tiene existencias o está asignada a una o más sucursales. Desasígnala primero antes de eliminarla.')
                ->warning()
                ->persistent()
                ->send();

            return;
        }

        $this->presentationToDeleteId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Borrado lógico (softdelete) de la presentación confirmada y remoción física de su imagen.
     */
    public function delete(): void
    {
        if (!$this->presentationToDeleteId) {
            return;
        }

        $id = $this->presentationToDeleteId;

        DB::transaction(function () use ($id) {
            $presentation = ProductoPresentacion::findOrFail($id);

            // Borrar imagen física
            if ($presentation->imagen) {
                Storage::disk('public')->delete($presentation->imagen);
            }

            // Eliminar códigos de barra asociados
            $presentation->barras()->delete();

            // Eliminar presentación (softdelete)
            $presentation->delete();

            Notification::make()
                ->title('Presentación eliminada correctamente')
                ->success()
                ->send();
        });

        $this->presentationToDeleteId = null;
        $this->showDeleteModal = false;
        $this->dispatch('refreshPresentations');
    }

    /**
     * Carga los datos del producto y abre el modal de edición del producto.
     */
    public function abrirEditarProducto(): void
    {
        $this->product_nombre = $this->record->nombre;
        $this->product_categoria_id = $this->record->categoria_id;
        $this->product_marca_id = $this->record->marca_id;
        $this->product_codigo_interno = $this->record->codigo_interno;
        $this->product_descripcion = $this->record->descripcion;
        $this->showProductModal = true;
    }

    /**
     * Guarda los cambios del producto directamente en la base de datos (Transacción segura).
     */
    public function guardarProducto(): void
    {
        $this->validate([
            'product_nombre' => 'required|string|max:255',
            'product_categoria_id' => 'required|exists:categorias,id',
            'product_marca_id' => 'required|exists:marcas,id',
            'product_codigo_interno' => 'nullable|string|max:255|unique:productos,codigo_interno,'.$this->record->id,
            'product_descripcion' => 'nullable|string',

        ], [
            'product_nombre.required' => 'El nombre del producto es requerido.',
            'product_categoria_id.required' => 'La categoría es requerida.',
            'product_marca_id.required' => 'La marca es requerida.',
        ]);

        DB::transaction(function () {
            $this->record->update([
                'nombre' => $this->product_nombre,
                'categoria_id' => $this->product_categoria_id,
                'marca_id' => $this->product_marca_id,
                'codigo_interno' => $this->product_codigo_interno,
                'descripcion' => $this->product_descripcion,
                'afecto_igv' => true,
                'activo' => true,
            ]);

            Notification::make()
                ->title('Datos del producto actualizados')
                ->success()
                ->send();
        });

        $this->showProductModal = false;
        $this->dispatch('refreshPresentations');
    }

    /**
     * Obtiene el listado de categorías activas.
     */
    public function getCategoriasProperty()
    {
        return Categoria::where('empresa_id', auth()->user()->empresa_id)->orderBy('nombre')->get();
    }

    /**
     * Obtiene el listado de marcas activas.
     */
    public function getMarcasProperty()
    {
        return Marca::where('empresa_id', auth()->user()->empresa_id)->orderBy('nombre')->get();
    }

    /**
     * Obtiene el listado de unidades de medida activas.
     */
    public function getUnidadesMedidaProperty()
    {
        return UniMedida::where('activo', true)->orderBy('nombre')->get();
    }

    /**
     * Obtiene otras presentaciones del mismo producto para ser seleccionadas como base.
     * Excluye a sí misma en caso de edición para evitar referencias circulares.
     */
    public function getPresentacionesDisponiblesProperty()
    {
        return ProductoPresentacion::query()
            ->with('unidadMedida')
            ->where('producto_id', $this->record->id)
            ->when($this->editingPresentationId, function ($query) {
                $query->where('id', '!=', $this->editingPresentationId);
            })
            ->when(filled($this->searchBaseTerm), function ($query) {
                $query->where('tipo_presentacion', 'like', '%'.$this->searchBaseTerm.'%');
            })
            ->get();
    }

    /**
     * Sugerencia de tipos de presentación populares de la base de datos.
     */
    public function getTiposSugeridosProperty(): array
    {
        return ProductoPresentacion::query()
            ->join('productos', 'producto_presentacion.producto_id', '=', 'productos.id')
            ->where('productos.empresa_id', auth()->user()->empresa_id)
            ->whereNull('producto_presentacion.deleted_at')
            ->distinct()
            ->limit(10)
            ->pluck('producto_presentacion.tipo_presentacion')
            ->filter()
            ->toArray();
    }

    public function render()
    {
        $presentaciones = ProductoPresentacion::with(['unidadMedida', 'presentacionBase.unidadMedida', 'barras', 'productoSucursales', 'lotePresentaciones'])
            ->where('producto_id', $this->record->id)
            ->get();

        return view('livewire.almacen.product-presentations-manager', [
            'presentaciones' => $presentaciones,
        ]);
    }
}
