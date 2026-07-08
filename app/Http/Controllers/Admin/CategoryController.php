<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
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

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create', ['parents' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $category = Category::create($validated);

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

        $category->update($validated);

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
        ]);
    }
}
