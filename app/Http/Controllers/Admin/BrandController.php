<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller implements HasMiddleware
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
        $brands = Brand::withCount('products')->orderBy('name')->paginate(15);

        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        $brand = Brand::create(collect($validated)->except('logo')->all());

        return redirect()->route('brands.index')->with('success', "Brand \"{$brand->name}\" created.");
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', ['brand' => $brand]);
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_logo') && $brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update(collect($validated)->except(['logo', 'remove_logo'])->all());

        return redirect()->route('brands.index')->with('success', "Brand \"{$brand->name}\" updated.");
    }

    public function toggleStatus(Brand $brand)
    {
        $brand->update(['status' => ! $brand->status]);

        return back()->with('success', "Brand \"{$brand->name}\" is now ".($brand->status ? 'active' : 'inactive').'.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return back()->with('error', "Brand \"{$brand->name}\" still has products assigned — reassign them first.");
        }

        if ($brand->logo_path) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', "Brand \"{$brand->name}\" deleted.");
    }
}
