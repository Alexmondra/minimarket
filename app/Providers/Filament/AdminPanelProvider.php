<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureSucursalContext;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18.5rem')
            ->collapsedSidebarWidth('5rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Clusters/Almacen/Resources'), for: 'App\\Filament\\Clusters\\Almacen\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Compras/Resources'), for: 'App\\Filament\\Clusters\\Compras\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Configuraciones/Resources'), for: 'App\\Filament\\Clusters\\Configuraciones\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Inventario/Resources'), for: 'App\\Filament\\Clusters\\Inventario\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Reportes/Resources'), for: 'App\\Filament\\Clusters\\Reportes\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Sunat/Resources'), for: 'App\\Filament\\Clusters\\Sunat\\Resources')
            ->discoverResources(in: app_path('Filament/Clusters/Ventas/Resources'), for: 'App\\Filament\\Clusters\\Ventas\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => view('filament.components.sucursal-topbar-selector')->render(),
            )
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
}
