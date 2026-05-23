<div class="p-4">
    <h3 class="text-lg font-medium mb-4">
        Presentación: <strong>{{ $tipo_presentacion }}</strong>
    </h3>

    @if($productos->isEmpty())
        <p class="text-gray-500">No hay productos con esta presentación.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Producto</th>
                        <th scope="col" class="px-6 py-3">Código</th>
                        <th scope="col" class="px-6 py-3">Categoría</th>
                        <th scope="col" class="px-6 py-3">Marca</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        <tr class="bg-white border-b">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $producto->nombre }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $producto->codigo_interno ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $producto->categoria->nombre ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $producto->marca->nombre ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
