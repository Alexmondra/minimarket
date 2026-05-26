<?php

namespace App\Http\Controllers\Filament;

use App\Models\Archivo;
use App\Support\SucursalContext;
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

    public function viewDocumentoPdf(\App\Models\Documento $documento): Response
    {
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $documento->sucursal_id), 403);

        if ($documento->tipo_comprobante === 'TICKET') {
            $documento->load([
                'empresa',
                'sucursal',
                'cliente',
                'detalles.presentacion.unidadMedida',
            ]);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ventas.pdf', ['documento' => $documento]);
            $ventaFileService = app(\App\Support\Ventas\VentaFileService::class);
            $ventaFileService->guardarPdf($documento, $pdf->output());

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $documento->serie . '-' . $documento->numero . '.pdf"',
            ]);
        }

        $archivo = $documento->archivos()->where('tipo_archivo', 'pdf')->first();

        if (!$archivo || !Storage::disk('local')->exists($archivo->ruta_archivo)) {
            $documento->load([
                'empresa',
                'sucursal',
                'cliente',
                'detalles.presentacion.unidadMedida',
            ]);
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('ventas.pdf', ['documento' => $documento]);
            $ventaFileService = app(\App\Support\Ventas\VentaFileService::class);
            
            $documento->archivos()->where('tipo_archivo', 'pdf')->delete();
            $archivo = $ventaFileService->guardarPdf($documento, $pdf->output());
        }

        $path = Storage::disk('local')->path($archivo->ruta_archivo);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.($archivo->nombre_archivo ?: basename($path)).'"',
        ]);
    }

    public function viewDocumentoTicket(\App\Models\Documento $documento): Response
    {
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $documento->sucursal_id), 403);

        if ($documento->tipo_comprobante === 'TICKET') {
            $documento->load([
                'empresa',
                'sucursal',
                'cliente',
                'detalles.presentacion.unidadMedida',
            ]);
            $htmlTicket = view('ventas.ticket', ['documento' => $documento])->render();
            $ventaFileService = app(\App\Support\Ventas\VentaFileService::class);
            $ventaFileService->guardarTicketHtml($documento, $htmlTicket);

            return response($htmlTicket, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'inline; filename="' . $documento->serie . '-' . $documento->numero . '.html"',
            ]);
        }

        $archivo = $documento->archivos()->where('tipo_archivo', 'ticket_html')->first();

        if (!$archivo || !Storage::disk('local')->exists($archivo->ruta_archivo)) {
            $documento->load([
                'empresa',
                'sucursal',
                'cliente',
                'detalles.presentacion.unidadMedida',
            ]);
            $htmlTicket = view('ventas.ticket', ['documento' => $documento])->render();
            $ventaFileService = app(\App\Support\Ventas\VentaFileService::class);
            
            $documento->archivos()->where('tipo_archivo', 'ticket_html')->delete();
            $archivo = $ventaFileService->guardarTicketHtml($documento, $htmlTicket);
        }

        $path = Storage::disk('local')->path($archivo->ruta_archivo);

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="'.($archivo->nombre_archivo ?: basename($path)).'"',
        ]);
    }
}
