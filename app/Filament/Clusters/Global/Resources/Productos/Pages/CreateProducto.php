<?php

namespace App\Filament\Clusters\Global\Resources\Productos\Pages;

use App\Filament\Clusters\Global\Resources\Productos\ProductoResource;
use App\Models\Producto;
use App\Models\UniMedida;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateProducto extends CreateRecord
{
    protected static string $resource = ProductoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['empresa_id'] = auth()->user()->empresa_id;

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nombre']);
        }

        // Asegurar que el slug sea único
        $baseSlug = $data['slug'];
        $slug = $baseSlug;
        $counter = 1;
        while (Producto::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }
        $data['slug'] = $slug;

        // Generar siempre código interno (SKU) limpio de forma automática
        $data['codigo_interno'] = Producto::generarCodigoInterno($data['nombre']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Producto $producto */
        $producto = $this->record;

        // Asegurar que el producto siempre nazca con al menos su presentación básica (Unidad)
        if ($producto && $producto->presentaciones()->doesntExist()) {
            $unidadId = UniMedida::where('abreviatura', 'und')->value('id')
                ?: UniMedida::value('id');

            $producto->presentaciones()->create([
                'unidad_medida_id' => $unidadId,
                'cantidad' => 1,
                'tipo_presentacion' => 'Unidad',
                'es_pesable' => false,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
