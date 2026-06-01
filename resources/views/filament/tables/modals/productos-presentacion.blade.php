<div class="p-2 space-y-4">
    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800/60">
        <div class="p-2 bg-indigo-500/10 text-indigo-500 rounded-xl">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7.5L12 3 4 7.5M20 7.5v9L12 21M20 7.5l-8 4.5M4 7.5v9L12 21M4 7.5l8 4.5" />
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">
                Variant: {{ $tipo_presentacion }}
            </h3>
            <p class="text-[10px] text-slate-500 dark:text-slate-400">Productos asociados a esta variante en el inventario</p>
        </div>
    </div>

    @if($productos->isEmpty())
        <div class="py-8 text-center">
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No hay productos con esta presentación actualmente.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-slate-100 dark:border-slate-800/80 shadow-sm">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/40 dark:bg-slate-950/20 text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider border-b dark:border-slate-800">
                        <th scope="col" class="px-5 py-3">Producto</th>
                        <th scope="col" class="px-5 py-3">Código</th>
                        <th scope="col" class="px-5 py-3">Categoría</th>
                        <th scope="col" class="px-5 py-3">Marca</th>
                        <th scope="col" class="px-5 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/40 text-slate-700 dark:text-slate-300">
                    @foreach($productos as $producto)
                        <tr class="hover:bg-slate-50/40 dark:hover:bg-slate-900/30 transition duration-150">
                            <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white max-w-[180px] truncate" title="{{ $producto->nombre }}">
                                {{ $producto->nombre }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold">
                                {{ $producto->codigo_interno ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 max-w-[120px] truncate" title="{{ $producto->categoria->nombre ?? '—' }}">
                                {{ $producto->categoria->nombre ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 max-w-[120px] truncate" title="{{ $producto->marca->nombre ?? '—' }}">
                                {{ $producto->marca->nombre ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ \App\Filament\Clusters\Global\Resources\Productos\ProductoResource::getUrl('edit', ['record' => $producto->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[10px] font-bold text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 rounded-xl shadow-sm hover:shadow active:scale-95 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    <span>Editar Producto</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
