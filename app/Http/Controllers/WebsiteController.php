<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\Party;
use App\Models\Product;
use App\Models\Site;

class WebsiteController extends Controller
{
    public function home()
    {
        $categories = Category::whereNull('parent_id')->where('status', true)
            ->withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        $products = Product::with('stockUnit')
            ->where('status', true)->where('has_variants', false)
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $branches = Site::where('status', true)->orderBy('name')->get();

        // Real figures for the hero stat row — never invented copy, so it
        // stays honest as the catalog grows (or starts empty).
        $heroStats = [
            'products' => Product::where('status', true)->count(),
            'categories' => $categories->count(),
            'branches' => $branches->count(),
        ];

        // Business-scale stats further down the page. Clients/Suppliers are
        // real Party data; Employees has no HR module yet (see README —
        // HRM is a later phase), so it's a static placeholder until that
        // exists, per an explicit decision to not fake a "real" data source.
        $businessStats = [
            'employees' => 25,
            'clients' => Party::where('is_customer', true)->where('status', true)->count(),
            'suppliers' => Party::where('is_supplier', true)->where('status', true)->count(),
        ];

        return view('website.home', [
            'company' => CompanySetting::current(),
            'categories' => $categories,
            'products' => $products,
            'branches' => $branches,
            'heroStats' => $heroStats,
            'businessStats' => $businessStats,
        ]);
    }

    public function about()
    {
        return view('website.about', ['company' => CompanySetting::current()]);
    }

    /**
     * Static placeholder — no gallery/press content model exists yet
     * (explicit decision: ship the page design now, wire real content
     * later).
     */
    public function media()
    {
        return view('website.media', ['company' => CompanySetting::current()]);
    }

    /**
     * Static "we're hiring" page — no job-posting management exists yet
     * (explicit decision: a real listings feature is a separate, later
     * piece of work).
     */
    public function career()
    {
        return view('website.career', ['company' => CompanySetting::current()]);
    }

    public function contact()
    {
        return view('website.contact', ['company' => CompanySetting::current()]);
    }

    public function shop()
    {
        $company = CompanySetting::current();

        abort_unless($company->ecommerce_enabled, 404);

        return view('website.shop', ['company' => $company]);
    }
}
