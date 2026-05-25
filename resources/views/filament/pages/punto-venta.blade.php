<x-filament-panels::page>
    <div class="mx-auto w-full max-w-3xl space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Punto de venta</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Sucursal activa: <span class="font-medium text-gray-800 dark:text-gray-200">{{ $this->sucursalNombre }}</span>
            </p>
        </div>

        @if ($this->tieneCajaAbierta())
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-5 dark:border-emerald-700 dark:bg-emerald-950/20">
                <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">
                    Tienes una caja abierta. Ya puedes registrar ventas.
                </p>
            </div>
        @else
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-5 dark:border-amber-700 dark:bg-amber-950/20">
                <p class="text-sm font-medium text-amber-700 dark:text-amber-300">
                    No tienes una caja abierta en esta sucursal. Usa el botón "Abrir caja" para continuar.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>

