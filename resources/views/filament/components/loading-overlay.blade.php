<div id="app-loading-overlay" class="app-loading-overlay" aria-hidden="true">
    <div class="app-loading-overlay__panel">
        <div class="app-loading-overlay__brand">
            <div class="app-loading-overlay__mark">
                @if ($companyLogoUrl)
                    <img src="{{ $companyLogoUrl }}" alt="{{ $companyName }}" class="app-loading-overlay__logo" loading="lazy">
                @else
                    <span class="app-loading-overlay__initials">{{ $companyInitials }}</span>
                @endif
            </div>

            <div class="app-loading-overlay__copy">
                <p class="app-loading-overlay__name">{{ $companyShortName }}</p>
                <p class="app-loading-overlay__subtitle">
                    {{ $sucursalName ?: ($isGlobalView ? 'Preparando vista global' : 'Cargando entorno de trabajo') }}
                </p>
            </div>
        </div>

        <div class="app-loading-overlay__pulse">
            <span class="app-loading-overlay__pulse-bar"></span>
            <span class="app-loading-overlay__pulse-bar"></span>
            <span class="app-loading-overlay__pulse-bar"></span>
        </div>

        <p class="app-loading-overlay__caption" id="app-loading-caption">Cargando...</p>
    </div>
</div>

<script>
    (() => {
        if (window.__minimarketLoadingOverlayInit) {
            return;
        }

        window.__minimarketLoadingOverlayInit = true;

        const overlay = document.getElementById('app-loading-overlay');

        if (!overlay) {
            return;
        }

        let activeLoads = 0;
        let shownAt = 0;
        let delayedRequestTimer = null;
        let initialLoadTimer = null;
        const minimumVisibleMs = 200;
        const revealDelayMs = 150;

        const reveal = () => {
            clearTimeout(delayedRequestTimer);
            clearTimeout(initialLoadTimer);

            if (!overlay.classList.contains('is-visible')) {
                overlay.classList.add('is-visible');
                shownAt = Date.now();
            }

            document.documentElement.classList.add('app-busy');
        };

        const conceal = (force = false) => {
            clearTimeout(delayedRequestTimer);
            clearTimeout(initialLoadTimer);

            const hide = () => {
                if (!force && activeLoads > 0) {
                    return;
                }

                overlay.classList.remove('is-visible');
                document.documentElement.classList.remove('app-busy');
            };

            const elapsed = Date.now() - shownAt;
            const wait = force ? 0 : Math.max(0, minimumVisibleMs - elapsed);

            window.setTimeout(hide, wait);
        };

        const beginLoad = ({ delayed = false } = {}) => {
            activeLoads += 1;

            if (delayed) {
                clearTimeout(delayedRequestTimer);
                delayedRequestTimer = window.setTimeout(() => {
                    if (activeLoads > 0) {
                        reveal();
                    }
                }, revealDelayMs);

                return;
            }

            clearTimeout(delayedRequestTimer);
            delayedRequestTimer = window.setTimeout(() => {
                if (activeLoads > 0) {
                    reveal();
                }
            }, revealDelayMs);
        };

        const endLoad = () => {
            activeLoads = Math.max(0, activeLoads - 1);

            if (activeLoads === 0) {
                conceal();
            }
        };

        if (document.readyState !== 'complete') {
            activeLoads = 1;
            initialLoadTimer = window.setTimeout(() => {
                if (document.readyState !== 'complete') {
                    reveal();
                }
            }, 420);
        }

        window.addEventListener('load', () => {
            activeLoads = 0;
            conceal();
        });

        window.addEventListener('beforeunload', reveal);

        document.addEventListener('livewire:navigating', () => beginLoad());
        document.addEventListener('livewire:navigated', () => {
            activeLoads = 0;
            conceal();
        });

        // Overlay only for page navigation (livewire:navigating), not per-request
    })();
</script>
