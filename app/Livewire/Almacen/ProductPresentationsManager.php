<?php

namespace App\Livewire\Almacen;

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
    public bool $product_afecto_igv = true;
    public bool $product_activo = true;

    // Búsqueda de autocompletado
    public string $searchBaseTerm = '';

    protected $listeners = [
        'refreshPresentations' => '$refresh',
    ];

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
            'imagen' => $this->imagen && !is_string($this->imagen) ? 'nullable|image|max:2048' : 'nullable',
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
        $this->existingImagen = $presentation->imagen;
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
            // Guardar presentación
            $presentation = ProductoPresentacion::updateOrCreate(
                ['id' => $this->editingPresentationId],
                [
                    'producto_id' => $this->record->id,
                    'tipo_presentacion' => $this->tipo_presentacion,
                    'cantidad' => $this->cantidad,
                    'unidad_medida_id' => $this->unidad_medida_id,
                    'presentacion_base_id' => $this->presentacion_base_id,
                    'es_pesable' => $this->es_pesable,
                ]
            );

            // Gestionar imagen
            if ($this->imagen && !is_string($this->imagen)) {
                // Eliminar imagen anterior si existe
                if ($presentation->imagen) {
                    Storage::disk('public')->delete($presentation->imagen);
                }
                $path = $this->imagen->store('productos/presentaciones', 'public');
                $presentation->update(['imagen' => $path]);
            }

            // Sincronizar códigos de barras
            // Eliminar códigos removidos
            ProductoPresentacionBarra::where('producto_presentacion_id', $presentation->id)
                ->whereNotIn('codigo_barra', $this->barras)
                ->delete();

            // Agregar nuevos códigos
            foreach ($this->barras as $code) {
                ProductoPresentacionBarra::firstOrCreate([
                    'producto_presentacion_id' => $presentation->id,
                    'codigo_barra' => $code,
                ]);
            }

            Notification::make()
                ->title($this->editingPresentationId ? 'Presentación actualizada' : 'Presentación agregada')
                ->success()
                ->send();
        });

        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Borrado lógico (softdelete) de la presentación y remoción física de su imagen.
     */
    public function eliminarPresentacion(int $id): void
    {
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
        $this->product_afecto_igv = (bool) $this->record->afecto_igv;
        $this->product_activo = (bool) $this->record->activo;

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
            'product_codigo_interno' => 'nullable|string|max:255|unique:productos,codigo_interno,' . $this->record->id,
            'product_descripcion' => 'nullable|string',
            'product_afecto_igv' => 'boolean',
            'product_activo' => 'boolean',
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
                'afecto_igv' => $this->product_afecto_igv,
                'activo' => $this->product_activo,
            ]);

            Notification::make()
                ->title('Datos del producto actualizados')
                ->success()
                ->send();
        });

        $this->showProductModal = false;
    }

    /**
     * Obtiene el listado de categorías activas.
     */
    public function getCategoriasProperty()
    {
        return \App\Models\Categoria::where('empresa_id', auth()->user()->empresa_id)->orderBy('nombre')->get();
    }

    /**
     * Obtiene el listado de marcas activas.
     */
    public function getMarcasProperty()
    {
        return \App\Models\Marca::where('empresa_id', auth()->user()->empresa_id)->orderBy('nombre')->get();
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
                $query->where('tipo_presentacion', 'like', '%' . $this->searchBaseTerm . '%');
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
        $presentaciones = ProductoPresentacion::with(['unidadMedida', 'presentacionBase.unidadMedida', 'barras'])
            ->where('producto_id', $this->record->id)
            ->get();

        return view('livewire.almacen.product-presentations-manager', [
            'presentaciones' => $presentaciones
        ]);
    }
}
