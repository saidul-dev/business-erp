<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UnitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.view', only: ['index']),
            new Middleware('permission:inventory.create', only: ['create', 'store']),
            new Middleware('permission:inventory.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:inventory.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $units = Unit::withCount('products')->orderBy('name')->paginate(15);

        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $unit = Unit::create($this->validated($request));

        return redirect()->route('units.index')->with('success', "Unit \"{$unit->name}\" created.");
    }

    public function edit(Unit $unit)
    {
        return view('admin.units.edit', ['unit' => $unit]);
    }

    public function update(Request $request, Unit $unit)
    {
        $unit->update($this->validated($request));

        return redirect()->route('units.index')->with('success', "Unit \"{$unit->name}\" updated.");
    }

    public function toggleStatus(Unit $unit)
    {
        $unit->update(['status' => ! $unit->status]);

        return back()->with('success', "Unit \"{$unit->name}\" is now ".($unit->status ? 'active' : 'inactive').'.');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->exists()) {
            return back()->with('error', "Unit \"{$unit->name}\" still has products assigned — reassign them first.");
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', "Unit \"{$unit->name}\" deleted.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:20'],
        ]);
    }
}
