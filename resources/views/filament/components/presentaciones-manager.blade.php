<div>
    @if ($getRecord() && $getRecord()->exists)
        @livewire('almacen.product-presentations-manager', ['record' => $getRecord()])
    @else
        <div class="p-8 text-center border-2 border-dashed border-slate-300 dark:border-[#1d2745]/60 rounded-3xl bg-slate-50/50 dark:bg-slate-900/10 shadow-sm">
            <div class="flex flex-col items-center gap-3 text-slate-400 dark:text-slate-600">
                <div class="p-3 bg-white dark:bg-slate-900 rounded-full border border-slate-200 dark:border-[#1d2745]/60 shadow-sm">
                    <svg class="h-8 w-8 stroke-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white uppercase tracking-wider">Gestión de Presentaciones</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium max-w-[280px]">
                    Guarde primero los datos básicos del producto para habilitar la adición y edición de presentaciones y códigos de barra.
                </p>
            </div>
        </div>
    @endif
</div>
