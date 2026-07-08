<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class CurrentSiteController extends Controller
{
    /**
     * Forced picker shown when a user has more than one assigned site and
     * hasn't chosen a Current Site yet (see SetCurrentSite middleware).
     */
    public function select()
    {
        $user = auth()->user();

        $sites = $user->seesAllSites()
            ? Site::where('status', true)->orderBy('name')->get()
            : $user->sites()->where('status', true)->orderBy('name')->get();

        return view('admin.select-site', compact('sites'));
    }

    /**
     * Switch the Current Site — used by both the forced picker and the
     * topbar Site Selector. Only Owner/Super Admin may pick "All Sites" (null).
     */
    public function switch(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'site_id' => ['nullable', 'exists:sites,id'],
        ]);

        $siteId = $validated['site_id'] ?? null;

        if ($siteId === null && ! $user->seesAllSites()) {
            abort(403, 'You must select a specific site.');
        }

        if ($siteId !== null && ! $user->seesAllSites() && ! $user->sites()->where('sites.id', $siteId)->exists()) {
            abort(403, 'You are not assigned to that site.');
        }

        $user->update(['current_site_id' => $siteId]);
        session(['current_site_id' => $siteId]);

        $message = $siteId ? 'Switched to '.Site::find($siteId)->name.'.' : 'Now viewing All Sites.';

        return redirect()->intended(route('dashboard'))->with('success', $message);
    }
}
