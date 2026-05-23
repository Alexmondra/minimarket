<div
    x-data="{
        zoomed: false,
        scale: 1,
        position: { x: 0, y: 0 },
        isDragging: false,
        dragStart: { x: 0, y: 0 },
        
        get open() {
            return $wire.modalImageUrl !== null;
        },
        
        get currentIndex() {
            return $wire.modalCurrentIndex;
        },
        
        get presentaciones() {
            return $wire.modalPresentaciones || [];
        },
        
        get hasMultiple() {
            return this.presentaciones.length > 1;
        },
        
        get currentTipo() {
            return this.presentaciones[this.currentIndex]?.tipo || '';
        },
        
        close() {
            $wire.cerrarModalImagen();
            this.resetZoom();
        },
        
        navigate(direction) {
            $wire.navegarPresentacion(direction);
            this.resetZoom();
        },
        
        resetZoom() {
            this.zoomed = false;
            this.scale = 1;
            this.position = { x: 0, y: 0 };
        },
        
        toggleZoom() {
            this.zoomed = !this.zoomed;
            this.scale = this.zoomed ? 2 : 1;
            this.position = { x: 0, y: 0 };
        },
        
        handleWheel(e) {
            if (!this.zoomed) {
                this.zoomed = true;
                this.scale = 2;
            } else {
                if (e.deltaY > 0 && this.scale > 1) {
                    this.scale = Math.max(1, this.scale - 0.5);
                } else if (e.deltaY < 0 && this.scale < 4) {
                    this.scale = Math.min(4, this.scale + 0.5);
                }
                if (this.scale <= 1) {
                    this.zoomed = false;
                }
            }
            e.preventDefault();
        },
        
        startDrag(e) {
            if (!this.zoomed || this.scale <= 1) return;
            this.isDragging = true;
            this.dragStart = {
                x: e.clientX - this.position.x,
                y: e.clientY - this.position.y,
            };
        },
        
        doDrag(e) {
            if (!this.isDragging) return;
            this.position = {
                x: e.clientX - this.dragStart.x,
                y: e.clientY - this.dragStart.y,
            };
        },
        
        stopDrag() {
            this.isDragging = false;
        },
        
        handleKeydown(e) {
            if (e.key === 'Escape') this.close();
            if (e.key === 'ArrowLeft') this.navigate(-1);
            if (e.key === 'ArrowRight') this.navigate(1);
        },
    }"
    x-init="
        $watch('open', val => {
            if (val) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        });
    "
    x-show="open"
    x-on:keydown="handleKeydown"
    x-cloak
    class="fixed inset-0 z-[9999]"
>
    <!-- Backdrop -->
    <div 
        class="absolute inset-0 bg-black/85 backdrop-blur-sm"
        @click="close()"
    ></div>
    
    <!-- Top bar controls -->
    <div class="absolute top-0 left-0 right-0 z-20 flex items-center justify-between px-4 py-3 bg-gradient-to-b from-black/50 to-transparent">
        <!-- Left: info -->
        <div class="flex items-center gap-3 text-white/70 text-sm">
            <template x-if="hasMultiple">
                <span class="bg-white/10 px-2.5 py-1 rounded-full text-xs font-medium" x-text="`${currentIndex + 1} / ${presentaciones.length}`"></span>
            </template>
            <template x-if="currentTipo">
                <span x-text="currentTipo" class="text-white/50 truncate max-w-[200px]"></span>
            </template>
        </div>
        
        <!-- Right: actions -->
        <div class="flex items-center gap-2">
            <!-- Zoom indicator -->
            <button 
                @click="toggleZoom()" 
                class="text-white/70 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10"
                x-tooltip="zoom ? 'Alejar' : 'Acercar'"
            >
                <template x-if="!zoomed">
                    <x-filament::icon icon="heroicon-o-magnifying-glass-plus" class="w-6 h-6" />
                </template>
                <template x-if="zoomed">
                    <x-filament::icon icon="heroicon-o-magnifying-glass-minus" class="w-6 h-6" />
                </template>
            </button>
            
            <!-- Close -->
            <button 
                @click="close()" 
                class="text-white/70 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-white/10"
            >
                <x-filament::icon icon="heroicon-o-x-mark" class="w-6 h-6" />
            </button>
        </div>
    </div>
    
    <!-- Previous button -->
    <template x-if="hasMultiple">
        <button 
            @click="navigate(-1)" 
            class="absolute left-3 top-1/2 -translate-y-1/2 z-20 text-white/70 hover:text-white transition-all p-2.5 rounded-full bg-black/30 hover:bg-black/60 hover:scale-110"
        >
            <x-filament::icon icon="heroicon-o-chevron-left" class="w-7 h-7" />
        </button>
    </template>
    
    <!-- Next button -->
    <template x-if="hasMultiple">
        <button 
            @click="navigate(1)" 
            class="absolute right-3 top-1/2 -translate-y-1/2 z-20 text-white/70 hover:text-white transition-all p-2.5 rounded-full bg-black/30 hover:bg-black/60 hover:scale-110"
        >
            <x-filament::icon icon="heroicon-o-chevron-right" class="w-7 h-7" />
        </button>
    </template>
    
    <!-- Image container -->
    <div class="absolute inset-0 z-10 flex items-center justify-center p-12">
        <div 
            class="relative select-none"
            @mousedown="startDrag"
            @mousemove="doDrag"
            @mouseup="stopDrag"
            @mouseleave="stopDrag"
            @wheel.prevent="handleWheel"
        >
            <img 
                :src="$wire.modalImageUrl ?? ''"
                :style="{
                    transform: `translate(${position.x}px, ${position.y}px) scale(${scale})`,
                    cursor: zoomed ? (isDragging ? 'grabbing' : 'grab') : 'pointer',
                }"
                class="max-w-[85vw] max-h-[80vh] object-contain transition-transform duration-200 ease-out rounded-lg shadow-2xl"
                @click="toggleZoom()"
                alt="Imagen del producto"
            />
            
            <!-- Zoom hint on first load -->
            <div 
                x-show="!zoomed && $wire.modalImageUrl"
                x-transition:leave="transition-opacity duration-300"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute bottom-3 left-1/2 -translate-x-1/2 bg-black/50 text-white/80 text-xs px-3 py-1.5 rounded-full backdrop-blur-sm pointer-events-none"
            >
                Haz clic para ampliar · 
                <template x-if="hasMultiple">
                    <span>← → para navegar · </span>
                </template>
                ESC para cerrar
            </div>
        </div>
    </div>
    
    <!-- Bottom dots navigation (for multiple presentaciones) -->
    <template x-if="hasMultiple">
        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
            <template x-for="(pres, index) in presentaciones" :key="index">
                <button 
                    @click="navigate(index - currentIndex)"
                    :class="index === currentIndex ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/60 w-2'"
                    class="h-2 rounded-full transition-all duration-300"
                ></button>
            </template>
        </div>
    </template>
</div>
