<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\HeroSlide;
use Database\Seeders\Concerns\CopiesEcommerceImages;
use Illuminate\Database\Seeder;

/**
 * Wires up the demo photos under public/assets/ecommerce/*.webp as the
 * home-shop storefront's hero carousel (see hero_slides table) and side
 * banners (CompanySetting::side_banner_1/2_path).
 */
class EcommerceBannerSeeder extends Seeder
{
    use CopiesEcommerceImages;

    public function run(): void
    {
        foreach (['banner-1.webp', 'banner-2.webp', 'banner-3.webp'] as $index => $file) {
            $imagePath = $this->copyEcommerceImage($file, 'hero-slides/'.$file);

            HeroSlide::firstOrCreate(
                ['image_path' => $imagePath],
                ['sort_order' => $index, 'status' => true]
            );
        }

        $company = CompanySetting::current();

        // Never overwrite banners a client already uploaded.
        if (! $company->side_banner_1_path && ! $company->side_banner_2_path) {
            $company->update([
                'side_banner_1_path' => $this->copyEcommerceImage('promo-1.webp', 'company/side-banner-1.webp'),
                'side_banner_2_path' => $this->copyEcommerceImage('promo-2.webp', 'company/side-banner-2.webp'),
            ]);
        }
    }
}
