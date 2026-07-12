<?php

namespace App\View\Components;

use App\Models\CompanySetting;
use App\Support\Cart;
use Illuminate\View\Component;
use Illuminate\View\View;

class WebsiteLayout extends Component
{
    public CompanySetting $company;

    public int $cartCount;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {
        $this->company = CompanySetting::current();
        $this->cartCount = $this->company->ecommerce_enabled ? (new Cart)->count() : 0;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.website');
    }
}
