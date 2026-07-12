<?php

namespace App\View\Components;

use App\Models\Category;
use App\Models\CompanySetting;
use App\Support\Cart;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class WebsiteLayout extends Component
{
    public CompanySetting $company;

    public int $cartCount;

    public Collection $navCategories;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {
        $this->company = CompanySetting::current();
        $this->cartCount = $this->company->ecommerce_enabled ? (new Cart)->count() : 0;

        // Only the shop header (see partials/header-shop) needs these —
        // skip the query entirely when e-commerce is off.
        $this->navCategories = $this->company->ecommerce_enabled
            ? Category::whereNull('parent_id')->where('status', true)->orderBy('name')->get()
            : collect();
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.website');
    }
}
