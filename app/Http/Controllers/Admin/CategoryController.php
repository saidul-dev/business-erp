<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller implements HasMiddleware
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
        $categories = Category::with('parent')->withCount(['children', 'products'])->orderBy('name')->paginate(15);
        $parents = Category::orderBy('name')->get();

        return view('admin.categories.index', compact('categories', 'parents'));
    }

    public function create()
    {
        return view('admin.categories.create', ['parents' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('icon')) {
            $validated['icon_path'] = $request->file('icon')->store('categories', 'public');
        }

        $category = Category::create(collect($validated)->except('icon')->all());

        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" created.");
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => Category::where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $this->validated($request, $category);

        if ($request->boolean('remove_icon') && $category->icon_path) {
            Storage::disk('public')->delete($category->icon_path);
            $validated['icon_path'] = null;
        }

        if ($request->hasFile('icon')) {
            if ($category->icon_path) {
                Storage::disk('public')->delete($category->icon_path);
            }
            $validated['icon_path'] = $request->file('icon')->store('categories', 'public');
        }

        $category->update(collect($validated)->except(['icon', 'remove_icon'])->all());

        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" updated.");
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['status' => ! $category->status]);

        return back()->with('success', "Category \"{$category->name}\" is now ".($category->status ? 'active' : 'inactive').'.');
    }

    public function destroy(Category $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', "Category \"{$category->name}\" still has sub-categories — remove or reassign them first.");
        }

        if ($category->products()->exists()) {
            return back()->with('error', "Category \"{$category->name}\" still has products assigned — reassign them first.");
        }

        if ($category->icon_path) {
            Storage::disk('public')->delete($category->icon_path);
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', "Category \"{$category->name}\" deleted.");
    }

    protected function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                Rule::notIn([$category?->id]),
            ],
            'icon' => ['nullable', 'image', 'max:1024'],
            'remove_icon' => ['nullable', 'boolean'],
        ]);
    }
}
