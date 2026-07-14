<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiteHealthChecker;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SiteHealthController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view'),
        ];
    }

    /**
     * Recompute the health score on demand — the daily schedule keeps it
     * fresh automatically once the server's cron is wired up, but this lets
     * an admin get a current reading right away (e.g. right after fixing an
     * issue it flagged).
     */
    public function refresh(SiteHealthChecker $checker)
    {
        $checker->run();

        return back()->with('success', __('Site health recalculated.'));
    }
}
