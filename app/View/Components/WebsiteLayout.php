<?php

namespace App\View\Components;

use App\Models\CompanySetting;
use Illuminate\View\Component;
use Illuminate\View\View;

class WebsiteLayout extends Component
{
    public CompanySetting $company;

    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
    ) {
        $this->company = CompanySetting::current();
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.website');
    }
}
