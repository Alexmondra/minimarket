<?php

namespace App\Support\Facturacion;

use App\Models\Archivo;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FacturacionFileService
{
    public function guardarXmlFirmado(Documento $documento, string $xml): Archivo
    {
        return $this->guardar($documento, 'xml_firmado', 'xml', $xml);
    }

    public function guardarCdrZip(Documento $documento, string $zip): Archivo
    {
        return $this->guardar($documento, 'cdr_zip', 'zip', $zip);
    }

    protected function guardar(Documento $documento, string $tipo, string $extension, string $contenido): Archivo
    {
        $filename = $this->nombreBase($documento).'-'.$tipo.'.'.$extension;
        $path = $this->rutaBase($documento).'/'.$filename;

        Storage::disk('local')->put($path, $contenido);

        return Archivo::create([
            'documento_id' => $documento->id,
            'tipo_archivo' => $tipo,
            'proveedor_almacenamiento' => 'local',
            'bucket' => 'private',
            'ruta_archivo' => $path,
            'nombre_archivo' => $filename,
        ]);
    }

    protected function rutaBase(Documento $documento): string
    {
        return sprintf(
            'facturacion/%s/%s/%s',
            $documento->empresa_id,
            $documento->fecha_emision?->format('Y/m'),
            Str::slug($documento->tipo_comprobante)
        );
    }

    protected function nombreBase(Documento $documento): string
    {
        return sprintf(
            '%s-%s-%s',
            $documento->empresa?->ruc ?? $documento->empresa_id,
            $documento->serie,
            $documento->numero
        );
    }
}
