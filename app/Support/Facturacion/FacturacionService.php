<?php

namespace App\Support\Facturacion;

use App\Models\Documento;
use App\Models\Sunat;
use Greenter\Model\Response\BillResult;
use Greenter\Model\Sale\Note;
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
     */
    public function procesar(Documento $documento): Sunat
    {
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

        if (! in_array($documento->tipo_comprobante, ['FACTURA', 'BOLETA'], true)) {
            $sunat->update([
                'codigo_respuesta_sunat' => 'NO_APLICA',
                'mensaje_sunat' => 'Ticket interno: no se envia a SUNAT.',
                'fecha_respuesta' => now(),
            ]);

            $documento->update([
                'estado_sunat' => 'NO_APLICA',
            ]);

            return $sunat->fresh();
        }

        try {
            $see = $this->seeFactory->make($documento->empresa);
            $greenterDocument = $this->documentFactory->make($documento);
            $xmlFirmado = $see->getXmlSigned($greenterDocument);

            if (! $xmlFirmado) {
                throw new \RuntimeException('Greenter no genero XML firmado.');
            }

            // Extraer y guardar el hash del XML firmado
            $hash = $this->extraerHash($xmlFirmado);
            if ($hash) {
                $documento->update(['hash' => $hash]);
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

            return $this->guardarRespuesta($sunat, $documento, $result);
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

            $documento->update([
                'estado_sunat' => 'PENDIENTE',
                'codigo_error_sunat' => 'ERROR',
                'mensaje_sunat' => $exception->getMessage(),
            ]);

            return $sunat->fresh();
        }
    }

    /**
     * Procesa una Nota de Crédito o Débito ante SUNAT.
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

            // Extraer y guardar hash
            $hash = $this->extraerHash($xmlFirmado);
            if ($hash) {
                $nota->update(['hash' => $hash]);
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

            return $this->guardarRespuesta($sunat, $nota, $result);
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

            $nota->update([
                'estado_sunat' => 'PENDIENTE',
                'codigo_error_sunat' => 'ERROR',
                'mensaje_sunat' => $exception->getMessage(),
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

    protected function guardarRespuesta(Sunat $sunat, Documento $documento, mixed $result): Sunat
    {
        if (! $result) {
            $sunat->update([
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => 'SIN_RESPUESTA',
                'mensaje_sunat' => 'SUNAT no retorno respuesta.',
                'fecha_respuesta' => now(),
            ]);

            $documento->update([
                'estado_sunat' => 'PENDIENTE',
                'codigo_error_sunat' => 'SIN_RESPUESTA',
                'mensaje_sunat' => 'SUNAT no retorno respuesta.',
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

            $estadoSunat = $this->mapearEstadoSunat((string) $code);

            $sunat->update([
                'estado_sunat' => $estadoSunat === 'ACEPTADA',
                'codigo_respuesta_sunat' => $code,
                'mensaje_sunat' => $message,
                'fecha_respuesta' => now(),
            ]);

            $documento->update([
                'estado_sunat' => $estadoSunat,
                'codigo_error_sunat' => $code,
                'mensaje_sunat' => $message,
            ]);

            return $sunat->fresh();
        }

        $cdr = $result instanceof BillResult ? $result->getCdrResponse() : null;
        $notes = $cdr?->getNotes() ? ' Notas: '.implode(' | ', $cdr->getNotes()) : '';
        $code = $cdr?->getCode() ?: '0';

        $aceptado = $this->codigoAceptado($code);

        $sunat->update([
            'estado_sunat' => $aceptado,
            'codigo_respuesta_sunat' => $code,
            'mensaje_sunat' => trim(($cdr?->getDescription() ?: 'Comprobante aceptado por SUNAT.').$notes),
            'fecha_respuesta' => now(),
        ]);

        $documento->update([
            'estado_sunat' => $aceptado ? 'ACEPTADA' : $this->mapearEstadoSunat($code),
            'codigo_error_sunat' => $code,
            'mensaje_sunat' => trim(($cdr?->getDescription() ?: 'Comprobante aceptado por SUNAT.').$notes),
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

    /**
     * Mapea el código de respuesta SUNAT a un estado legible.
     */
    protected function mapearEstadoSunat(string $code): string
    {
        if (! is_numeric($code)) {
            return 'PENDIENTE';
        }

        $numericCode = (int) $code;

        if ($numericCode === 0) {
            return 'ACEPTADA';
        }

        if ($numericCode >= 100 && $numericCode <= 1999) {
            return 'OBSERVADA';
        }

        if ($numericCode >= 2000 && $numericCode <= 3999) {
            return 'RECHAZADA';
        }

        if ($numericCode >= 4000) {
            return 'OBSERVADA';
        }

        return 'PENDIENTE';
    }
}
