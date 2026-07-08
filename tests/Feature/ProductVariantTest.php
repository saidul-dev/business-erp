<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    private function baseProduct(): Product
    {
        $category = Category::create(['name' => 'General']);
        $unit = Unit::create(['name' => 'Piece', 'short_name' => 'pc']);

        return Product::create([
            'name' => 'T-Shirt',
            'sku' => 'TSHIRT',
            'category_id' => $category->id,
            'stock_unit_id' => $unit->id,
            'purchase_unit_conversion' => 1,
            'sale_unit_conversion' => 1,
            'estimated_cost' => 100,
            'selling_price' => 150,
            'reorder_level' => 0,
            'has_variants' => true,
        ]);
    }

    public function test_variant_label_joins_attribute_values(): void
    {
        $product = $this->baseProduct();
        $color = Attribute::create(['name' => 'Color']);
        $red = $color->values()->create(['value' => 'Red']);
        $size = Attribute::create(['name' => 'Size']);
        $medium = $size->values()->create(['value' => 'M']);

        $variant = $product->variants()->create([
            'sku' => 'TSHIRT-RED-M', 'selling_price' => 160, 'status' => true,
        ]);
        $variant->attributeValues()->attach($red->id, ['attribute_id' => $color->id]);
        $variant->attributeValues()->attach($medium->id, ['attribute_id' => $size->id]);

        $this->assertSame('Red / M', $variant->fresh()->label);
        $this->assertTrue($product->isVariable());
    }
}
