<?php

namespace App\Filament\Clusters\Almacen\Resources\Productos\Pages;

use App\Filament\Clusters\Almacen\Resources\Productos\ProductoResource;
use App\Models\Producto;
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
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        $data['slug'] = $slug;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
