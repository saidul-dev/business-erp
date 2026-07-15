<?php

namespace App\Providers;

use App\Models\CompanySetting;
use App\Models\Sale;
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

        // Lets the admin sidebar (and any admin page that needs to
        // show/hide e-commerce-only UI, e.g. the Sales list's Channel
        // column) read this without every controller having to fetch and
        // pass CompanySetting itself. Blade component slots render in
        // their own data scope, so the layout view alone isn't enough —
        // each page view that needs it has to be listed here too.
        View::composer(
            ['layouts.app', 'admin.sales.index', 'admin.products.create', 'admin.products.edit'],
            function ($view) {
                $view->with('ecommerceEnabled', CompanySetting::current()->ecommerce_enabled);
            }
        );

        // Sidebar badge counting online orders still awaiting fulfillment,
        // so staff notice new storefront orders without opening the Sales menu.
        View::composer('layouts.app', function ($view) {
            $view->with('pendingOnlineOrdersCount', Sale::query()
                ->where('channel', 'online')
                ->where('status', 'pending')
                ->count());
        });
    }
}
