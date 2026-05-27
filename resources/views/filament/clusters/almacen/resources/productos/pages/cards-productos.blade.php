<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 p-4">
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

            $editUrl = \App\Filament\Clusters\Almacen\Resources\Productos\ProductoResource::getUrl('edit', ['record' => $record]);
        @endphp
        
        <div class="group relative flex flex-col bg-white dark:bg-[#0d111f]/90 rounded-2xl border border-slate-200/80 dark:border-[#1d2745]/60 shadow-sm hover:shadow-xl hover:border-indigo-500/50 dark:hover:border-indigo-400/50 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
            
            <!-- Image Area -->
            <div class="aspect-square bg-slate-50 dark:bg-slate-900/60 flex items-center justify-center p-3 relative overflow-hidden border-b border-slate-100 dark:border-[#1d2745]/30">
                @if($imagenUrl)
                    <img 
                        src="{{ $imagenUrl }}" 
                        alt="{{ $record->nombre }}"
                        class="w-full h-full object-contain p-1.5 transition-transform duration-500 group-hover:scale-105 cursor-pointer"
                        wire:click="openProductImageModal('{{ $imagenUrl }}', '{{ $presentacionesJson }}')"
                        loading="lazy"
                    />
                    
                    <!-- Overlay zoom/gallery button on hover -->
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-all duration-300 z-10">
                        <button type="button" 
                                wire:click="openProductImageModal('{{ $imagenUrl }}', '{{ $presentacionesJson }}')"
                                class="p-1.5 rounded-lg bg-white/90 dark:bg-slate-950/80 text-indigo-600 dark:text-indigo-400 border border-slate-200 dark:border-[#1d2745]/60 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 shadow-sm transition duration-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                            </svg>
                        </button>
                    </div>
                @else
                    <a href="{{ $editUrl }}" class="w-full h-full flex items-center justify-center cursor-pointer">
                        <img 
                            src="{{ $noImageUrl }}" 
                            alt="{{ $record->nombre }}"
                            class="w-full h-full object-contain opacity-40 p-3 transition duration-300 group-hover:scale-105"
                        />
                    </a>
                @endif

                <!-- Category/Brand Badges -->
                @if($record->categoria)
                    <div class="absolute bottom-2 left-2 flex flex-wrap gap-1">
                        <span class="px-1.5 py-0.5 rounded-md text-[8px] font-extrabold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                            {{ $record->categoria->nombre }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Info Area (Click to edit) -->
            <a href="{{ $editUrl }}" class="p-3 flex-1 flex flex-col justify-between gap-3 hover:bg-slate-50/50 dark:hover:bg-slate-900/40 cursor-pointer">
                <div class="space-y-1.5 w-full">
                    <p class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2 leading-tight">
                        {{ $record->nombre }}
                    </p>
                    
                    @if($presentacionPrioritaria)
                        <div class="flex items-center gap-1">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[9px] font-bold text-slate-600 dark:text-slate-400 border border-slate-200/50 dark:border-slate-700/50">
                                {{ $presentacionPrioritaria->tipo_presentacion }}
                            </span>
                            @if($presentacionPrioritaria->cantidad > 1)
                                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-500">
                                    x{{ $presentacionPrioritaria->cantidad }} {{ $presentacionPrioritaria->unidadMedida->abreviatura ?? '' }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Footer elements inside card -->
                <div class="pt-2 border-t border-slate-100 dark:border-[#1d2745]/20 flex items-center justify-between">
                    <span class="text-[9px] font-semibold text-slate-400 dark:text-slate-500 font-mono">
                        {{ $record->codigo_interno ?: 'S/C' }}
                    </span>
                    
                    <!-- Arrow link indicator on hover -->
                    <span class="text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 group-hover:translate-x-0.5 transition duration-300 transform -translate-x-1">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    @endforeach
</div>
