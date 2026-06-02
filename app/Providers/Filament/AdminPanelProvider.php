<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Escritorio;
use App\Http\Middleware\EnsureSucursalContext;
use App\Support\SucursalContext;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
            ])
            ->brandName(fn (): string => $this->getPanelBranding()['companyName'])
            ->brandLogo(fn (): HtmlString => new HtmlString(view('filament.components.company-brand', $this->getPanelBranding())->render()))
            ->darkModeBrandLogo(fn (): HtmlString => new HtmlString(view('filament.components.company-brand', $this->getPanelBranding())->render()))
            ->brandLogoHeight('3.25rem')
            ->navigationGroups([
                'Catálogo Global',
                'Compras',
                'Inventario',
                'Movimientos',
                'Reportes',
                'Sunat',
                'Ventas',
                'Configuraciones',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18.5rem')
            ->collapsedSidebarWidth('5rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Clusters/Almacen/Resources'), for: 'App\\Filament\\Clusters\\Almacen\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Compras/Resources'), for: 'App\\Filament\\Clusters\\Compras\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Configuraciones/Resources'), for: 'App\\Filament\\Clusters\\Configuraciones\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Global/Resources'), for: 'App\\Filament\\Clusters\\Global\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Inventario/Resources'), for: 'App\\Filament\\Clusters\\Inventario\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Reportes/Resources'), for: 'App\\Filament\\Clusters\\Reportes\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Sunat/Resources'), for: 'App\\Filament\\Clusters\\Sunat\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Ventas/Resources'), for: 'App\\Filament\\Clusters\\Ventas\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Escritorio::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.components.sucursal-topbar-selector')->render() . \Illuminate\Support\Facades\Blade::render('@livewire(\'alertas-bell\')'),
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => view('filament.components.login-back-button')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                fn (): string => view('filament.components.sidebar-size-switcher')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => view('filament.components.loading-overlay', $this->getPanelBranding())->render(),
            )
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                //
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureSucursalContext::class,
            ]);
    }

    protected function getPanelBranding(): array
    {
        $user = auth()->user();
        $empresa = $user?->empresa;
        $sucursalContext = app(SucursalContext::class);
        $activeSucursal = $sucursalContext->activeSucursal($user);
        $companyName = $empresa?->razon_social ?: config('app.name', 'Mini Market');

        return [
            'companyName' => $companyName,
            'companyShortName' => Str::limit($companyName, 34),
            'companyLogoUrl' => $this->resolveStorageUrl($empresa?->logo),
            'companyInitials' => $this->makeInitials($companyName),
            'sucursalName' => $activeSucursal?->nombre_sucursal,
            'isGlobalView' => ! $activeSucursal && $sucursalContext->canUseAllMode($user),
        ];
    }

    protected function resolveStorageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if (Str::startsWith($normalizedPath, 'storage/')) {
            return asset($normalizedPath);
        }

        return asset('storage/' . $normalizedPath);
    }

    protected function makeInitials(string $name): string
    {
        $initials = Str::of($name)
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $segment): string => Str::upper(Str::substr($segment, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'MM';
    }
}
