<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Support\Facturacion\FacturacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcesarNotaCreditoSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying.
     */
    public $backoff = [10, 60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Documento $notaCredito,
        public Documento $documentoAfectado,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(FacturacionService $facturacionService): void
    {
        $facturacionService->procesarNota($this->notaCredito, $this->documentoAfectado);
    }
}
