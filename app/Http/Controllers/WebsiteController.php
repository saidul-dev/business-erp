<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\ContactMessage;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * The root domain is the SaaS product's own marketing page (pricing,
     * register CTA) — not any one tenant's restaurant site. A per-tenant
     * public storefront (the old home() behavior, still in
     * website/home.blade.php) needs its own tenant context to show safely
     * — Product/Category/Branch have no meaning at the root with no tenant
     * signed in, and would leak across tenants if queried unscoped here.
     * Revisit once per-tenant public sites get real subdomain routing.
     */
    public function home()
    {
        return view('website.saas-home', [
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function about()
    {
        return view('website.about', [
            'company' => CompanySetting::current(),
            'branches' => Branch::where('status', true)->orderBy('name')->get(),
            'stats' => $this->catalogStats(),
        ]);
    }

    /**
     * Real figures for the hero/about "at a glance" stat rows — never
     * invented copy, so it stays honest as the catalog grows (or starts
     * empty).
     */
    private function catalogStats(): array
    {
        return [
            'products' => Product::where('status', true)->count(),
            'categories' => Category::whereNull('parent_id')->where('status', true)
                ->whereHas('products', fn ($q) => $q->where('status', true))
                ->count(),
            'branches' => Branch::where('status', true)->count(),
        ];
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
        return view('website.contact', [
            'company' => CompanySetting::current(),
            'branches' => Branch::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        return back()->with('success', "Thanks, {$validated['name']} — we've received your message and will get back to you soon.");
    }
}
