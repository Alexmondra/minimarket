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

        // Auto-generar código interno si está vacío (mitad del nombre del producto + 3 números aleatorios)
        if (empty($data['codigo_interno'])) {
            // Sanitizar nombre: remover caracteres especiales y espacios
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $data['nombre']);
            $length = strlen($cleanName);
            $halfLength = max(1, (int) ceil($length / 2));
            $halfName = strtoupper(substr($cleanName, 0, $halfLength));
            
            // Limitar a máximo 8 caracteres la porción del nombre
            if (strlen($halfName) > 8) {
                $halfName = substr($halfName, 0, 8);
            }
            
            // Generar 3 dígitos aleatorios
            $randomDigits = str_pad((string) rand(0, 999), 3, '0', STR_PAD_LEFT);
            $codigo = $halfName . $randomDigits;
            
            // Asegurar unicidad del código interno en la base de datos
            $baseCodigo = $codigo;
            $counter = 1;
            while (Producto::where('codigo_interno', $codigo)->exists()) {
                $codigo = $baseCodigo . '-' . $counter;
                $counter++;
            }
            
            $data['codigo_interno'] = $codigo;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
