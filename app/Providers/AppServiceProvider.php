<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Super Admin & Owner bypass for all permissions
        Gate::before(function ($user, $ability) {
            return ($user->hasRole('Super Admin') || $user->hasRole('Owner')) ? true : null;
        });
        // Force HTTPS if loaded behind ngrok or HTTPS proxy to fix broken asset loading
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // Global app settings sharing
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('app_settings') || \Illuminate\Support\Facades\Schema::hasTable('app_settings_tb')) {
                $settings = \Illuminate\Support\Facades\DB::table('app_settings')->pluck('value', 'key')->all();
                $appName = $settings['app_name'] ?? 'Lumen ERP';
                $appLogo = isset($settings['app_logo']) && $settings['app_logo'] 
                    ? asset('storage/' . $settings['app_logo']) 
                    : asset('gambar/logo.png');
                view()->share('appName', $appName);
                view()->share('appLogo', $appLogo);
            } else {
                view()->share('appName', 'Lumen ERP');
                view()->share('appLogo', asset('gambar/logo.png'));
            }
        } catch (\Throwable $e) {
            view()->share('appName', 'Lumen ERP');
            view()->share('appLogo', asset('gambar/logo.png'));
        }
    }
}
