<div x-data x-init="
    const form = $el.closest('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            // Si el origen del evento submit no es el formulario principal, detenemos la propagación y prevenimos la acción
            if (e.target !== form) {
                e.stopPropagation();
                e.preventDefault();
            }
        }, { capture: true });
    }
" class="hidden"></div>
