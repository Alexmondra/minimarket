<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Proveedor -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Proveedor *</label>
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.300ms="searchProveedor" 
                       placeholder="Buscar por nombre o RUC..."
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                @if($showProveedorDropdown && count($proveedoresResultados) > 0)
                    <div class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        @foreach($proveedoresResultados as $prov)
                            <button type="button" 
                                    wire:click="seleccionarProveedor({{ $prov['id'] }}, '{{ $prov['nombre'] }}')"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600 border-b border-gray-100 dark:border-gray-600 last:border-0">
                                <div class="font-medium">{{ $prov['nombre'] }}</div>
                                <div class="text-xs text-gray-500">{{ $prov['numero_documento'] }} - {{ $prov['razon_social'] }}</div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('proveedorId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Sucursal -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sucursal *</label>
            <select wire:model="sucursalId"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="">Seleccionar sucursal</option>
                @foreach($this->sucursales as $suc)
                    <option value="{{ $suc->id }}">{{ $suc->nombre_sucursal }}</option>
                @endforeach
            </select>
            @error('sucursalId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Tipo Comprobante -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo Comprobante *</label>
            <select wire:model="tipoComprobante"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="factura">Factura</option>
                <option value="boleta">Boleta</option>
                <option value="nota_credito">Nota de Crédito</option>
                <option value="nota_debito">Nota de Débito</option>
            </select>
        </div>

        <!-- N° Factura -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° Factura Proveedor</label>
            <input type="text" wire:model="numeroFactura"
                   placeholder="Opcional"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>

        <!-- Fecha -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Recepción *</label>
            <input type="date" wire:model="fechaRecepcion"
                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>

        <!-- Observaciones -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Observaciones</label>
            <textarea wire:model="observaciones" rows="2"
                      class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
        </div>
    </div>
</div>
