<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the authenticated user's Current Site for this request.
 *
 * - 1 assigned site  -> auto-selected, no picker shown.
 * - >1 assigned sites -> must have a valid selection in session, otherwise
 *   redirected to the site picker (see ERP Architecture Guideline v1.0).
 * - 0 assigned sites  -> Owner/Super Admin default to "All Sites" (null);
 *   anyone else simply has no site context yet (nothing to enforce until
 *   a site-aware module needs one).
 */
class SetCurrentSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($request->routeIs('sites.select', 'sites.switch', 'logout')) {
            return $next($request);
        }

        $assignedSiteIds = $user->sites()->pluck('sites.id');

        if ($assignedSiteIds->count() === 1) {
            $onlySiteId = $assignedSiteIds->first();

            if ($user->current_site_id !== $onlySiteId) {
                $user->update(['current_site_id' => $onlySiteId]);
            }

            session(['current_site_id' => $onlySiteId]);
        } elseif ($assignedSiteIds->count() > 1) {
            $current = session('current_site_id', $user->current_site_id);

            if (! $current || ! $assignedSiteIds->contains($current)) {
                return redirect()->route('sites.select');
            }

            session(['current_site_id' => $current]);
        } elseif ($user->seesAllSites()) {
            session(['current_site_id' => session('current_site_id', $user->current_site_id)]);
        }

        return $next($request);
    }
}
