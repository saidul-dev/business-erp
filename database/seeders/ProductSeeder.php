<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
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
                'category' => 'Garments',
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
                'category' => 'Garments',
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
                    'status' => true,
                ]
            );
        }
    }
}
