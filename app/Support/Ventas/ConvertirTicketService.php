<?php

namespace App\Support\Ventas;

use App\Jobs\ProcesarFacturaSunat;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Serie;
use App\Support\Facturacion\FacturacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ConvertirTicketService
{
    public function __construct(
        protected FacturacionService $facturacionService,
        protected VentaFileService $fileService,
    ) {}

    public function convertir(Documento $documento, string $tipoComprobante, ?int $clienteId = null): Documento
    {
        $tipoComprobante = strtoupper($tipoComprobante);

        if ($documento->tipo_comprobante !== 'TICKET') {
            throw new RuntimeException('Solo se pueden convertir documentos tipo ticket.');
        }

        if (! in_array($tipoComprobante, ['BOLETA', 'FACTURA'], true)) {
            throw new RuntimeException('Tipo de comprobante no válido para conversión.');
        }

        $documento->loadMissing('cliente');
        $cliente = $clienteId ? Cliente::query()->find($clienteId) : $documento->cliente;

        $this->validarCliente($documento, $tipoComprobante, $cliente);

        $documento = DB::transaction(function () use ($documento, $tipoComprobante, $cliente): Documento {
            $serie = Serie::query()
                ->where('sucursal_id', $documento->sucursal_id)
                ->where('tipo_comprobante', $tipoComprobante)
                ->lockForUpdate()
                ->first();

            if (! $serie) {
                $serie = Serie::create([
                    'sucursal_id' => $documento->sucursal_id,
                    'tipo_comprobante' => $tipoComprobante,
                    'serie' => $tipoComprobante === 'FACTURA' ? 'F001' : 'B001',
                    'correlativo' => 1,
                ]);
            }

            $documento->update([
                'tipo_comprobante' => $tipoComprobante,
                'serie' => $serie->serie,
                'numero' => str_pad((string) $serie->correlativo, 8, '0', STR_PAD_LEFT),
                'fecha_emision' => now()->toDateString(),
                'cliente_id' => $cliente?->id,
            ]);

            $serie->increment('correlativo');

            $documento->refresh()->load([
                'empresa',
                'sucursal.ubigeoRel',
                'cliente',
                'sunat',
                'detalles.presentacion.unidadMedida',
            ]);

            $this->facturacionService->preparar($documento);

            return $documento;
        });

        $this->eliminarPdfAnterior($documento);

        $pdf = Pdf::loadView('ventas.pdf', ['documento' => $documento]);
        $this->fileService->guardarPdf($documento, $pdf->output());

        ProcesarFacturaSunat::dispatch($documento);

        return $documento->fresh([
            'cliente',
            'empresa',
            'sucursal',
            'detalles.presentacion.unidadMedida',
            'archivos',
            'sunat',
        ]);
    }

    protected function validarCliente(Documento $documento, string $tipoComprobante, ?Cliente $cliente): void
    {
        if ($tipoComprobante === 'FACTURA') {
            if (! $cliente || $cliente->tipo_documento !== 'RUC' || strlen((string) $cliente->documento) !== 11 || blank($cliente->razon_social)) {
                throw new RuntimeException('La factura requiere un cliente con RUC válido y razón social.');
            }

            return;
        }

        if ((float) $documento->total_neto < 700) {
            return;
        }

        if (! $cliente || $cliente->documento === '00000000' || blank($cliente->documento)) {
            throw new RuntimeException('Las boletas con importe igual o mayor a S/ 700.00 requieren identificar al cliente.');
        }
    }

    protected function eliminarPdfAnterior(Documento $documento): void
    {
        $documento->archivos()
            ->where('tipo_archivo', 'pdf')
            ->get()
            ->each(function ($archivo): void {
                if ($archivo->ruta_archivo && Storage::disk('local')->exists($archivo->ruta_archivo)) {
                    Storage::disk('local')->delete($archivo->ruta_archivo);
                }

                $archivo->forceDelete();
            });
    }
}
