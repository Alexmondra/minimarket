<?php

use App\Http\Controllers\Filament\ArchivoPrivadoController;
use App\Http\Controllers\Filament\CompraComprobanteController;
use App\Http\Controllers\Filament\PresentacionSearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('public.inicio');
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
