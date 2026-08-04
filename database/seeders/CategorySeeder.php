<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Appetizers',
            'Soups & Salads',
            'Main Course',
            'Rice & Biryani',
            'Grill & BBQ',
            'Breads',
            'Desserts',
            'Beverages',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
