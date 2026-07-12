<?php

namespace App\Http\Middleware;

use App\Models\CompanySetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every storefront route (catalog, cart, checkout, tracking) 404s until
 * Admin > Settings > E-commerce turns the store on — same gate the
 * original WebsiteController::shop() placeholder already used.
 */
class EnsureEcommerceEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(CompanySetting::current()->ecommerce_enabled, 404);

        return $next($request);
    }
}
