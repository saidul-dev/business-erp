<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignPage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Single-product ad-landing pages (see App\Http\Controllers\CampaignController
 * for the public-facing side, rendered at /campaign/{slug}). Same permission
 * gate as Hero Slides — this is marketing content, not the ecommerce_enabled
 * toggle itself.
 */
class CampaignPageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['index']),
            new Middleware('permission:settings.edit', only: ['create', 'store', 'edit', 'update', 'destroy']),
        ];
    }

    public function index()
    {
        $pages = CampaignPage::with('product')->orderByDesc('id')->get();

        return view('admin.campaign-pages.index', compact('pages'));
    }

    public function create()
    {
        $products = Product::where('status', true)->orderBy('name')->get();

        return view('admin.campaign-pages.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        if ($request->hasFile('banner_image')) {
            $validated['banner_image_path'] = $request->file('banner_image')->store('campaign-pages', 'public');
        }

        CampaignPage::create(collect($validated)->except('banner_image')->all());

        return redirect()->route('campaign-pages.index')->with('success', 'Campaign page created.');
    }

    public function edit(CampaignPage $campaignPage)
    {
        $products = Product::where('status', true)->orderBy('name')->get();

        return view('admin.campaign-pages.edit', ['page' => $campaignPage, 'products' => $products]);
    }

    public function update(Request $request, CampaignPage $campaignPage)
    {
        $validated = $this->validated($request, $campaignPage);

        if ($request->hasFile('banner_image')) {
            if ($campaignPage->banner_image_path) {
                Storage::disk('public')->delete($campaignPage->banner_image_path);
            }
            $validated['banner_image_path'] = $request->file('banner_image')->store('campaign-pages', 'public');
        }

        $campaignPage->update(collect($validated)->except('banner_image')->all());

        return redirect()->route('campaign-pages.index')->with('success', 'Campaign page updated.');
    }

    public function destroy(CampaignPage $campaignPage)
    {
        if ($campaignPage->banner_image_path) {
            Storage::disk('public')->delete($campaignPage->banner_image_path);
        }
        $campaignPage->delete();

        return redirect()->route('campaign-pages.index')->with('success', 'Campaign page deleted.');
    }

    public function toggleStatus(CampaignPage $campaignPage)
    {
        $campaignPage->update(['status' => ! $campaignPage->status]);

        return back()->with('success', 'Campaign page is now '.($campaignPage->status ? 'active' : 'inactive').'.');
    }

    protected function validated(Request $request, ?CampaignPage $campaignPage = null): array
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('campaign_pages', 'slug')->ignore($campaignPage)],
            'headline' => ['required', 'string', 'max:255'],
            'subheadline' => ['nullable', 'string', 'max:255'],
            'features' => ['nullable', 'string'],
            // No banner is required — CampaignPage::banner_image_url falls
            // back to the product's own image when this is left empty.
            'banner_image' => ['nullable', 'image', 'max:4096'],
            'status' => ['nullable', 'boolean'],
        ]);

        // Checkboxes omit the field entirely when unchecked, so read it directly.
        $validated['status'] = $request->boolean('status');

        return $validated;
    }
}
