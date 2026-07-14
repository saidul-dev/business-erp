<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use Database\Seeders\Concerns\CopiesEcommerceImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    use CopiesEcommerceImages;

    /**
     * Storefront demo catalog, built from the photos under
     * public/assets/ecommerce/categories/{folder}/*.webp — one product per
     * photo, so every seeded category has real product images. Category
     * name here must match CategorySeeder::CATEGORIES; 'folder' is the
     * on-disk directory (a couple were named with typos).
     */
    protected const IMAGE_CATEGORIES = [
        'Beauty & Health' => ['folder' => 'Beauty & Health', 'code' => 'BH'],
        'Electronics' => ['folder' => 'Electronincs', 'code' => 'ELEC'],
        'Fashion' => ['folder' => 'Fashion', 'code' => 'FAS'],
        'Groceries' => ['folder' => 'Groceries', 'code' => 'GRO'],
        'Home & Kitchen' => ['folder' => 'Home & Kitchen', 'code' => 'HK'],
        'Sports & Outdoor' => ['folder' => 'Sports & Outdoor', 'code' => 'SPO'],
        'Stationery' => ['folder' => 'Stationary', 'code' => 'STA'],
        'Toys & Baby' => ['folder' => 'Toys & Baby', 'code' => 'TOY'],
    ];

    /**
     * Products generated per category photo. A few photos (cotton-t-shirt,
     * notebook, ballpen-set) are used as the image for an existing
     * hand-written product below instead of spawning a near-duplicate.
     */
    protected const IMAGE_PRODUCTS = [
        'Beauty & Health' => [
            ['name' => 'Moisturizing Cream', 'file' => 'Moisturizer.webp', 'price' => 450],
            ['name' => 'Facewash', 'file' => 'facewash.webp', 'price' => 280],
            ['name' => 'Perfume', 'file' => 'perfume.webp', 'price' => 1200],
            ['name' => 'Shampoo', 'file' => 'shampoo.webp', 'price' => 350],
            ['name' => 'Sunscreen Lotion', 'file' => 'sunscreen.webp', 'price' => 500],
        ],
        'Electronics' => [
            ['name' => 'Bluetooth Speaker', 'file' => 'bluetooth-speaker.webp', 'price' => 2500],
            ['name' => 'Laptop', 'file' => 'laptop.webp', 'price' => 55000],
            ['name' => 'Mirrorless Camera', 'file' => 'mirrorless-camera.webp', 'price' => 68000],
            ['name' => 'Smartphone', 'file' => 'smartphone.webp', 'price' => 22000],
            ['name' => 'Smartwatch', 'file' => 'smartwatch.webp', 'price' => 4500],
            ['name' => 'Wireless Headphone', 'file' => 'wireless-headphone.webp', 'price' => 3200],
        ],
        'Fashion' => [
            ['name' => 'Casual Sneakers', 'file' => 'casual-sneakers.webp', 'price' => 1800],
            ['name' => 'Classic Watch', 'file' => 'classic-watch.webp', 'price' => 3500],
            ['name' => 'Leather Handbag', 'file' => 'leather-handbag.webp', 'price' => 2200],
            ['name' => 'Polarized Sunglasses', 'file' => 'polarized_sunglasses.webp', 'price' => 950],
            ['name' => 'Slim Fit Denim Jeans', 'file' => 'slimt-fit-denim-jeans.webp', 'price' => 1600],
        ],
        'Groceries' => [
            ['name' => 'Apple', 'file' => 'apple.webp', 'price' => 220, 'unit' => 'Kg'],
            ['name' => 'Capsicum', 'file' => 'capsicum.webp', 'price' => 120, 'unit' => 'Kg'],
            ['name' => 'Eggs (Dozen)', 'file' => 'eggs.webp', 'price' => 150, 'unit' => 'Dz'],
            ['name' => 'Mixed Fruits Basket', 'file' => 'fruits.webp', 'price' => 350, 'unit' => 'Pack'],
            ['name' => 'Milk', 'file' => 'milk.webp', 'price' => 90, 'unit' => 'L'],
            ['name' => 'Cooking Oil', 'file' => 'oil.webp', 'price' => 190, 'unit' => 'L'],
            ['name' => 'Rice', 'file' => 'rice.webp', 'price' => 75, 'unit' => 'Kg'],
            ['name' => 'Fresh Vegetables Pack', 'file' => 'vegetables.webp', 'price' => 140, 'unit' => 'Pack'],
        ],
        'Home & Kitchen' => [
            ['name' => 'Blender & Juicer', 'file' => 'blender-juicer.webp', 'price' => 3200],
            ['name' => 'Decorative Table Lamp', 'file' => 'decorative-table-lamp.webp', 'price' => 1800],
            ['name' => 'Dinnerware Set', 'file' => 'dinnerware-set.webp', 'price' => 2600],
            ['name' => 'Electric Kettle', 'file' => 'electric-kettle.webp', 'price' => 1400],
            ['name' => 'Food Storage Container Set', 'file' => 'food-container.webp', 'price' => 650],
            ['name' => 'Non-stick Cookware Set', 'file' => 'non-stick-cookware-set.webp', 'price' => 4200],
        ],
        'Sports & Outdoor' => [
            ['name' => 'Adjustable Dumbbells', 'file' => 'adjustable-dumbles.webp', 'price' => 2800],
            ['name' => 'Badminton Set', 'file' => 'badminton-set.webp', 'price' => 950],
            ['name' => 'Camping Tent', 'file' => 'camping-tent.webp', 'price' => 3500],
            ['name' => 'Football', 'file' => 'football.webp', 'price' => 650],
            ['name' => 'Mountain Bicycle Helmet', 'file' => 'mountain-bycyle-helmet.webp', 'price' => 1200],
            ['name' => 'Sports Water Bottle', 'file' => 'sports-water-bottle.webp', 'price' => 350],
        ],
        'Stationery' => [
            ['name' => 'School Backpack', 'file' => 'bagpack.webp', 'price' => 1100],
            ['name' => 'Color Pencil Set', 'file' => 'color-pencil-set.webp', 'price' => 220],
            ['name' => 'Document File Folder', 'file' => 'document-file-folder.webp', 'price' => 90],
            ['name' => 'Pencil Set', 'file' => 'pencil-set.webp', 'price' => 60],
        ],
        'Toys & Baby' => [
            ['name' => 'Baby Feeding Set', 'file' => 'baby-feeding-set.webp', 'price' => 850],
            ['name' => 'Baby Stroller', 'file' => 'baby-stoller.webp', 'price' => 6500],
            ['name' => 'Building Blocks Set', 'file' => 'building-blocks.webp', 'price' => 750],
            ['name' => 'Kids Ride-on Car', 'file' => 'kids-ride-car.webp', 'price' => 4200],
            ['name' => 'Remote Control Car', 'file' => 'remort-control-car.webp', 'price' => 1500],
            ['name' => 'Teddy Bear', 'file' => 'teddy.webp', 'price' => 550],
        ],
    ];

    /**
     * Existing hand-written products whose category photo is reused as
     * their image_path instead of spawning a near-duplicate product.
     */
    protected const REUSED_IMAGES = [
        'GAR-001' => ['category' => 'Fashion', 'file' => 'cotton-t-shirt.webp'],
        'STA-001' => ['category' => 'Stationery', 'file' => 'notebook.webp'],
        'STA-002' => ['category' => 'Stationery', 'file' => 'ballpen-set.webp'],
    ];

    /**
     * Flash-sale products (see WebsiteController::ecommerceHome, which shows
     * up to 12) — one per category, plus 4 extra across categories that
     * already have more than one product for extra storefront variety.
     */
    protected const FLASH_SALE_SKUS = [
        'BH-101',   // Moisturizing Cream
        'ELEC-101', // Bluetooth Speaker
        'FAS-101',  // Casual Sneakers
        'GRO-101',  // Apple
        'HK-101',   // Blender & Juicer
        'SPO-101',  // Adjustable Dumbbells
        'STA-101',  // School Backpack
        'TOY-101',  // Baby Feeding Set
        'GRO-103',  // Eggs (Dozen)
        'HK-104',   // Electric Kettle
        'SPO-103',  // Camping Tent
        'STA-103',  // Document File Folder
    ];

    /**
     * A handful of featured products (WebsiteController::ecommerceHome shows
     * up to 6) — deliberately different SKUs from FLASH_SALE_SKUS for
     * storefront variety.
     */
    protected const FEATURED_SKUS = [
        'ELEC-104', // Smartphone
        'BH-103',   // Perfume
        'FAS-103',  // Leather Handbag
        'TOY-102',  // Baby Stroller
        'GRO-104',  // Mixed Fruits Basket
        'HK-102',   // Decorative Table Lamp
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');
        $brands = Brand::pluck('id', 'name');
        $units = Unit::pluck('id', 'short_name');

        $products = [
            [
                'name' => 'Walton Rice Cooker 1.8L',
                'sku' => 'ELEC-001',
                'barcode' => '8801000000011',
                'category' => 'Electronics',
                'brand' => 'Walton',
                'unit' => 'Pcs',
                'estimated_cost' => 2200,
                'selling_price' => 2800,
                'reorder_level' => 5,
            ],
            [
                'name' => 'Walton LED Bulb 12W',
                'sku' => 'ELEC-002',
                'barcode' => '8801000000028',
                'category' => 'Electronics',
                'brand' => 'Walton',
                'unit' => 'Pcs',
                'estimated_cost' => 150,
                'selling_price' => 220,
                'reorder_level' => 20,
            ],
            [
                'name' => 'Pran Mustard Oil 1L',
                'sku' => 'GRO-001',
                'barcode' => '8801000000035',
                'category' => 'Groceries',
                'brand' => 'Pran',
                'unit' => 'L',
                'estimated_cost' => 180,
                'selling_price' => 210,
                'reorder_level' => 30,
            ],
            [
                'name' => 'Pran Chanachur 200g',
                'sku' => 'GRO-002',
                'barcode' => '8801000000042',
                'category' => 'Groceries',
                'brand' => 'Pran',
                'unit' => 'Pack',
                'estimated_cost' => 60,
                'selling_price' => 80,
                'reorder_level' => 40,
            ],
            [
                'name' => 'Square Toilet Soap 100g',
                'sku' => 'HK-001',
                'barcode' => '8801000000059',
                'category' => 'Home & Kitchen',
                'brand' => 'Square',
                'unit' => 'Pcs',
                'estimated_cost' => 35,
                'selling_price' => 50,
                'reorder_level' => 25,
            ],
            [
                'name' => "Men's Cotton T-Shirt",
                'sku' => 'GAR-001',
                'barcode' => '8801000000066',
                'category' => 'Fashion',
                'brand' => 'Generic',
                'unit' => 'Pcs',
                'estimated_cost' => 250,
                'selling_price' => 450,
                'reorder_level' => 15,
            ],
            [
                'name' => 'Ladies Kurti',
                'sku' => 'GAR-002',
                'barcode' => '8801000000073',
                'category' => 'Fashion',
                'brand' => 'Generic',
                'unit' => 'Pcs',
                'estimated_cost' => 400,
                'selling_price' => 700,
                'reorder_level' => 10,
            ],
            [
                'name' => 'A4 Exercise Notebook',
                'sku' => 'STA-001',
                'barcode' => '8801000000080',
                'category' => 'Stationery',
                'brand' => 'Generic',
                'unit' => 'Pcs',
                'estimated_cost' => 25,
                'selling_price' => 40,
                'reorder_level' => 50,
            ],
            [
                'name' => 'Ball Point Pen (Box of 10)',
                'sku' => 'STA-002',
                'barcode' => '8801000000097',
                'category' => 'Stationery',
                'brand' => 'Generic',
                'unit' => 'Box',
                'estimated_cost' => 80,
                'selling_price' => 120,
                'reorder_level' => 20,
            ],
            [
                'name' => 'Non-stick Frying Pan',
                'sku' => 'HK-002',
                'barcode' => '8801000000103',
                'category' => 'Home & Kitchen',
                'brand' => 'Generic',
                'unit' => 'Pcs',
                'estimated_cost' => 350,
                'selling_price' => 550,
                'reorder_level' => 8,
            ],
        ];

        foreach ($products as $product) {
            $unitId = $units[$product['unit']];
            $imagePath = isset(self::REUSED_IMAGES[$product['sku']])
                ? $this->copyImageProduct(
                    self::IMAGE_CATEGORIES[self::REUSED_IMAGES[$product['sku']]['category']]['folder'],
                    self::REUSED_IMAGES[$product['sku']]['file']
                )
                : null;

            Product::firstOrCreate(
                ['sku' => $product['sku']],
                [
                    'name' => $product['name'],
                    'barcode' => $product['barcode'],
                    'category_id' => $categories[$product['category']],
                    'brand_id' => $brands[$product['brand']],
                    'stock_unit_id' => $unitId,
                    'purchase_unit_id' => $unitId,
                    'purchase_unit_conversion' => 1,
                    'sale_unit_id' => $unitId,
                    'sale_unit_conversion' => 1,
                    'estimated_cost' => $product['estimated_cost'],
                    'selling_price' => $product['selling_price'],
                    'reorder_level' => $product['reorder_level'],
                    'image_path' => $imagePath,
                    'status' => true,
                ]
            );
        }

        $this->seedVariantProducts($categories, $brands, $units);
        $this->seedImageBackedProducts($categories, $brands['Generic'], $units);

        Product::whereIn('sku', self::FLASH_SALE_SKUS)->update(['is_flash_sale' => true]);
        Product::whereIn('sku', self::FEATURED_SKUS)->update(['is_featured' => true]);
    }

    /**
     * A couple of has_variants=true demo products, so the Attribute /
     * Product Variant feature has real data to show on first login.
     */
    protected function seedVariantProducts(Collection $categories, Collection $brands, Collection $units): void
    {
        $colorAttributeId = Attribute::where('name', 'Color')->value('id');
        $sizeAttributeId = Attribute::where('name', 'Size')->value('id');
        $colorValueIds = AttributeValue::where('attribute_id', $colorAttributeId)->pluck('id', 'value');
        $sizeValueIds = AttributeValue::where('attribute_id', $sizeAttributeId)->pluck('id', 'value');

        // Polo T-Shirt: varies by Color and Size.
        $poloUnitId = $units['Pcs'];
        $polo = Product::firstOrCreate(
            ['sku' => 'GAR-003'],
            [
                'name' => 'Premium Polo T-Shirt',
                'category_id' => $categories['Fashion'],
                'brand_id' => $brands['Generic'],
                'stock_unit_id' => $poloUnitId,
                'purchase_unit_id' => $poloUnitId,
                'purchase_unit_conversion' => 1,
                'sale_unit_id' => $poloUnitId,
                'sale_unit_conversion' => 1,
                'estimated_cost' => 300,
                'selling_price' => 550,
                'reorder_level' => 10,
                'status' => true,
                'has_variants' => true,
            ]
        );

        foreach (['Black', 'White', 'Blue'] as $color) {
            foreach (['M', 'L', 'XL'] as $size) {
                $variant = ProductVariant::firstOrCreate(
                    ['sku' => 'GAR-003-'.strtoupper(substr($color, 0, 3)).'-'.$size],
                    [
                        'product_id' => $polo->id,
                        'selling_price' => 550,
                        'estimated_cost' => 300,
                        'status' => true,
                    ]
                );

                $variant->attributeValues()->syncWithoutDetaching([
                    $colorValueIds[$color] => ['attribute_id' => $colorAttributeId],
                    $sizeValueIds[$size] => ['attribute_id' => $sizeAttributeId],
                ]);
            }
        }

        // Cotton Bed Sheet: varies by Color only.
        $bedUnitId = $units['Pcs'];
        $bedSheet = Product::firstOrCreate(
            ['sku' => 'HK-003'],
            [
                'name' => 'Cotton Bed Sheet (King Size)',
                'category_id' => $categories['Home & Kitchen'],
                'brand_id' => $brands['Generic'],
                'stock_unit_id' => $bedUnitId,
                'purchase_unit_id' => $bedUnitId,
                'purchase_unit_conversion' => 1,
                'sale_unit_id' => $bedUnitId,
                'sale_unit_conversion' => 1,
                'estimated_cost' => 600,
                'selling_price' => 950,
                'reorder_level' => 10,
                'status' => true,
                'has_variants' => true,
            ]
        );

        foreach (['Black', 'White', 'Blue', 'Green'] as $color) {
            $variant = ProductVariant::firstOrCreate(
                ['sku' => 'HK-003-'.strtoupper(substr($color, 0, 3))],
                [
                    'product_id' => $bedSheet->id,
                    'selling_price' => 950,
                    'estimated_cost' => 600,
                    'status' => true,
                ]
            );

            $variant->attributeValues()->syncWithoutDetaching([
                $colorValueIds[$color] => ['attribute_id' => $colorAttributeId],
            ]);
        }
    }

    /**
     * One product per photo under public/assets/ecommerce/categories, so the
     * storefront catalog has a real image for every category (see
     * self::IMAGE_PRODUCTS).
     */
    protected function seedImageBackedProducts(Collection $categories, int $genericBrandId, Collection $units): void
    {
        $unitId = $units['Pcs'];
        $categoryOrders = array_flip(array_keys(self::IMAGE_CATEGORIES));

        foreach (self::IMAGE_PRODUCTS as $categoryName => $items) {
            $meta = self::IMAGE_CATEGORIES[$categoryName];
            $categoryId = $categories[$categoryName];
            $categoryOrder = $categoryOrders[$categoryName] + 1;

            foreach ($items as $index => $item) {
                $sku = sprintf('%s-1%02d', $meta['code'], $index + 1);
                $unit = $units[$item['unit'] ?? 'Pcs'] ?? $unitId;
                $imagePath = $this->copyImageProduct($meta['folder'], $item['file']);

                Product::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $item['name'],
                        'barcode' => sprintf('880%02d0000%04d', $categoryOrder, $index + 1),
                        'category_id' => $categoryId,
                        'brand_id' => $genericBrandId,
                        'stock_unit_id' => $unit,
                        'purchase_unit_id' => $unit,
                        'purchase_unit_conversion' => 1,
                        'sale_unit_id' => $unit,
                        'sale_unit_conversion' => 1,
                        'estimated_cost' => round($item['price'] * 0.65),
                        'selling_price' => $item['price'],
                        'reorder_level' => 10,
                        'image_path' => $imagePath,
                        'status' => true,
                    ]
                );
            }
        }
    }

    protected function copyImageProduct(string $folder, string $file): string
    {
        return $this->copyEcommerceImage(
            'categories/'.$folder.'/'.$file,
            'products/'.Str::slug(pathinfo($file, PATHINFO_FILENAME)).'.'.pathinfo($file, PATHINFO_EXTENSION)
        );
    }
}
