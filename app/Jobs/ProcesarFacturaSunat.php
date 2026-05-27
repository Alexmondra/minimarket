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

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [10, 60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(public Documento $documento)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(FacturacionService $facturacionService): void
    {
        $facturacionService->procesar($this->documento);
    }
}
