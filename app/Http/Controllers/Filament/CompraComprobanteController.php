<?php

namespace App\Http\Controllers\Filament;

use App\Models\Compra;
use App\Support\SucursalContext;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CompraComprobanteController
{
    public function view(Compra $compra): Response
    {
        abort_unless(app(SucursalContext::class)->canAccessSucursal((int) $compra->sucursal_id), 403);

        if (!$compra->archivo_comprobante || !Storage::disk('local')->exists($compra->archivo_comprobante)) {
            abort(404);
        }

        $path = Storage::disk('local')->path($compra->archivo_comprobante);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ];

        $mime = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}
