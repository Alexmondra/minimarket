<x-filament-panels::page>
    <div class="mx-auto w-full max-w-4xl space-y-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-950 dark:text-white">Seleccionar sucursal</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Elige la sucursal con la que vas a trabajar en esta sesión.
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($sucursales as $sucursal)
                <button
                    type="button"
                    wire:click="seleccionar({{ $sucursal['id'] }})"
                    class="rounded-lg border border-gray-200 bg-white p-4 text-left transition hover:border-primary-500 hover:bg-primary-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-500 dark:hover:bg-primary-950/30"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $sucursal['nombre'] }}
                            </div>
                            @if ($sucursal['direccion'])
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $sucursal['direccion'] }}
                                </div>
                            @endif
                        </div>

                        @if ($sucursal['codigo'])
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-[11px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ $sucursal['codigo'] }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
