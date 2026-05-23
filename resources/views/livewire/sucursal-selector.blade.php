@if ($visible)
    <div class="hidden sm:block">
        <label class="sr-only" for="topbar-sucursal-selector">Sucursal activa</label>
        <select
            id="topbar-sucursal-selector"
            wire:model.live="selectedSucursalId"
            class="h-9 max-w-56 rounded-md border border-gray-300 bg-white px-3 text-xs font-medium text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
        >
            <option value="">Todas las sucursales</option>

            @foreach ($sucursales as $sucursal)
                <option value="{{ $sucursal['id'] }}">{{ $sucursal['nombre'] }}</option>
            @endforeach
        </select>
    </div>
@endif
