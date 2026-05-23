<x-filament-panels::page>
    {{-- Render the table content (either table or cards) --}}
    {{ $this->table }}
    
    {{-- Product Image Lightbox Modal --}}
    @include('filament.tables.modals.product-image-lightbox')
</x-filament-panels::page>
