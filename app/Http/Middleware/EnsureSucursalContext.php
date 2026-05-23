<?php

namespace App\Http\Middleware;

use App\Filament\Pages\SeleccionarSucursal;
use App\Support\SucursalContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSucursalContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $context = app(SucursalContext::class);
        $context->normalizeSession($user);

        if ($context->hasNoSucursalAccess($user)) {
            abort(403, 'Tu usuario no tiene una sucursal asignada.');
        }

        if ($request->is('admin/seleccionar-sucursal*')) {
            return $next($request);
        }

        if ($context->requiresSelectionPage($user)) {
            return redirect(SeleccionarSucursal::getUrl());
        }

        return $next($request);
    }
}
