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

            return $sunat->fresh();
        }

        try {
            $see = $this->seeFactory->make($documento->empresa);
            $greenterDocument = $this->documentFactory->make($documento);
            $xmlFirmado = $see->getXmlSigned($greenterDocument);

            if (! $xmlFirmado) {
                throw new \RuntimeException('Greenter no genero XML firmado.');
            }

            $this->fileService->guardarXmlFirmado($documento, $xmlFirmado);

            $result = $see->send($greenterDocument);

            if ($result instanceof BillResult && $result->getCdrZip()) {
                $this->fileService->guardarCdrZip($documento, $result->getCdrZip());
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
            $error = $result->getError();
            $sunat->update([
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => $error?->getCode() ?: 'ERROR',
                'mensaje_sunat' => $error?->getMessage() ?: 'SUNAT rechazo el envio.',
                'fecha_respuesta' => now(),
            ]);

            return $sunat->fresh();
        }

        $cdr = $result instanceof BillResult ? $result->getCdrResponse() : null;
        $notes = $cdr?->getNotes() ? ' Notas: '.implode(' | ', $cdr->getNotes()) : '';

        $sunat->update([
            'estado_sunat' => true,
            'codigo_respuesta_sunat' => $cdr?->getCode() ?: '0',
            'mensaje_sunat' => trim(($cdr?->getDescription() ?: 'Comprobante aceptado por SUNAT.').$notes),
            'fecha_respuesta' => now(),
        ]);

        return $sunat->fresh();
    }
}
