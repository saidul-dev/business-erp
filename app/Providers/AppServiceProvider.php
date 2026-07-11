<?php

namespace App\Providers;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        // Super Admin bypasses all permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // Lets the admin sidebar conditionally show/hide e-commerce-only
        // menu items (Online Orders, Marketing, ...) without every
        // controller having to fetch and pass CompanySetting itself.
        View::composer('layouts.app', function ($view) {
            $view->with('ecommerceEnabled', CompanySetting::current()->ecommerce_enabled);
        });
    }
}
