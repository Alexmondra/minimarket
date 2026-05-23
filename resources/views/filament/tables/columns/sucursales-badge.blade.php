<div class="flex flex-wrap gap-1">
    @foreach($getRecord()->sucursales as $sucursal)
        <x-filament::badge color="primary">
            {{ $sucursal->nombre_sucursal }}
        </x-filament::badge>
    @endforeach
</div>
