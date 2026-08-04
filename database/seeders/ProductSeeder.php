<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProductSeeder extends Seeder
{
    /**
     * Demo menu catalog, grouped by CategorySeeder::run()'s categories.
     * 'brand' is only set on packaged/branded beverages — a cooked dish has
     * no brand. No image_path here: there's no photo library for a menu
     * (the old ecommerce demo photos were retail product shots), so these
     * seed with a blank image and a client uploads real menu photos later.
     */
    protected const MENU = [
        'Appetizers' => [
            ['name' => 'Chicken Wings', 'unit' => 'Plate', 'cost' => 180, 'price' => 250],
            ['name' => 'Vegetable Samosa', 'unit' => 'Plate', 'cost' => 80, 'price' => 120],
            ['name' => 'Spring Rolls', 'unit' => 'Plate', 'cost' => 100, 'price' => 150],
            ['name' => 'Chicken Tikka', 'unit' => 'Plate', 'cost' => 190, 'price' => 280],
        ],
        'Soups & Salads' => [
            ['name' => 'Chicken Corn Soup', 'unit' => 'Cup', 'cost' => 90, 'price' => 150],
            ['name' => 'Thai Salad', 'unit' => 'Plate', 'cost' => 140, 'price' => 220],
            ['name' => 'Caesar Salad', 'unit' => 'Plate', 'cost' => 160, 'price' => 250],
        ],
        'Main Course' => [
            ['name' => 'Beef Bhuna', 'unit' => 'Plate', 'cost' => 260, 'price' => 380],
            ['name' => 'Mutton Rezala', 'unit' => 'Plate', 'cost' => 320, 'price' => 450],
            ['name' => 'Vegetable Korma', 'unit' => 'Plate', 'cost' => 140, 'price' => 220],
        ],
        'Rice & Biryani' => [
            ['name' => 'Mutton Biryani', 'unit' => 'Plate', 'cost' => 260, 'price' => 380],
            ['name' => 'Vegetable Fried Rice', 'unit' => 'Plate', 'cost' => 120, 'price' => 200],
            ['name' => 'Plain Rice', 'unit' => 'Plate', 'cost' => 40, 'price' => 80],
        ],
        'Grill & BBQ' => [
            ['name' => 'Chicken Seekh Kebab', 'unit' => 'Plate', 'cost' => 200, 'price' => 300],
            ['name' => 'Beef Steak', 'unit' => 'Plate', 'cost' => 380, 'price' => 550],
            ['name' => 'BBQ Chicken Platter', 'unit' => 'Plate', 'cost' => 300, 'price' => 450],
        ],
        'Breads' => [
            ['name' => 'Butter Naan', 'unit' => 'Pcs', 'cost' => 20, 'price' => 40],
            ['name' => 'Garlic Naan', 'unit' => 'Pcs', 'cost' => 25, 'price' => 50],
            ['name' => 'Paratha', 'unit' => 'Pcs', 'cost' => 12, 'price' => 25],
            ['name' => 'Tandoori Roti', 'unit' => 'Pcs', 'cost' => 10, 'price' => 20],
        ],
        'Desserts' => [
            ['name' => 'Chocolate Brownie', 'unit' => 'Pcs', 'cost' => 90, 'price' => 150],
            ['name' => 'Firni', 'unit' => 'Cup', 'cost' => 45, 'price' => 80],
            ['name' => 'Rasmalai', 'unit' => 'Plate', 'cost' => 70, 'price' => 120],
            ['name' => 'Ice Cream Sundae', 'unit' => 'Cup', 'cost' => 100, 'price' => 180],
        ],
        'Beverages' => [
            ['name' => 'Coca-Cola (Can)', 'unit' => 'Btl', 'cost' => 25, 'price' => 40, 'brand' => 'Coca-Cola'],
            ['name' => 'Fresh Lime Soda', 'unit' => 'Cup', 'cost' => 30, 'price' => 60],
            ['name' => 'Mango Lassi', 'unit' => 'Cup', 'cost' => 50, 'price' => 90],
            ['name' => 'Cold Coffee', 'unit' => 'Cup', 'cost' => 70, 'price' => 120],
            ['name' => 'Mineral Water', 'unit' => 'Btl', 'cost' => 12, 'price' => 20, 'brand' => 'Fresh'],
        ],
    ];

    protected const CATEGORY_CODES = [
        'Appetizers' => 'APP',
        'Soups & Salads' => 'SAL',
        'Main Course' => 'MC',
        'Rice & Biryani' => 'RB',
        'Grill & BBQ' => 'GRL',
        'Breads' => 'BRD',
        'Desserts' => 'DES',
        'Beverages' => 'BEV',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');
        $brands = Brand::pluck('id', 'name');
        $units = Unit::pluck('id', 'short_name');

        $barcodeSeq = 1;

        foreach (self::MENU as $categoryName => $items) {
            $categoryId = $categories[$categoryName];
            $code = self::CATEGORY_CODES[$categoryName];

            foreach ($items as $index => $item) {
                $unitId = $units[$item['unit']];
                $sku = sprintf('%s-%03d', $code, $index + 1);

                Product::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $item['name'],
                        'barcode' => sprintf('8801%08d', $barcodeSeq++),
                        'category_id' => $categoryId,
                        'brand_id' => isset($item['brand']) ? $brands[$item['brand']] : null,
                        'stock_unit_id' => $unitId,
                        'purchase_unit_id' => $unitId,
                        'purchase_unit_conversion' => 1,
                        'sale_unit_id' => $unitId,
                        'sale_unit_conversion' => 1,
                        'estimated_cost' => $item['cost'],
                        'selling_price' => $item['price'],
                        'reorder_level' => 10,
                        'status' => true,
                    ]
                );
            }
        }

        $this->seedVariantProducts($categories, $units, $barcodeSeq);
    }

    /**
     * A couple of has_variants=true demo products, so the Attribute /
     * Product Variant feature has real data to show on first login —
     * Chicken Biryani by Portion Size, Chicken Curry by Spice Level.
     */
    protected function seedVariantProducts(Collection $categories, Collection $units, int $barcodeSeq): void
    {
        $portionAttributeId = Attribute::where('name', 'Portion Size')->value('id');
        $spiceAttributeId = Attribute::where('name', 'Spice Level')->value('id');
        $portionValueIds = AttributeValue::where('attribute_id', $portionAttributeId)->pluck('id', 'value');
        $spiceValueIds = AttributeValue::where('attribute_id', $spiceAttributeId)->pluck('id', 'value');

        $plateUnitId = $units['Plate'];

        $biryani = Product::firstOrCreate(
            ['sku' => 'RB-004'],
            [
                'name' => 'Chicken Biryani',
                'category_id' => $categories['Rice & Biryani'],
                'stock_unit_id' => $plateUnitId,
                'purchase_unit_id' => $plateUnitId,
                'purchase_unit_conversion' => 1,
                'sale_unit_id' => $plateUnitId,
                'sale_unit_conversion' => 1,
                'estimated_cost' => 200,
                'selling_price' => 300,
                'reorder_level' => 10,
                'status' => true,
                'has_variants' => true,
            ]
        );

        $portionPricing = ['Regular' => 300, 'Large' => 420, 'Family' => 750];

        foreach ($portionPricing as $portion => $price) {
            $variant = ProductVariant::firstOrCreate(
                ['sku' => 'RB-004-'.strtoupper(substr($portion, 0, 3))],
                [
                    'product_id' => $biryani->id,
                    'selling_price' => $price,
                    'estimated_cost' => round($price * 0.65),
                    'status' => true,
                ]
            );

            $variant->attributeValues()->syncWithoutDetaching([
                $portionValueIds[$portion] => ['attribute_id' => $portionAttributeId],
            ]);
        }

        $curry = Product::firstOrCreate(
            ['sku' => 'MC-004'],
            [
                'name' => 'Chicken Curry',
                'category_id' => $categories['Main Course'],
                'stock_unit_id' => $plateUnitId,
                'purchase_unit_id' => $plateUnitId,
                'purchase_unit_conversion' => 1,
                'sale_unit_id' => $plateUnitId,
                'sale_unit_conversion' => 1,
                'estimated_cost' => 190,
                'selling_price' => 280,
                'reorder_level' => 10,
                'status' => true,
                'has_variants' => true,
            ]
        );

        foreach (['Mild', 'Medium', 'Hot'] as $spice) {
            $variant = ProductVariant::firstOrCreate(
                ['sku' => 'MC-004-'.strtoupper(substr($spice, 0, 3))],
                [
                    'product_id' => $curry->id,
                    'selling_price' => 280,
                    'estimated_cost' => 190,
                    'status' => true,
                ]
            );

            $variant->attributeValues()->syncWithoutDetaching([
                $spiceValueIds[$spice] => ['attribute_id' => $spiceAttributeId],
            ]);
        }
    }
}
