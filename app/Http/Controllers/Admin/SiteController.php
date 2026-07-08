<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class SiteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sites.view', only: ['index']),
            new Middleware('permission:sites.create', only: ['create', 'store']),
            new Middleware('permission:sites.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:sites.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $sites = Site::withCount('users')->orderBy('name')->paginate(15);

        return view('admin.sites.index', compact('sites'));
    }

    public function create()
    {
        return view('admin.sites.create', ['types' => Site::TYPES]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $site = Site::create($validated);

        return redirect()->route('sites.index')->with('success', "Site \"{$site->name}\" created.");
    }

    public function edit(Site $site)
    {
        return view('admin.sites.edit', ['site' => $site, 'types' => Site::TYPES]);
    }

    public function update(Request $request, Site $site)
    {
        $validated = $this->validated($request, $site);

        $site->update($validated);

        return redirect()->route('sites.index')->with('success', "Site \"{$site->name}\" updated.");
    }

    public function toggleStatus(Site $site)
    {
        $site->update(['status' => ! $site->status]);

        return back()->with('success', "Site \"{$site->name}\" is now ".($site->status ? 'active' : 'inactive').'.');
    }

    public function destroy(Site $site)
    {
        if ($site->users()->exists()) {
            return back()->with('error', "Site \"{$site->name}\" still has users assigned — reassign them first.");
        }

        $site->delete();

        return redirect()->route('sites.index')->with('success', "Site \"{$site->name}\" deleted.");
    }

    protected function validated(Request $request, ?Site $site = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('sites', 'code')->ignore($site?->id)],
            'type' => ['required', 'string', Rule::in(Site::TYPES)],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
        ]);
    }
}
