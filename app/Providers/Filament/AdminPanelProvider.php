<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Escritorio;
use App\Http\Middleware\EnsureSucursalContext;
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
            ->navigationGroups([
                'Catálogo Global',
                'Compras',
                'Almacén',
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
}
