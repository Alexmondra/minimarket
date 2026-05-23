<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-4">
    @php
        $noImageUrl = url('/images/no-image.svg');
    @endphp
    @foreach ($records as $record)
        @php
            // Obtener la presentación prioritaria (unidad > cualquier otra con imagen)
            $presentacionPrioritaria = $record->presentacionPrioritaria();
            
            $imagenUrl = $presentacionPrioritaria?->imagen_url;
            
            // Preparar datos para el modal (todas las presentaciones con imagen)
            $presentacionesConImagen = $record->presentaciones_con_imagen;
            $presentacionesJson = $presentacionesConImagen->map(function ($p) {
                return [
                    'imagen' => $p->imagen_url,
                    'tipo' => $p->tipo_presentacion,
                ];
            })->values()->toJson();
        @endphp
        <div 
            class="flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:shadow-md hover:border-gray-300 dark:hover:border-gray-600"
        >
            <!-- Image area -->
            <div 
                class="aspect-square bg-gray-100 dark:bg-gray-700 flex items-center justify-center p-4 overflow-hidden relative group cursor-pointer"
                @if($imagenUrl)
                    wire:click="openProductImageModal('{{ $imagenUrl }}', '{{ $presentacionesJson }}')"
                @endif
            >
                @if($imagenUrl)
                    <img 
                        src="{{ $imagenUrl }}" 
                        alt="{{ $record->nombre }}"
                        class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-105"
                        loading="lazy"
                    />
                    {{-- Overlay on hover --}}
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-200 flex items-center justify-center">
                        <x-filament::icon 
                            icon="heroicon-o-eye" 
                            class="w-8 h-8 text-white opacity-0 group-hover:opacity-80 transition-all duration-200 drop-shadow-lg" 
                        />
                    </div>
                @else
                    <img 
                        src="{{ $noImageUrl }}" 
                        alt="{{ $record->nombre }}"
                        class="w-full h-full object-contain opacity-40"
                    />
                @endif
            </div>

            <!-- Info area -->
            <div class="p-2 flex-1 flex flex-col items-center justify-center gap-1">
                <p class="text-xs font-medium text-gray-900 dark:text-gray-100 text-center line-clamp-2 leading-tight">
                    {{ $record->nombre }}
                </p>
                @if($presentacionPrioritaria)
                    <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate max-w-full">
                        {{ $presentacionPrioritaria->tipo_presentacion }}
                        @if($presentacionPrioritaria->cantidad > 1)
                            · {{ $presentacionPrioritaria->cantidad }} {{ $presentacionPrioritaria->unidadMedida->abreviatura ?? '' }}
                        @endif
                    </span>
                @endif
            </div>
        </div>
    @endforeach
</div>
