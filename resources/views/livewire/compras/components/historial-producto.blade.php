<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xs font-medium text-gray-900 dark:text-white">Historial de Compras</h3>
                    <button wire:click="cerrar" class="text-gray-400 hover:text-gray-600 text-lg leading-none">&times;</button>
                </div>
                <div class="p-4 space-y-3">
                    @forelse($historialCompras as $h)
                        <div class="pb-3 border-b border-gray-100 dark:border-gray-700 last:border-0 last:pb-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="font-medium text-xs text-gray-900 dark:text-gray-100">{{ $h['proveedor'] ?? 'N/A' }}</span>
                                    <span class="text-xs text-gray-400 ml-2">
                                        {{ \Carbon\Carbon::parse($h['created_at'])->format('d/m/Y') }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400">Lote: {{ $h['codigo_lote'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-gray-500">
                                    Cantidad: <strong class="text-gray-900 dark:text-gray-100">{{ number_format($h['cantidad'], 0) }} und</strong>
                                </span>
                                <span class="text-gray-500">
                                    Total: <strong class="text-gray-900 dark:text-gray-100">S/ {{ number_format($h['total'], 2) }}</strong>
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-xs text-center py-4">No hay compras anteriores de este producto</p>
                    @endforelse
                </div>
                <div class="flex justify-end px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="cerrar"
                            class="px-3 py-1.5 text-xs text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
