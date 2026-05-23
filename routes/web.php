<?php

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
