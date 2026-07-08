<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;

class WebsiteController extends Controller
{
    public function home()
    {
        return view('website.home', ['company' => CompanySetting::current()]);
    }

    public function shop()
    {
        $company = CompanySetting::current();

        abort_unless($company->ecommerce_enabled, 404);

        return view('website.shop', ['company' => $company]);
    }
}
