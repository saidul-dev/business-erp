<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

/**
 * Slides for the e-commerce homepage's hero carousel (see
 * website/home-shop.blade.php). Same permission gate as Website Settings —
 * this is homepage content, not the ecommerce_enabled toggle itself.
 */
class HeroSlideController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['index']),
            new Middleware('permission:settings.edit', only: ['create', 'store', 'edit', 'update', 'destroy', 'toggleStatus']),
        ];
    }

    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.hero-slides.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $validated['image_path'] = $request->file('image')->store('hero-slides', 'public');

        HeroSlide::create(collect($validated)->except('image')->all());

        return redirect()->route('hero-slides.index')->with('success', 'Hero slide added.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', ['slide' => $heroSlide]);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validated = $this->validated($request, required: false);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($heroSlide->image_path);
            $validated['image_path'] = $request->file('image')->store('hero-slides', 'public');
        }

        $heroSlide->update(collect($validated)->except('image')->all());

        return redirect()->route('hero-slides.index')->with('success', 'Hero slide updated.');
    }

    public function toggleStatus(HeroSlide $heroSlide)
    {
        $heroSlide->update(['status' => ! $heroSlide->status]);

        return back()->with('success', 'Hero slide is now '.($heroSlide->status ? 'active' : 'inactive').'.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        Storage::disk('public')->delete($heroSlide->image_path);
        $heroSlide->delete();

        return redirect()->route('hero-slides.index')->with('success', 'Hero slide deleted.');
    }

    protected function validated(Request $request, bool $required = true): array
    {
        $validated = $request->validate([
            'image' => [$required ? 'required' : 'nullable', 'image', 'max:4096'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        // Checkboxes omit the field entirely when unchecked, so read it directly.
        $validated['status'] = $request->boolean('status');

        return $validated;
    }
}
