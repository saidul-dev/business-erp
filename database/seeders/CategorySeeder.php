<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Concerns\CopiesEcommerceImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use CopiesEcommerceImages;

    /**
     * Category name => source folder under public/assets/ecommerce/categories
     * (a few folders were named with typos when the photos were dropped in,
     * so the on-disk folder doesn't always match the category name we want).
     */
    protected const CATEGORIES = [
        'Electronics' => 'Electronincs',
        'Groceries' => 'Groceries',
        'Fashion' => 'Fashion',
        'Home & Kitchen' => 'Home & Kitchen',
        'Beauty & Health' => 'Beauty & Health',
        'Sports & Outdoor' => 'Sports & Outdoor',
        'Stationery' => 'Stationary',
        'Toys & Baby' => 'Toys & Baby',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $name => $folder) {
            $slug = Str::slug($name);
            $sourceDir = 'categories/'.$folder.'/icon';
            $iconFile = collect(glob(public_path('assets/ecommerce/'.$sourceDir.'/*')))->first();

            $iconPath = $iconFile
                ? $this->copyEcommerceImage(
                    $sourceDir.'/'.basename($iconFile),
                    'categories/'.$slug.'-icon.'.pathinfo($iconFile, PATHINFO_EXTENSION)
                )
                : null;

            Category::firstOrCreate(['name' => $name], ['icon_path' => $iconPath]);
        }
    }
}
