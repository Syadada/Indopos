<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // Auto-fallback to offline SQLite if PostgreSQL is unreachable (e.g. running on laptop sebelah without PG)
        if (config('database.default') === 'pgsql') {
            try {
                \Illuminate\Support\Facades\DB::connection('pgsql')->getPdo();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Config::set('database.default', 'sqlite');
                \Illuminate\Support\Facades\DB::purge('pgsql');
            }
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
