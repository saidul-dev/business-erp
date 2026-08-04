<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free Trial',
                'slug' => 'free-trial',
                'description' => '30-day trial, 1 branch — evaluate before you pay.',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'max_branches' => 1,
                'trial_days' => 30,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'For a single restaurant with up to 3 branches.',
                'price' => 2500,
                'billing_cycle' => 'monthly',
                'max_branches' => 3,
                'trial_days' => 0,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing multi-branch chains, up to 10 branches.',
                'price' => 6000,
                'billing_cycle' => 'monthly',
                'max_branches' => 10,
                'trial_days' => 0,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited branches for large franchises — contact us.',
                'price' => 15000,
                'billing_cycle' => 'monthly',
                'max_branches' => 999,
                'trial_days' => 0,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
