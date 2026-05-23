<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresentacionSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('query');
        $empresaId = auth()->user()->empresa_id;

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $results = DB::table('producto_presentacion')
            ->join('productos', 'producto_presentacion.producto_id', '=', 'productos.id')
            ->where('productos.empresa_id', $empresaId)
            ->where('producto_presentacion.tipo_presentacion', 'like', '%' . $query . '%')
            ->whereNull('producto_presentacion.deleted_at')
            ->whereNull('productos.deleted_at')
            ->distinct()
            ->pluck('producto_presentacion.tipo_presentacion')
            ->take(10)
            ->values()
            ->toArray();

        return response()->json($results);
    }
}
