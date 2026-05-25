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
}
