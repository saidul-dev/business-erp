<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trial expired, no active paid period, and not platform staff (tenant_id
 * null) — locked out of the admin panel until they pick a package. Checked
 * after auth (a guest has nothing to gate) but before current-branch (no
 * point resolving a branch for a tenant that can't get past billing anyway).
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant_id) {
            return $next($request);
        }

        if ($request->routeIs('billing.*', 'logout')) {
            return $next($request);
        }

        if (! $user->tenant->hasActiveAccess()) {
            return redirect()->route('billing.index')
                ->with('error', 'Your trial or subscription has ended — pick a package to continue.');
        }

        return $next($request);
    }
}
