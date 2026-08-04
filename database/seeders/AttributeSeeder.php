<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $tenantId = null): void
    {
        $attributes = [
            'Portion Size' => ['Regular', 'Large', 'Family'],
            'Spice Level' => ['Mild', 'Medium', 'Hot'],
        ];

        foreach ($attributes as $name => $values) {
            $attribute = Attribute::firstOrCreate(['tenant_id' => $tenantId, 'name' => $name]);

            foreach ($values as $sortOrder => $value) {
                AttributeValue::firstOrCreate(
                    ['attribute_id' => $attribute->id, 'value' => $value],
                    ['sort_order' => $sortOrder]
                );
            }
        }
    }
}
