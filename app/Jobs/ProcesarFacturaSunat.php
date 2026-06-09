<?php

namespace App\Jobs;

use App\Models\Documento;
use App\Support\Facturacion\FacturacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcesarFacturaSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    public $backoff = [10, 60, 300, 900];

    public function __construct(public Documento $documento) {}

    public function handle(FacturacionService $facturacionService): void
    {
        $facturacionService->enviarSunat($this->documento);
    }
}
