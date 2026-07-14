<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

/**
 * Demo copy for the storefront's About / Mission-Vision-Values section (see
 * website/about.blade.php and website/partials/mission-vision-values.blade.php)
 * — otherwise those sections stay empty on a fresh install.
 */
class CompanyProfileSeeder extends Seeder
{
    public function run(): void
    {
        $company = CompanySetting::current();

        // Never overwrite text a client already wrote in Admin > Settings > Website.
        if ($company->about_text || $company->mission_text || $company->vision_text || $company->values_text) {
            return;
        }

        $company->update([
            'about_text' => 'We are a growing retail business serving customers across Bangladesh with '
                .'quality electronics, groceries, fashion, and home essentials — both in-store and online. '
                .'From a single head office to a network of warehouses and shops, we focus on reliable stock, '
                .'fair pricing, and fast delivery.',
            'mission_text' => 'To make everyday shopping simple and dependable by offering the right products, '
                .'at the right price, always in stock — whether a customer walks into our shop or orders from '
                .'our website.',
            'vision_text' => 'To become the most trusted retail and e-commerce brand in Bangladesh, known for '
                .'quality, transparency, and putting customers first in every transaction.',
            'values_text' => 'Integrity, Customer First, Quality Assurance, Continuous Improvement',
        ]);
    }
}
