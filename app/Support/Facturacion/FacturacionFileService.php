<?php

namespace App\Support\Facturacion;

use App\Models\Archivo;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;

class FacturacionFileService
{
    public function guardarXmlFirmado(Documento $documento, string $xml): Archivo
    {
        return $this->guardar($documento, 'xml', 'xml', $xml);
    }

    public function guardarCdrZip(Documento $documento, string $zip): Archivo
    {
        return $this->guardar($documento, 'cdr', 'zip', $zip);
    }

    public function extraerCdrXml(string $zip): ?string
    {
        if (! class_exists(\ZipArchive::class)) {
            return null;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'cdr_');
        if ($tmp === false) {
            return null;
        }

        file_put_contents($tmp, $zip);

        try {
            $archive = new \ZipArchive;
            $opened = false;
            if ($archive->open($tmp) !== true) {
                return null;
            }
            $opened = true;

            for ($index = 0; $index < $archive->numFiles; $index++) {
                $name = (string) $archive->getNameIndex($index);
                if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'xml') {
                    continue;
                }

                $content = $archive->getFromIndex($index);

                return $content === false ? null : $content;
            }

            return null;
        } finally {
            if (($opened ?? false) && isset($archive) && $archive instanceof \ZipArchive) {
                $archive->close();
            }

            @unlink($tmp);
        }
    }

    protected function guardar(Documento $documento, string $tipo, string $extension, string $contenido): Archivo
    {
        $filename = $this->nombreBase($documento).'.'.$extension;
        if ($tipo !== 'xml') {
            $filename = $this->nombreBase($documento).'-'.$tipo.'.'.$extension;
        }
        $path = $this->rutaBase($documento).'/'.$filename;

        Storage::disk('local')->put($path, $contenido);

        return Archivo::updateOrCreate(
            [
                'documento_id' => $documento->id,
                'tipo_archivo' => $tipo === 'cdr' ? 'cdr_zip' : $tipo,
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
