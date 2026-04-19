<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\User;
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
        Gate::define('admin-access', function (User $user) {
            return $user->isAdmin();
        });

        // Share settings with all views
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $settingsFile = 'settings.json';
            $settings = [];
            if (\Illuminate\Support\Facades\Storage::disk('local')->exists($settingsFile)) {
                $settings = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($settingsFile), true);
            }
            $view->with('systemSettings', $settings);
        });
    }
}
