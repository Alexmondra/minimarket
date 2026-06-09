<?php

namespace App\Http\Controllers\Filament;

use App\Models\Archivo;
use App\Models\Documento;
use App\Support\SucursalContext;
use App\Support\Ventas\VentaFileService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ArchivoPrivadoController
{
    public function view(Archivo $archivo): Response
    {
        $documento = $archivo->documento;

        abort_unless($documento, 404);
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $documento->sucursal_id), 403);
        abort_unless(Storage::disk('local')->exists($archivo->ruta_archivo), 404);

        $path = Storage::disk('local')->path($archivo->ruta_archivo);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.($archivo->nombre_archivo ?: basename($path)).'"',
        ]);
    }

    public function download(Archivo $archivo): Response
    {
        $documento = $archivo->documento;

        abort_unless($documento, 404);
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $documento->sucursal_id), 403);
        abort_unless(Storage::disk('local')->exists($archivo->ruta_archivo), 404);

        return Storage::disk('local')->download(
            $archivo->ruta_archivo,
            $archivo->nombre_archivo ?: basename($archivo->ruta_archivo)
        );
    }

    public function viewDocumentoPdf(Documento $documento): Response
    {
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $documento->sucursal_id), 403);

        if ($documento->tipo_comprobante === 'TICKET') {
            $documento->load([
                'empresa',
                'sucursal.ubigeoRel',
                'cliente',
                'sunat',
                'detalles.presentacion.unidadMedida',
            ]);
            $pdf = Pdf::loadView('ventas.pdf', ['documento' => $documento]);
            $tmpPath = tempnam(sys_get_temp_dir(), 'ticket-pdf-');
            $pdf->save($tmpPath);

            return response()->file($tmpPath, [
                'Content-Disposition' => 'inline; filename="'.$documento->serie.'-'.$documento->numero.'.pdf"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
            ])->deleteFileAfterSend(true);
        }

        $archivo = $documento->archivos()->where('tipo_archivo', 'pdf')->first();

        if (! $archivo || ! Storage::disk('local')->exists($archivo->ruta_archivo)) {
            $documento->load([
                'empresa',
                'sucursal.ubigeoRel',
                'cliente',
                'sunat',
                'detalles.presentacion.unidadMedida',
            ]);
            $pdf = Pdf::loadView('ventas.pdf', ['documento' => $documento]);
            $ventaFileService = app(VentaFileService::class);

            $documento->archivos()->where('tipo_archivo', 'pdf')->delete();
            $archivo = $ventaFileService->guardarPdf($documento, $pdf->output());
        }

        $path = Storage::disk('local')->path($archivo->ruta_archivo);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.($archivo->nombre_archivo ?: basename($path)).'"',
        ]);
    }

    public function viewDocumentoTicket(Documento $documento): Response
    {
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $documento->sucursal_id), 403);

        $documento->load([
            'empresa',
            'sucursal',
            'cliente',
            'detalles.presentacion.unidadMedida',
        ]);
        $htmlTicket = view('ventas.ticket', ['documento' => $documento])->render();

        return response($htmlTicket, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="'.$documento->serie.'-'.$documento->numero.'.html"',
        ]);
    }
}
