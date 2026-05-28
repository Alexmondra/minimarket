<?php

use App\Http\Controllers\Filament\ArchivoPrivadoController;
use App\Http\Controllers\Filament\CompraComprobanteController;
use App\Http\Controllers\Filament\PresentacionSearchController;
use App\Models\Empresa;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $empresa = Empresa::query()->find(1) ?? Empresa::query()->first();

    if (! $empresa) {
        return view('public.inicio', ['empresa' => null]);
    }

    if ($empresa->slug) {
        return redirect()->to('/'.$empresa->slug);
    }

    return view('public.inicio', ['empresa' => $empresa]);
});

Route::get('/filament/presentaciones/search', [PresentacionSearchController::class, 'search'])
    ->middleware(['web', 'auth']);

Route::get('/filament/compras/comprobante/{compra}', [CompraComprobanteController::class, 'view'])
    ->middleware(['web', 'auth'])
    ->name('filament.compras.comprobante');

Route::get('/filament/archivos/{archivo}/view', [ArchivoPrivadoController::class, 'view'])
    ->middleware(['web', 'auth'])
    ->name('filament.archivos.view');

Route::get('/filament/archivos/{archivo}/download', [ArchivoPrivadoController::class, 'download'])
    ->middleware(['web', 'auth'])
    ->name('filament.archivos.download');

Route::get('/filament/documentos/{documento}/pdf', [ArchivoPrivadoController::class, 'viewDocumentoPdf'])
    ->middleware(['web', 'auth'])
    ->name('filament.documentos.pdf');

Route::get('/filament/documentos/{documento}/ticket', [ArchivoPrivadoController::class, 'viewDocumentoTicket'])
    ->middleware(['web', 'auth'])
    ->name('filament.documentos.ticket');

Route::get('/{slug}', function (string $slug) {
    $empresa = Empresa::query()->where('slug', $slug)->first();

    if (! $empresa) {
        abort(404);
    }

    return view('public.inicio', ['empresa' => $empresa]);
})->where('slug', '[a-z0-9][a-z0-9-]*[a-z0-9]|[a-z0-9]');
