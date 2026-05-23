<x-filament::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($this->getSucursales() as $sucursal)
            <a href="{{ \App\Filament\Clusters\Configuraciones\Resources\Series\SerieResource::getUrl('index', ['tableFilters[sucursal_id][value]' => $sucursal->id]) }}"
               class="block group">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200 hover:border-primary-300 group-hover:-translate-y-1">
                    <!-- Foto -->
                    <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                        @if($sucursal->imagen_sucursal)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($sucursal->imagen_sucursal) }}"
                                 alt="{{ $sucursal->nombre_sucursal }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <x-filament::icon name="heroicon-o-building-storefront" class="w-16 h-16" />
                            </div>
                        @endif
                    </div>
                    <!-- Nombre -->
                    <div class="p-4 text-center">
                        <h3 class="font-semibold text-gray-900 text-lg group-hover:text-primary-600 transition-colors">
                            {{ $sucursal->nombre_sucursal }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $sucursal->direccion }}</p>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full">
                <x-filament::empty-state
                    heading="No hay sucursales activas"
                    description="No se encontraron sucursales activas para tu empresa."
                    icon="heroicon-o-building-storefront" />
            </div>
        @endforelse
    </div>
</x-filament::page>
