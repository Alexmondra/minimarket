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

class ProcesarFacturaSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [15, 60];

    public function __construct(public Documento $documento) {}

    public function handle(FacturacionService $facturacionService): void
    {
        $facturacionService->enviarSunat(
            $this->documento,
            throwOnConnectionError: true,
            intento: $this->attempts()
        );
    }

    public function failed(?Throwable $exception): void
    {
        Sunat::updateOrCreate(
            ['documento_id' => $this->documento->id],
            [
                'empresa_id' => $this->documento->empresa_id,
                'estado_sunat' => false,
                'codigo_respuesta_sunat' => 'ERROR_CONEXION',
                'mensaje_sunat' => 'Se agotaron los 3 intentos de conexión con SUNAT: ' . ($exception?->getMessage() ?? 'Error desconocido.'),
                'fecha_respuesta' => now(),
            ]
        );
    }
}
