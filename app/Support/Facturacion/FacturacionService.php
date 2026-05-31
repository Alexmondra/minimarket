<?php

namespace App\Support\Facturacion;

use App\Models\Documento;
use App\Models\Sunat;
use Greenter\Model\Response\BillResult;
use Illuminate\Support\Facades\Log;
use Throwable;

class FacturacionService
{
    public function __construct(
        protected GreenterSeeFactory $seeFactory,
        protected DocumentoGreenterFactory $documentFactory,
        protected FacturacionFileService $fileService,
    ) {}

    /**
     * Procesa un comprobante (FACTURA o BOLETA) ante SUNAT.
     * Todo el estado, código, mensaje y hash se guardan en la tabla sunat.
     */
    public function procesar(Documento $documento): ?Sunat
    {
        if (! in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA'], true)) {
            return null;
        }

        $documento->loadMissing(['empresa.empresaConfig', 'sunat']);

        $sunat = Sunat::updateOrCreate(
            ['documento_id' => $documento->id],
            [
                'empresa_id' => $documento->empresa_id,
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => null,
                'mensaje_sunat' => 'Pendiente de envio SUNAT.',
                'fecha_envio' => now(),
            ]
        );

        try {
            $see = $this->seeFactory->make($documento->empresa);
            $greenterDocument = $this->documentFactory->make($documento);
            $xmlFirmado = $see->getXmlSigned($greenterDocument);

            if (! $xmlFirmado) {
                throw new \RuntimeException('Greenter no genero XML firmado.');
            }

            // Extraer hash del XML y guardarlo en sunat
            $hash = $this->extraerHash($xmlFirmado);
            if ($hash) {
                $sunat->update(['hash' => $hash]);
            }

            $this->fileService->guardarXmlFirmado($documento, $xmlFirmado);

            $result = $see->send($greenterDocument);

            if ($result instanceof BillResult && $result->getCdrZip()) {
                $cdrZip = $result->getCdrZip();
                $this->fileService->guardarCdrZip($documento, $cdrZip);

                $cdrXml = $this->fileService->extraerCdrXml($cdrZip);
                if ($cdrXml) {
                    $this->fileService->guardarCdrXml($documento, $cdrXml);
                }
            }

            return $this->guardarRespuesta($sunat, $result);
        } catch (Throwable $exception) {
            Log::error('Error al enviar documento a SUNAT.', [
                'documento_id' => $documento->id,
                'message' => $exception->getMessage(),
            ]);

            $sunat->update([
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => 'ERROR',
                'mensaje_sunat' => $exception->getMessage(),
                'fecha_respuesta' => now(),
            ]);

            return $sunat->fresh();
        }
    }

    /**
     * Procesa una Nota de Crédito ante SUNAT.
     */
    public function procesarNota(Documento $nota, Documento $documentoAfectado): Sunat
    {
        $nota->loadMissing(['empresa.empresaConfig', 'documentoReferencia', 'detalles.presentacion.unidadMedida']);
        $documentoAfectado->loadMissing(['empresa.empresaConfig', 'sucursal.ubigeoRel', 'cliente', 'detalles.presentacion.unidadMedida']);

        $sunat = Sunat::updateOrCreate(
            ['documento_id' => $nota->id],
            [
                'empresa_id' => $nota->empresa_id,
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => null,
                'mensaje_sunat' => 'Pendiente de envio SUNAT (Nota).',
                'fecha_envio' => now(),
            ]
        );

        try {
            $see = $this->seeFactory->make($nota->empresa);
            $note = $this->documentFactory->makeNotaCredito($nota, $documentoAfectado);
            $xmlFirmado = $see->getXmlSigned($note);

            if (! $xmlFirmado) {
                throw new \RuntimeException('Greenter no genero XML firmado para la Nota.');
            }

            $hash = $this->extraerHash($xmlFirmado);
            if ($hash) {
                $sunat->update(['hash' => $hash]);
            }

            $this->fileService->guardarXmlFirmado($nota, $xmlFirmado);

            $result = $see->send($note);

            if ($result instanceof BillResult && $result->getCdrZip()) {
                $cdrZip = $result->getCdrZip();
                $this->fileService->guardarCdrZip($nota, $cdrZip);

                $cdrXml = $this->fileService->extraerCdrXml($cdrZip);
                if ($cdrXml) {
                    $this->fileService->guardarCdrXml($nota, $cdrXml);
                }
            }

            return $this->guardarRespuesta($sunat, $result);
        } catch (Throwable $exception) {
            Log::error('Error al enviar Nota a SUNAT.', [
                'documento_id' => $nota->id,
                'message' => $exception->getMessage(),
            ]);

            $sunat->update([
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => 'ERROR',
                'mensaje_sunat' => $exception->getMessage(),
                'fecha_respuesta' => now(),
            ]);

            return $sunat->fresh();
        }
    }

    /**
     * Extrae el hash (DigestValue) del XML firmado.
     */
    public function extraerHash(string $xml): ?string
    {
        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $digestValue = $dom->getElementsByTagName('DigestValue')->item(0);

        return $digestValue ? $digestValue->nodeValue : null;
    }

    protected function guardarRespuesta(Sunat $sunat, mixed $result): Sunat
    {
        if (! $result) {
            $sunat->update([
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => 'SIN_RESPUESTA',
                'mensaje_sunat' => 'SUNAT no retorno respuesta.',
                'fecha_respuesta' => now(),
            ]);

            return $sunat->fresh();
        }

        if (! $result->isSuccess()) {
            $cdr = method_exists($result, 'getCdrResponse') ? $result->getCdrResponse() : null;
            $code = null;
            $message = null;

            if ($cdr) {
                $code = $cdr->getCode();
                $message = $cdr->getDescription();
            }

            if (! $code || ! $message) {
                $error = $result->getError();
                $code = $code ?: ($error?->getCode() ?: 'ERROR');
                $message = $message ?: ($error?->getMessage() ?: 'SUNAT rechazo el envio.');
            }

            $sunat->update([
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => $code,
                'mensaje_sunat' => $message,
                'fecha_respuesta' => now(),
            ]);

            return $sunat->fresh();
        }

        $cdr = $result instanceof BillResult ? $result->getCdrResponse() : null;
        $notes = $cdr?->getNotes() ? ' Notas: '.implode(' | ', $cdr->getNotes()) : '';
        $code = $cdr?->getCode() ?: '0';

        $sunat->update([
            'estado_sunat' => $this->codigoAceptado($code),
            'codigo_respuesta_sunat' => $code,
            'mensaje_sunat' => trim(($cdr?->getDescription() ?: 'Comprobante aceptado por SUNAT.').$notes),
            'fecha_respuesta' => now(),
        ]);

        return $sunat->fresh();
    }

    protected function codigoAceptado(string $code): bool
    {
        if (! is_numeric($code)) {
            return false;
        }

        $numericCode = (int) $code;

        return $numericCode === 0 || ($numericCode >= 100 && $numericCode <= 1999);
    }
}
