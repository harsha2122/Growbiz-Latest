<?php

namespace App\Providers;

use Botble\Base\Supports\DashboardMenu;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()->removeItem([
                'cms-core-system', // Platform Administration
                'cms-core-plugins', // Plugins
                'cms-plugins-location', // Locations
                'cms-core-theme', // Themes (inside Appearance)
            ]);
        });
    }
}
