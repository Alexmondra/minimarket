<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Models\Sunat;
use App\Support\Facturacion\FacturacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcesarNotaCreditoSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [15, 60];

    public function __construct(
        public Documento $notaCredito,
        public Documento $documentoAfectado,
    ) {}

    public function handle(FacturacionService $facturacionService): void
    {
        $facturacionService->enviarNotaSunat(
            $this->notaCredito,
            $this->documentoAfectado,
            throwOnConnectionError: true,
            intento: $this->attempts()
        );
    }

    public function failed(?Throwable $exception): void
    {
        Sunat::updateOrCreate(
            ['documento_id' => $this->notaCredito->id],
            [
                'empresa_id' => $this->notaCredito->empresa_id,
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => 'ERROR_CONEXION',
                'mensaje_sunat' => 'Se agotaron los 3 intentos de conexión con SUNAT (Nota de Crédito): ' . ($exception?->getMessage() ?? 'Error desconocido.'),
                'fecha_respuesta' => now(),
            ]
        );
    }
}
