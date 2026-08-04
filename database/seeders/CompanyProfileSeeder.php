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
    public function run(?int $tenantId = null): void
    {
        $company = CompanySetting::current($tenantId);

        // Never overwrite text a client already wrote in Admin > Settings > Website.
        if ($company->about_text || $company->mission_text || $company->vision_text || $company->values_text) {
            return;
        }

        $company->update([
            'about_text' => 'We are a growing restaurant serving customers across Bangladesh with fresh, '
                .'quality food — dine-in, takeaway, and home delivery. From a single outlet to a network of '
                .'branches and a central kitchen, we focus on consistent taste, fair pricing, and fast service.',
            'mission_text' => 'To make every meal simple and dependable by serving the right dish, at the right '
                .'price, always fresh — whether a customer dines in, takes away, or orders delivery.',
            'vision_text' => 'To become the most trusted restaurant brand in Bangladesh, known for taste, '
                .'hygiene, and putting customers first in every order.',
            'values_text' => 'Hygiene, Customer First, Quality Assurance, Continuous Improvement',
        ]);
    }
}
