<?php

namespace App\Support\Ventas;

use App\Models\Archivo;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;

class VentaFileService
{
    public function guardarPdf(Documento $documento, string $pdfContent): ?Archivo
    {
        if (! in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA', 'NOTA_CREDITO', 'NOTA_DEBITO'], true)) {
            return null;
        }

        $filename = $this->nombreBase($documento).'-pdf.pdf';
        $path = $this->rutaBase($documento).'/'.$filename;

        Storage::disk('local')->put($path, $pdfContent);

        return Archivo::updateOrCreate(
            [
                'documento_id' => $documento->id,
                'tipo_archivo' => 'pdf',
            ],
            [
                'proveedor_almacenamiento' => 'local',
                'bucket' => 'private',
                'ruta_archivo' => $path,
                'nombre_archivo' => $filename,
            ]
        );
    }

    protected function rutaBase(Documento $documento): string
    {
        $carpeta = in_array($documento->tipo_comprobante, ['NOTA_CREDITO', 'NOTA_DEBITO'], true)
            ? 'nc_nd'
            : 'facturas_boletas';

        return sprintf(
            'comprobantes/%s/%s',
            ($documento->fecha_emision ?? now())->format('Y/m'),
            $carpeta
        );
    }

    protected function nombreBase(Documento $documento): string
    {
        return sprintf(
            '%s-%s-%s',
            $documento->empresa?->ruc ?? $documento->empresa_id,
            $documento->serie,
            str_pad((string) $documento->numero, 8, '0', STR_PAD_LEFT)
        );
    }
}
