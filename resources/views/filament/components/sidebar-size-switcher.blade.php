<div
    x-data="{ size: localStorage.getItem('fi-sidebar-size') || 'comfortable' }"
    x-init="document.documentElement.dataset.sidebarSize = size"
    x-show="$store.sidebar.isOpen"
    class="sidebar-size-switcher"
>
    <div class="sidebar-size-switcher__header">
        <p class="sidebar-size-switcher__eyebrow">Espacio del menu</p>
        <p class="sidebar-size-switcher__hint">Elige la densidad que te resulte mas comoda.</p>
    </div>

    <div class="sidebar-size-switcher__controls">
        <button
            type="button"
            class="sidebar-size-switcher__btn"
            x-bind:class="{ 'is-active': size === 'compact' }"
            x-on:click="size = 'compact'; localStorage.setItem('fi-sidebar-size', size); document.documentElement.dataset.sidebarSize = size"
        >
            Compacto
        </button>

        <button
            type="button"
            class="sidebar-size-switcher__btn"
            x-bind:class="{ 'is-active': size === 'comfortable' }"
            x-on:click="size = 'comfortable'; localStorage.setItem('fi-sidebar-size', size); document.documentElement.dataset.sidebarSize = size"
        >
            Comodo
        </button>

        <button
            type="button"
            class="sidebar-size-switcher__btn"
            x-bind:class="{ 'is-active': size === 'spacious' }"
            x-on:click="size = 'spacious'; localStorage.setItem('fi-sidebar-size', size); document.documentElement.dataset.sidebarSize = size"
        >
            Amplio
        </button>
    </div>
</div>
