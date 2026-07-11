<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanySetting;

class WebsiteController extends Controller
{
    public function home()
    {
        $categories = Category::whereNull('parent_id')->where('status', true)
            ->withCount('products')
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        return view('website.home', ['company' => CompanySetting::current(), 'categories' => $categories]);
    }

    public function shop()
    {
        $company = CompanySetting::current();

        abort_unless($company->ecommerce_enabled, 404);

        return view('website.shop', ['company' => $company]);
    }
}
