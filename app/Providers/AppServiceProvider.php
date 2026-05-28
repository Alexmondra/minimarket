<?php

namespace App\Providers;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registramos Blade Heroicons
        $this->app->register(BladeHeroiconsServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar Filament para cargar estilos personalizados
        // Esto asegura que los estilos de Tailwind se compilen correctamente
        // para las vistas personalizadas de Filament
    }
}
