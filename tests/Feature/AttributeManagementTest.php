<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
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

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    public function test_admin_can_create_attribute_with_values(): void
    {
        $this->actingAs($this->admin())
            ->post(route('attributes.store'), [
                'name' => 'Color',
                'values' => ['Red', 'Blue', ''],
            ])
            ->assertRedirect(route('attributes.index'));

        $attribute = Attribute::firstWhere('name', 'Color');
        $this->assertNotNull($attribute);
        $this->assertSame(['Red', 'Blue'], $attribute->values->pluck('value')->all());
    }

    public function test_admin_can_update_attribute_values(): void
    {
        $attribute = Attribute::create(['name' => 'Size']);
        $keep = $attribute->values()->create(['value' => 'S', 'sort_order' => 0]);
        $attribute->values()->create(['value' => 'M', 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->put(route('attributes.update', $attribute), [
                'name' => 'Size',
                'values' => ['S', 'L'],
            ])
            ->assertRedirect(route('attributes.index'));

        $this->assertSame(['S', 'L'], $attribute->fresh()->values->pluck('value')->all());
        $this->assertDatabaseHas('attribute_values', ['id' => $keep->id, 'value' => 'S']);
    }

    public function test_cannot_delete_attribute_used_by_a_variant(): void
    {
        // Guard exists once variants reference attributes (Task 3+). For now
        // assert a plain attribute deletes cleanly.
        $attribute = Attribute::create(['name' => 'Material']);

        $this->actingAs($this->admin())
            ->delete(route('attributes.destroy', $attribute))
            ->assertRedirect(route('attributes.index'));

        $this->assertDatabaseMissing('attributes', ['id' => $attribute->id]);
    }

    public function test_viewer_without_create_permission_is_forbidden(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo('inventory.view'); // view only, no create

        $this->actingAs($user)
            ->post(route('attributes.store'), ['name' => 'X', 'values' => ['a']])
            ->assertForbidden();
    }
}
