<?php

namespace App\Support\Ventas;

use App\Models\Archivo;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VentaFileService
{
    public function guardarXml(Documento $documento, string $xml): Archivo
    {
        $filename = $this->nombreBase($documento).'.xml';
        $path = $this->rutaBase($documento).'/'.$filename;

        Storage::disk('local')->put($path, $xml);

        return Archivo::create([
            'documento_id' => $documento->id,
            'tipo_archivo' => 'xml',
            'proveedor_almacenamiento' => 'local',
            'bucket' => 'private',
            'ruta_archivo' => $path,
            'nombre_archivo' => $filename,
        ]);
    }

    public function guardarTicketHtml(Documento $documento, string $html): Archivo
    {
        $filename = $this->nombreBase($documento).'-ticket.html';
        $path = $this->rutaBase($documento).'/'.$filename;

        Storage::disk('local')->put($path, $html);

        return Archivo::create([
            'documento_id' => $documento->id,
            'tipo_archivo' => 'ticket_html',
            'proveedor_almacenamiento' => 'local',
            'bucket' => 'private',
            'ruta_archivo' => $path,
            'nombre_archivo' => $filename,
        ]);
    }

    protected function rutaBase(Documento $documento): string
    {
        return sprintf(
            'ventas/%s/%s/%s',
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
