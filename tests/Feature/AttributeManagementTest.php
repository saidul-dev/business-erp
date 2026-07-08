<?php

namespace Tests\Feature;

use App\Models\Attribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribute_has_ordered_values(): void
    {
        $attribute = Attribute::create(['name' => 'Size']);
        $attribute->values()->create(['value' => 'L', 'sort_order' => 2]);
        $attribute->values()->create(['value' => 'S', 'sort_order' => 1]);

        $this->assertSame(['S', 'L'], $attribute->values->pluck('value')->all());
    }
}
