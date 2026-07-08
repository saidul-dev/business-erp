# Product Variants & Industry-Standard Features Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let each product opt into industry-standard features — variants (Color/Size with per-variant price/SKU/barcode), plus toggle flags for batch/expiry/serial tracking and warranty/guarantee durations — via checkboxes on the product form.

**Architecture:** Global reusable attributes (`attributes` + `attribute_values`) are managed like Category/Brand. Products gain boolean feature flags and nullable warranty/guarantee period columns. When a product `has_variants`, its sellable rows live in `product_variants`, each linked to the attribute values that compose it via a `product_variant_values` pivot. Batch/expiry/serial store only intent (booleans) now; their data capture arrives with the future Purchase/Production modules. Blade + Alpine.js drive the progressive-disclosure form.

**Tech Stack:** Laravel 12, PHP 8.2, Blade, Alpine.js, Tailwind, spatie/laravel-permission, PHPUnit (sqlite `:memory:`).

## Global Constraints

- Follow existing controller pattern: `implements HasMiddleware` with `permission:inventory.{view,create,edit,delete}` mapping (see `app/Http/Controllers/Admin/BrandController.php`).
- All new inventory master-data routes go inside the existing auth group in `routes/web.php`, using `Route::resource(...)->except('show')`.
- Migrations must sort AFTER `2026_07_09_000000_*`; use the `2026_07_10_*` prefix and be reversible (`down()` drops what `up()` adds).
- Attribute management reuses `inventory.*` permissions — no new permission group.
- Tests: `use RefreshDatabase;` seed permissions with `RolePermissionSeeder`, act as an `Admin` user. DB is sqlite in-memory (from `phpunit.xml`).
- `track_expiry = true` MUST force `track_batch = true` server-side, not only in the UI.
- Money columns are `decimal(12,2)`; conversion/quantity precision elsewhere is `decimal(12,4)`.
- Run the full suite with: `php artisan test`

---

## File Structure

**Create:**
- `database/migrations/2026_07_10_000001_create_attributes_tables.php` — `attributes`, `attribute_values`
- `database/migrations/2026_07_10_000002_add_feature_flags_to_products_table.php` — flag + warranty/guarantee columns
- `database/migrations/2026_07_10_000003_create_product_variants_tables.php` — `product_variants`, `product_variant_values`
- `app/Models/Attribute.php`, `app/Models/AttributeValue.php`, `app/Models/ProductVariant.php`
- `app/Http/Controllers/Admin/AttributeController.php`
- `resources/views/admin/attributes/{index,create,edit,_form}.blade.php`
- `tests/Feature/AttributeManagementTest.php`
- `tests/Feature/ProductFeatureFlagsTest.php`
- `tests/Feature/ProductVariantTest.php`

**Modify:**
- `app/Models/Product.php` — relationships, casts, helpers
- `app/Http/Controllers/Admin/ProductController.php` — `formOptions()`, `validated()`, `store()`, `update()`
- `resources/views/admin/products/_form.blade.php` — features card + variant builder
- `routes/web.php` — attributes resource route
- `resources/views/layouts/app.blade.php` — Attributes sidebar link

---

## Task 1: Attributes & values — schema and models

**Files:**
- Create: `database/migrations/2026_07_10_000001_create_attributes_tables.php`
- Create: `app/Models/Attribute.php`
- Create: `app/Models/AttributeValue.php`
- Test: `tests/Feature/AttributeManagementTest.php` (model portion)

**Interfaces:**
- Produces:
  - `Attribute` — `$fillable = ['name']`; `values(): HasMany` → `AttributeValue` ordered by `sort_order`.
  - `AttributeValue` — `$fillable = ['attribute_id','value','sort_order']`; `attribute(): BelongsTo`.
  - Tables `attributes(id, name UNIQUE, timestamps)`, `attribute_values(id, attribute_id FK cascade, value, sort_order default 0, timestamps, UNIQUE(attribute_id,value))`.

- [ ] **Step 1: Write the migration**

Create `database/migrations/2026_07_10_000001_create_attributes_tables.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
```

- [ ] **Step 2: Write the models**

Create `app/Models/Attribute.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    protected $fillable = ['name'];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order')->orderBy('id');
    }
}
```

Create `app/Models/AttributeValue.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    protected $fillable = ['attribute_id', 'value', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
```

- [ ] **Step 3: Write the failing model test**

Create `tests/Feature/AttributeManagementTest.php`:
```php
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
```

- [ ] **Step 4: Run the test**

Run: `php artisan test --filter=test_attribute_has_ordered_values`
Expected: PASS (migration + models wired correctly).

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_07_10_000001_create_attributes_tables.php app/Models/Attribute.php app/Models/AttributeValue.php tests/Feature/AttributeManagementTest.php
git commit -m "feat: add global attribute + attribute value schema and models"
```

---

## Task 2: Attribute CRUD — controller, routes, views, nav

**Files:**
- Create: `app/Http/Controllers/Admin/AttributeController.php`
- Create: `resources/views/admin/attributes/{index,create,edit,_form}.blade.php`
- Modify: `routes/web.php` (after the `units` resource line, ~line 49)
- Modify: `resources/views/layouts/app.blade.php` (after the Units sidebar link, ~line 91)
- Test: `tests/Feature/AttributeManagementTest.php`

**Interfaces:**
- Consumes: `Attribute`, `AttributeValue` from Task 1.
- Produces: named routes `attributes.index|create|store|edit|update|destroy`. Values submitted as `values[]` (array of strings); controller syncs them (create/update/delete) in a transaction.

- [ ] **Step 1: Write the failing CRUD tests**

Add to `tests/Feature/AttributeManagementTest.php` (add imports `use App\Models\User;`, `use Database\Seeders\RolePermissionSeeder;`, `use App\Models\AttributeValue;`):
```php
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
        $user->assignRole('Sales'); // has inventory.view only

        $this->actingAs($user)
            ->post(route('attributes.store'), ['name' => 'X', 'values' => ['a']])
            ->assertForbidden();
    }
```

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --filter=AttributeManagementTest`
Expected: FAIL — route `attributes.store` not defined.

- [ ] **Step 3: Add the route**

In `routes/web.php`, immediately after the `units` toggle-status line (~line 49), add:
```php
        Route::resource('attributes', \App\Http\Controllers\Admin\AttributeController::class)->except('show');
```

- [ ] **Step 4: Write the controller**

Create `app/Http/Controllers/Admin/AttributeController.php`:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:inventory.view', only: ['index']),
            new Middleware('permission:inventory.create', only: ['create', 'store']),
            new Middleware('permission:inventory.edit', only: ['edit', 'update']),
            new Middleware('permission:inventory.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $attributes = Attribute::withCount('values')->orderBy('name')->paginate(15);

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $attribute = Attribute::create(['name' => $data['name']]);
            $this->syncValues($attribute, $data['values']);
        });

        return redirect()->route('attributes.index')->with('success', "Attribute \"{$data['name']}\" created.");
    }

    public function edit(Attribute $attribute)
    {
        $attribute->load('values');

        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $this->validated($request, $attribute);

        DB::transaction(function () use ($attribute, $data) {
            $attribute->update(['name' => $data['name']]);
            $this->syncValues($attribute, $data['values']);
        });

        return redirect()->route('attributes.index')->with('success', "Attribute \"{$attribute->name}\" updated.");
    }

    public function destroy(Attribute $attribute)
    {
        // Block deletion while any product variant references this attribute.
        $used = DB::table('product_variant_values')->where('attribute_id', $attribute->id)->exists();
        if ($used) {
            return back()->with('error', "Attribute \"{$attribute->name}\" is used by product variants — remove those first.");
        }

        $attribute->delete();

        return redirect()->route('attributes.index')->with('success', "Attribute \"{$attribute->name}\" deleted.");
    }

    protected function validated(Request $request, ?Attribute $attribute = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('attributes', 'name')->ignore($attribute?->id)],
            'values' => ['required', 'array', 'min:1'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ]);

        // Drop blank rows and duplicate values (case-insensitive), preserve order.
        $seen = [];
        $clean = [];
        foreach ($validated['values'] as $value) {
            $value = trim((string) $value);
            $key = mb_strtolower($value);
            if ($value === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $clean[] = $value;
        }

        if ($clean === []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'values' => 'Add at least one value.',
            ]);
        }

        $validated['values'] = $clean;

        return $validated;
    }

    protected function syncValues(Attribute $attribute, array $values): void
    {
        $existing = $attribute->values()->get()->keyBy(fn ($v) => mb_strtolower($v->value));
        $keepIds = [];

        foreach (array_values($values) as $index => $value) {
            $key = mb_strtolower($value);
            if ($existing->has($key)) {
                $row = $existing->get($key);
                $row->update(['value' => $value, 'sort_order' => $index]);
                $keepIds[] = $row->id;
            } else {
                $keepIds[] = $attribute->values()->create(['value' => $value, 'sort_order' => $index])->id;
            }
        }

        $attribute->values()->whereNotIn('id', $keepIds)->delete();
    }
}
```

- [ ] **Step 5: Write the index view**

Create `resources/views/admin/attributes/index.blade.php`:
```blade
<x-app-layout>
    <x-slot name="title">Attributes</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">Attributes</h2>
                <p class="text-sm text-slate-500 mt-0.5">Reusable variant options like Color and Size</p>
            </div>
            @can('inventory.create')
                <a href="{{ route('attributes.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Add Attribute</a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Values</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attributes as $attribute)
                    <tr>
                        <td class="px-4 py-3 font-medium text-brand-900">{{ $attribute->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $attribute->values_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('inventory.edit')
                                <a href="{{ route('attributes.edit', $attribute) }}" class="text-brand-700 hover:underline">Edit</a>
                            @endcan
                            @can('inventory.delete')
                                <form method="POST" action="{{ route('attributes.destroy', $attribute) }}" class="inline" onsubmit="return confirm('Delete this attribute?')">
                                    @csrf @method('DELETE')
                                    <button class="ml-3 text-rose-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">No attributes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attributes->links() }}</div>
</x-app-layout>
```

- [ ] **Step 6: Write the shared form partial**

Create `resources/views/admin/attributes/_form.blade.php`:
```blade
@php $editing = isset($attribute); @endphp

<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5 max-w-xl"
     x-data="{ values: {{ Illuminate\Support\Js::from(old('values', $editing ? $attribute->values->pluck('value')->all() : [''])) }} }">
    <div>
        <x-input-label for="name" value="Attribute Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $attribute->name ?? '')" required autofocus placeholder="e.g. Color" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label value="Values" />
        <p class="text-xs text-slate-400 mb-2">e.g. Red, Blue, Black</p>
        <template x-for="(value, i) in values" :key="i">
            <div class="mt-2 flex items-center gap-2">
                <input type="text" :name="'values[' + i + ']'" x-model="values[i]"
                       class="block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500" placeholder="Value">
                <button type="button" @click="values.splice(i, 1)" x-show="values.length > 1"
                        class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">Remove</button>
            </div>
        </template>
        <button type="button" @click="values.push('')"
                class="mt-3 rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">+ Add Value</button>
        <x-input-error class="mt-2" :messages="$errors->get('values')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update Attribute' : 'Create Attribute' }}</x-primary-button>
    <a href="{{ route('attributes.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
```

- [ ] **Step 7: Write create + edit wrappers**

Create `resources/views/admin/attributes/create.blade.php`:
```blade
<x-app-layout>
    <x-slot name="title">Add Attribute</x-slot>
    <x-slot name="header"><h2 class="text-2xl font-bold text-brand-900">Add Attribute</h2></x-slot>

    <form method="POST" action="{{ route('attributes.store') }}">
        @csrf
        @include('admin.attributes._form')
    </form>
</x-app-layout>
```

Create `resources/views/admin/attributes/edit.blade.php`:
```blade
<x-app-layout>
    <x-slot name="title">Edit Attribute</x-slot>
    <x-slot name="header"><h2 class="text-2xl font-bold text-brand-900">Edit Attribute</h2></x-slot>

    <form method="POST" action="{{ route('attributes.update', $attribute) }}">
        @csrf
        @method('PUT')
        @include('admin.attributes._form')
    </form>
</x-app-layout>
```

- [ ] **Step 8: Add the sidebar link**

In `resources/views/layouts/app.blade.php`, immediately after the Units `x-sidebar-sublink` block (~line 91), add:
```blade
                            <x-sidebar-sublink :href="route('attributes.index')" :active="request()->routeIs('attributes.*')">
                                Attributes
                            </x-sidebar-sublink>
```

- [ ] **Step 9: Run the tests to verify they pass**

Run: `php artisan test --filter=AttributeManagementTest`
Expected: PASS (all 5 tests).

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/Admin/AttributeController.php resources/views/admin/attributes routes/web.php resources/views/layouts/app.blade.php tests/Feature/AttributeManagementTest.php
git commit -m "feat: attribute CRUD with inline values and sidebar link"
```

---

## Task 3: Product feature-flag columns, variant tables, and models

**Files:**
- Create: `database/migrations/2026_07_10_000002_add_feature_flags_to_products_table.php`
- Create: `database/migrations/2026_07_10_000003_create_product_variants_tables.php`
- Create: `app/Models/ProductVariant.php`
- Modify: `app/Models/Product.php`
- Test: `tests/Feature/ProductVariantTest.php` (model portion)

**Interfaces:**
- Consumes: `Attribute`, `AttributeValue` (Task 1); `Product` (existing).
- Produces:
  - `products` gains: `has_variants`, `track_batch`, `track_expiry`, `track_serial` (booleans, default false); `warranty_period`, `guarantee_period` (unsignedInteger nullable); `warranty_unit`, `guarantee_unit` (string nullable).
  - `product_variants(id, product_id FK cascade, sku UNIQUE, barcode nullable UNIQUE, selling_price decimal(12,2), estimated_cost decimal(12,2) nullable, status bool default true, timestamps)`.
  - `product_variant_values(id, product_variant_id FK cascade, attribute_id FK restrict, attribute_value_id FK restrict, UNIQUE(product_variant_id, attribute_id))`.
  - `ProductVariant` model: `$fillable = ['product_id','sku','barcode','selling_price','estimated_cost','status']`; `product(): BelongsTo`; `attributeValues(): BelongsToMany` (via `product_variant_values`, `withPivot('attribute_id')`); `getLabelAttribute(): string` → values joined by `' / '`.
  - `Product`: `variants(): HasMany`; boolean casts for the 4 flags; `isVariable(): bool`.

- [ ] **Step 1: Write the products flag migration**

Create `database/migrations/2026_07_10_000002_add_feature_flags_to_products_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('status');
            $table->boolean('track_batch')->default(false)->after('has_variants');
            $table->boolean('track_expiry')->default(false)->after('track_batch');
            $table->boolean('track_serial')->default(false)->after('track_expiry');
            $table->unsignedInteger('warranty_period')->nullable()->after('track_serial');
            $table->string('warranty_unit')->nullable()->after('warranty_period');
            $table->unsignedInteger('guarantee_period')->nullable()->after('warranty_unit');
            $table->string('guarantee_unit')->nullable()->after('guarantee_period');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'has_variants', 'track_batch', 'track_expiry', 'track_serial',
                'warranty_period', 'warranty_unit', 'guarantee_period', 'guarantee_unit',
            ]);
        });
    }
};
```

- [ ] **Step 2: Write the variant tables migration**

Create `database/migrations/2026_07_10_000003_create_product_variants_tables.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->restrictOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->restrictOnDelete();
            $table->unique(['product_variant_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
    }
};
```

- [ ] **Step 3: Write the ProductVariant model**

Create `app/Models/ProductVariant.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'selling_price', 'estimated_cost', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'selling_price' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_values')
            ->withPivot('attribute_id');
    }

    /**
     * Human-readable variant label, e.g. "Red / M".
     */
    public function getLabelAttribute(): string
    {
        return $this->attributeValues->pluck('value')->join(' / ');
    }
}
```

- [ ] **Step 4: Update the Product model**

In `app/Models/Product.php`, add to `$fillable` (after `'status'`):
```php
        'has_variants',
        'track_batch',
        'track_expiry',
        'track_serial',
        'warranty_period',
        'warranty_unit',
        'guarantee_period',
        'guarantee_unit',
```
Add to `$casts`:
```php
        'has_variants' => 'boolean',
        'track_batch' => 'boolean',
        'track_expiry' => 'boolean',
        'track_serial' => 'boolean',
        'warranty_period' => 'integer',
        'guarantee_period' => 'integer',
```
Add these to the imports at the top (after the existing `use ... BelongsTo;`):
```php
use Illuminate\Database\Eloquent\Relations\HasMany;
```
Add these methods to the class (e.g. after `saleUnit()`):
```php
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function isVariable(): bool
    {
        return (bool) $this->has_variants;
    }
```

- [ ] **Step 5: Write the failing model test**

Create `tests/Feature/ProductVariantTest.php`:
```php
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
```

> Note: this test assumes `Category` has `$fillable` including `name`, and `Unit` includes `name`, `short_name`. Confirm against those models; adjust the create() payloads if their fillable differs.

- [ ] **Step 6: Run the test**

Run: `php artisan test --filter=test_variant_label_joins_attribute_values`
Expected: PASS.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_10_000002_add_feature_flags_to_products_table.php database/migrations/2026_07_10_000003_create_product_variants_tables.php app/Models/ProductVariant.php app/Models/Product.php tests/Feature/ProductVariantTest.php
git commit -m "feat: add product feature flags, variant tables and models"
```

---

## Task 4: ProductController — persist feature flags + warranty/guarantee

**Files:**
- Modify: `app/Http/Controllers/Admin/ProductController.php` (`formOptions()`, `validated()`, `store()`, `update()`)
- Test: `tests/Feature/ProductFeatureFlagsTest.php`

**Interfaces:**
- Consumes: `Product` flags/casts (Task 3), `Attribute` (Task 1).
- Produces: `formOptions()` returns an extra `attributes` key (Attributes with values eager-loaded). `validated()` returns flags + warranty/guarantee normalized (units nulled when period is null; `track_batch` forced true when `track_expiry`).

- [ ] **Step 1: Write the failing flag tests**

Create `tests/Feature/ProductFeatureFlagsTest.php`:
```php
<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFeatureFlagsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        $category = Category::create(['name' => 'General']);
        $unit = Unit::create(['name' => 'Piece', 'short_name' => 'pc']);

        return array_merge([
            'name' => 'Basic Product',
            'sku' => 'BASIC-1',
            'category_id' => $category->id,
            'stock_unit_id' => $unit->id,
            'purchase_unit_conversion' => 1,
            'sale_unit_conversion' => 1,
            'estimated_cost' => 10,
            'selling_price' => 20,
            'reorder_level' => 0,
        ], $overrides);
    }

    public function test_expiry_forces_batch_tracking_on(): void
    {
        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->payload([
                'track_expiry' => '1',
                'track_batch' => '0',
            ]))
            ->assertRedirect(route('products.index'));

        $product = Product::firstWhere('sku', 'BASIC-1');
        $this->assertTrue($product->track_expiry);
        $this->assertTrue($product->track_batch); // forced on by expiry
    }

    public function test_warranty_duration_persists_and_unit_nulls_when_disabled(): void
    {
        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->payload([
                'warranty_period' => 12,
                'warranty_unit' => 'months',
                'guarantee_period' => null,
                'guarantee_unit' => 'years', // should be nulled since period is null
            ]))
            ->assertRedirect(route('products.index'));

        $product = Product::firstWhere('sku', 'BASIC-1');
        $this->assertSame(12, $product->warranty_period);
        $this->assertSame('months', $product->warranty_unit);
        $this->assertNull($product->guarantee_period);
        $this->assertNull($product->guarantee_unit);
    }

    public function test_invalid_warranty_unit_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->payload([
                'warranty_period' => 12,
                'warranty_unit' => 'decades',
            ]))
            ->assertSessionHasErrors('warranty_unit');
    }
}
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter=ProductFeatureFlagsTest`
Expected: FAIL — flags not validated/saved; expiry does not force batch.

- [ ] **Step 3: Extend `formOptions()`**

In `app/Http/Controllers/Admin/ProductController.php`, add `use App\Models\Attribute;` to imports, and update `formOptions()`:
```php
    protected function formOptions(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
            'attributes' => Attribute::with('values')->orderBy('name')->get(),
        ];
    }
```

- [ ] **Step 4: Extend `validated()` with flags + warranty/guarantee**

Replace the `validated()` method body's rule array by adding these keys (keep all existing keys), then append normalization before the return. The full method becomes:
```php
    protected function validated(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product?->id)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($product?->id)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'stock_unit_id' => ['required', 'integer', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'purchase_unit_conversion' => ['required', 'numeric', 'min:0.0001'],
            'sale_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sale_unit_conversion' => ['required', 'numeric', 'min:0.0001'],
            'description' => ['nullable', 'string', 'max:2000'],
            'estimated_cost' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'has_variants' => ['nullable', 'boolean'],
            'track_batch' => ['nullable', 'boolean'],
            'track_expiry' => ['nullable', 'boolean'],
            'track_serial' => ['nullable', 'boolean'],
            'warranty_period' => ['nullable', 'integer', 'min:1'],
            'warranty_unit' => ['nullable', 'required_with:warranty_period', 'in:days,months,years'],
            'guarantee_period' => ['nullable', 'integer', 'min:1'],
            'guarantee_unit' => ['nullable', 'required_with:guarantee_period', 'in:days,months,years'],
        ]);

        // Normalize booleans (unchecked checkboxes are absent from the request).
        foreach (['has_variants', 'track_batch', 'track_expiry', 'track_serial'] as $flag) {
            $validated[$flag] = $request->boolean($flag);
        }

        // Expiry lives on a batch — enabling it implies batch tracking.
        if ($validated['track_expiry']) {
            $validated['track_batch'] = true;
        }

        // A duration with no period is meaningless — null the unit.
        if (empty($validated['warranty_period'])) {
            $validated['warranty_period'] = null;
            $validated['warranty_unit'] = null;
        }
        if (empty($validated['guarantee_period'])) {
            $validated['guarantee_period'] = null;
            $validated['guarantee_unit'] = null;
        }

        return $validated;
    }
```

> `store()` and `update()` already persist via `Product::create(...)` / `$product->update(...)` on the validated array, so the new keys flow through with no change to those methods in this task.

- [ ] **Step 5: Run to verify pass**

Run: `php artisan test --filter=ProductFeatureFlagsTest`
Expected: PASS (3 tests).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/ProductController.php tests/Feature/ProductFeatureFlagsTest.php
git commit -m "feat: validate and persist product feature flags and warranty/guarantee"
```

---

## Task 5: ProductController — variant sync (store/update/delete)

**Files:**
- Modify: `app/Http/Controllers/Admin/ProductController.php` (`store()`, `update()`, add `validateVariants()` + `syncVariants()`)
- Test: `tests/Feature/ProductVariantTest.php` (HTTP portion)

**Interfaces:**
- Consumes: `validated()` flags (Task 4); `ProductVariant`, `Product::variants()` (Task 3).
- Produces: when `has_variants` is true, request carries `variants` = array of rows, each:
  `{ id?: int, sku: string, barcode?: string, selling_price: numeric, estimated_cost?: numeric, status?: bool, values: { <attribute_id>: <attribute_value_id> } }`.
  Variants are created/updated/deleted transactionally; `product_variant_values` rows re-synced per variant. When `has_variants` is false, all existing variants are deleted.

- [ ] **Step 1: Write the failing variant HTTP tests**

Add to `tests/Feature/ProductVariantTest.php` (add imports `use App\Models\User;`, `use Database\Seeders\RolePermissionSeeder;`):
```php
    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Admin');

        return $user;
    }

    private function variablePayload(array $variants): array
    {
        $category = Category::create(['name' => 'Apparel']);
        $unit = Unit::create(['name' => 'Piece', 'short_name' => 'pc']);

        return [
            'name' => 'Polo',
            'sku' => 'POLO',
            'category_id' => $category->id,
            'stock_unit_id' => $unit->id,
            'purchase_unit_conversion' => 1,
            'sale_unit_conversion' => 1,
            'estimated_cost' => 100,
            'selling_price' => 150,
            'reorder_level' => 0,
            'has_variants' => '1',
            'variants' => $variants,
        ];
    }

    public function test_creating_variable_product_stores_variants_and_values(): void
    {
        $color = Attribute::create(['name' => 'Color']);
        $red = $color->values()->create(['value' => 'Red']);
        $blue = $color->values()->create(['value' => 'Blue']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->variablePayload([
                ['sku' => 'POLO-RED', 'selling_price' => 160, 'status' => '1', 'values' => [$color->id => $red->id]],
                ['sku' => 'POLO-BLUE', 'selling_price' => 170, 'status' => '1', 'values' => [$color->id => $blue->id]],
            ]))
            ->assertRedirect(route('products.index'));

        $product = Product::firstWhere('sku', 'POLO');
        $this->assertCount(2, $product->variants);
        $this->assertEqualsCanonical(['POLO-RED', 'POLO-BLUE'], $product->variants->pluck('sku')->all());
        $this->assertDatabaseHas('product_variant_values', [
            'attribute_id' => $color->id, 'attribute_value_id' => $red->id,
        ]);
    }

    public function test_updating_syncs_added_and_removed_variants(): void
    {
        $color = Attribute::create(['name' => 'Color']);
        $red = $color->values()->create(['value' => 'Red']);
        $blue = $color->values()->create(['value' => 'Blue']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->variablePayload([
                ['sku' => 'POLO-RED', 'selling_price' => 160, 'status' => '1', 'values' => [$color->id => $red->id]],
            ]));

        $product = Product::firstWhere('sku', 'POLO');
        $existingId = $product->variants->first()->id;

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), array_merge(
                $this->variablePayload([
                    ['id' => $existingId, 'sku' => 'POLO-RED', 'selling_price' => 165, 'status' => '1', 'values' => [$color->id => $red->id]],
                    ['sku' => 'POLO-BLUE', 'selling_price' => 170, 'status' => '1', 'values' => [$color->id => $blue->id]],
                ]),
                ['sku' => 'POLO'] // keep same product sku
            ))
            ->assertRedirect(route('products.index'));

        $product->refresh()->load('variants');
        $this->assertCount(2, $product->variants);
        $this->assertSame('165.00', $product->variants->firstWhere('id', $existingId)->selling_price);
    }

    public function test_turning_off_variants_deletes_them(): void
    {
        $color = Attribute::create(['name' => 'Color']);
        $red = $color->values()->create(['value' => 'Red']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->variablePayload([
                ['sku' => 'POLO-RED', 'selling_price' => 160, 'status' => '1', 'values' => [$color->id => $red->id]],
            ]));
        $product = Product::firstWhere('sku', 'POLO');

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), [
                'name' => 'Polo', 'sku' => 'POLO', 'category_id' => $product->category_id,
                'stock_unit_id' => $product->stock_unit_id, 'purchase_unit_conversion' => 1,
                'sale_unit_conversion' => 1, 'estimated_cost' => 100, 'selling_price' => 150,
                'reorder_level' => 0, 'has_variants' => '0',
            ])
            ->assertRedirect(route('products.index'));

        $this->assertCount(0, $product->fresh()->variants);
        $this->assertDatabaseCount('product_variant_values', 0);
    }

    public function test_variant_sku_must_be_unique(): void
    {
        $color = Attribute::create(['name' => 'Color']);
        $red = $color->values()->create(['value' => 'Red']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->variablePayload([
                ['sku' => 'DUP', 'selling_price' => 160, 'status' => '1', 'values' => [$color->id => $red->id]],
                ['sku' => 'DUP', 'selling_price' => 170, 'status' => '1', 'values' => [$color->id => $red->id]],
            ]))
            ->assertSessionHasErrors('variants.1.sku');
    }
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter=ProductVariantTest`
Expected: FAIL — variants not validated/stored.

- [ ] **Step 3: Add variant validation helper**

In `app/Http/Controllers/Admin/ProductController.php`, add `use App\Models\ProductVariant;` and `use Illuminate\Support\Facades\DB;` to imports. Add this method:
```php
    /**
     * Validate the nested variants payload. Only called when has_variants is on.
     * Uniqueness for sku/barcode is enforced against product_variants, ignoring
     * rows belonging to this product (so its own variants don't self-collide).
     */
    protected function validateVariants(Request $request, ?Product $product): array
    {
        $ignoreIds = $product ? $product->variants()->pluck('id')->all() : [];
        $ignoreClause = $ignoreIds ? ',NULL,id,id,'.implode(',', $ignoreIds) : '';

        $validated = $request->validate([
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.sku' => ['required', 'string', 'max:100', 'distinct', 'unique:product_variants,sku'.$ignoreClause],
            'variants.*.barcode' => ['nullable', 'string', 'max:100', 'distinct', 'unique:product_variants,barcode'.$ignoreClause],
            'variants.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variants.*.estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'variants.*.status' => ['nullable', 'boolean'],
            'variants.*.values' => ['required', 'array', 'min:1'],
            'variants.*.values.*' => ['required', 'integer', 'exists:attribute_values,id'],
        ]);

        return $validated['variants'];
    }
```

> The `unique` ignore syntax uses the full parameter form:
> `unique:table,column,except,idColumn,extraWhereCol,extraWhereVal...`. Because Laravel's
> array-style `Rule::unique()->ignore()` can't ignore *multiple* ids, we build the string
> form: `,NULL,id` means "no single id to ignore" and each appended `,id,<n>` adds a
> `whereNot('id', <n>)`. If `$ignoreIds` is empty the clause is empty (plain unique).

Correction — the multi-id ignore above is not expressible with the extra-where syntax (that
adds AND conditions, not a NOT IN). Use a closure rule instead. Replace the `variants.*.sku`
and `variants.*.barcode` rules with closures:
```php
            'variants.*.sku' => ['required', 'string', 'max:100', 'distinct',
                function ($attr, $value, $fail) use ($ignoreIds) {
                    $exists = ProductVariant::where('sku', $value)->whereNotIn('id', $ignoreIds)->exists();
                    if ($exists) {
                        $fail('This SKU is already in use.');
                    }
                }],
            'variants.*.barcode' => ['nullable', 'string', 'max:100', 'distinct',
                function ($attr, $value, $fail) use ($ignoreIds) {
                    if ($value === null || $value === '') {
                        return;
                    }
                    $exists = ProductVariant::where('barcode', $value)->whereNotIn('id', $ignoreIds)->exists();
                    if ($exists) {
                        $fail('This barcode is already in use.');
                    }
                }],
```
And drop the now-unused `$ignoreClause` variable. The final method keeps `$ignoreIds` only.

- [ ] **Step 4: Add the sync helper**

Add this method to `ProductController`:
```php
    /**
     * Create/update/delete variants for a product and re-sync their
     * attribute-value pivot rows. $variants is the validated variant array.
     */
    protected function syncVariants(Product $product, array $variants): void
    {
        $keepIds = [];

        foreach ($variants as $row) {
            $attributes = [
                'sku' => $row['sku'],
                'barcode' => $row['barcode'] ?? null,
                'selling_price' => $row['selling_price'],
                'estimated_cost' => $row['estimated_cost'] ?? null,
                'status' => (bool) ($row['status'] ?? true),
            ];

            $variant = ! empty($row['id'])
                ? tap($product->variants()->findOrFail($row['id']))->update($attributes)
                : $product->variants()->create($attributes);

            $keepIds[] = $variant->id;

            // Rebuild the attribute-value links: values is [attribute_id => attribute_value_id].
            $variant->attributeValues()->detach();
            foreach ($row['values'] as $attributeId => $valueId) {
                $variant->attributeValues()->attach($valueId, ['attribute_id' => (int) $attributeId]);
            }
        }

        // Remove variants no longer present.
        $product->variants()->whereNotIn('id', $keepIds)->each(function (ProductVariant $variant) {
            $variant->attributeValues()->detach();
            $variant->delete();
        });
    }
```

- [ ] **Step 5: Wire store() and update() to sync variants**

Replace `store()`:
```php
    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $variants = $validated['has_variants'] ? $this->validateVariants($request, null) : [];

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product = DB::transaction(function () use ($validated, $variants) {
            $product = Product::create(collect($validated)->except('image')->all());
            if ($product->has_variants) {
                $this->syncVariants($product, $variants);
            }

            return $product;
        });

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" created.");
    }
```

Replace `update()`:
```php
    public function update(Request $request, Product $product)
    {
        $validated = $this->validated($request, $product);
        $variants = $validated['has_variants'] ? $this->validateVariants($request, $product) : [];

        if ($request->boolean('remove_image') && $product->image_path) {
            Storage::disk('public')->delete($product->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($request, $product, $validated, $variants) {
            $product->update(collect($validated)->except(['image', 'remove_image'])->all());

            if ($product->has_variants) {
                $this->syncVariants($product, $variants);
            } else {
                // Feature turned off — drop any existing variants and their links.
                $product->variants()->each(function (ProductVariant $variant) {
                    $variant->attributeValues()->detach();
                    $variant->delete();
                });
            }
        });

        return redirect()->route('products.index')->with('success', "Product \"{$product->name}\" updated.");
    }
```

- [ ] **Step 6: Run to verify pass**

Run: `php artisan test --filter=ProductVariantTest`
Expected: PASS (all tests — model + HTTP).

- [ ] **Step 7: Run the full suite**

Run: `php artisan test`
Expected: PASS (no regressions in existing Auth/Profile tests).

- [ ] **Step 8: Commit**

```bash
git add app/Http/Controllers/Admin/ProductController.php tests/Feature/ProductVariantTest.php
git commit -m "feat: transactional variant create/update/delete on products"
```

---

## Task 6: Product form — features card (flags + warranty/guarantee)

**Files:**
- Modify: `resources/views/admin/products/_form.blade.php`
- Test: assert the create page renders the new controls (add to `ProductFeatureFlagsTest`)

**Interfaces:**
- Consumes: form posts consumed by Task 4's `validated()`. Field names: `has_variants`,
  `track_batch`, `track_expiry`, `track_serial`, `warranty_period`, `warranty_unit`,
  `guarantee_period`, `guarantee_unit`.

- [ ] **Step 1: Write the failing render test**

Add to `tests/Feature/ProductFeatureFlagsTest.php`:
```php
    public function test_create_form_shows_feature_controls(): void
    {
        // Ensure at least one category and unit exist so the form renders fully.
        Category::create(['name' => 'General']);
        Unit::create(['name' => 'Piece', 'short_name' => 'pc']);

        $this->actingAs($this->admin())
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('Product Type &amp; Features', false)
            ->assertSee('name="track_batch"', false)
            ->assertSee('name="warranty_period"', false)
            ->assertSee('name="has_variants"', false);
    }
```

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter=test_create_form_shows_feature_controls`
Expected: FAIL — controls not present.

- [ ] **Step 3: Add the features card to `_form.blade.php`**

The root `<div>` of `_form.blade.php` currently opens with:
```blade
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4" x-data="{ preview: @js($product->image_url ?? null), removeImage: false }">
```
Extend that `x-data` object to hold the feature state. Replace the opening tag with:
```blade
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4"
     x-data="productForm({
        preview: @js($product->image_url ?? null),
        hasVariants: {{ old('has_variants', $product->has_variants ?? false) ? 'true' : 'false' }},
        trackBatch: {{ old('track_batch', $product->track_batch ?? false) ? 'true' : 'false' }},
        trackExpiry: {{ old('track_expiry', $product->track_expiry ?? false) ? 'true' : 'false' }},
        trackSerial: {{ old('track_serial', $product->track_serial ?? false) ? 'true' : 'false' }},
        hasWarranty: {{ old('warranty_period', $product->warranty_period ?? null) ? 'true' : 'false' }},
        hasGuarantee: {{ old('guarantee_period', $product->guarantee_period ?? null) ? 'true' : 'false' }},
     })">
```
(The `productForm()` Alpine component is registered in Task 7, Step 4. Until then the flags
card below still works because the component wraps these same properties; if implementing
Task 6 alone, temporarily use an inline object — but implement Task 7 in the same branch.)

Then, immediately BEFORE the final closing `</div>` of the grid (the line before
`<div class="mt-5 flex items-center gap-3">`), insert the features card:
```blade
    <!-- Product Type & Features -->
    <div class="lg:col-span-3 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4">
            <h3 class="font-bold text-brand-900">Product Type &amp; Features</h3>
            <p class="text-xs text-slate-400">Enable only what this product needs. All off by default.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Variants toggle -->
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="has_variants" value="1" x-model="hasVariants"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-brand-900">Has variants</span>
                    <span class="block text-xs text-slate-400">Different Color/Size combinations, each with its own price</span>
                </span>
            </label>

            <!-- Serial / IMEI -->
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="track_serial" value="1" x-model="trackSerial"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-brand-900">Track serial / IMEI</span>
                    <span class="block text-xs text-slate-400">Unique number per unit — captured during purchase (coming soon)</span>
                </span>
            </label>

            <!-- Batch -->
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="track_batch" value="1" x-model="trackBatch" :disabled="trackExpiry"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500 disabled:opacity-60">
                <span>
                    <span class="block text-sm font-semibold text-brand-900">Track batches</span>
                    <span class="block text-xs text-slate-400" x-text="trackExpiry ? 'Required by expiry tracking' : 'Grouped by batch during purchase (coming soon)'"></span>
                </span>
            </label>

            <!-- Expiry -->
            <label class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                <input type="checkbox" name="track_expiry" value="1" x-model="trackExpiry"
                       @change="if (trackExpiry) trackBatch = true"
                       class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                <span>
                    <span class="block text-sm font-semibold text-brand-900">Track expiry date</span>
                    <span class="block text-xs text-slate-400">Perishable goods — enables batch tracking</span>
                </span>
            </label>

            <!-- Warranty -->
            <div class="rounded-lg border border-slate-200 p-3">
                <label class="flex items-start gap-3">
                    <input type="checkbox" x-model="hasWarranty"
                           @change="if (!hasWarranty) { $refs.warrantyPeriod.value = ''; }"
                           class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                    <span>
                        <span class="block text-sm font-semibold text-brand-900">Has warranty</span>
                        <span class="block text-xs text-slate-400">Repair coverage duration</span>
                    </span>
                </label>
                <div class="mt-3 flex items-center gap-2" x-show="hasWarranty" x-cloak>
                    <x-text-input x-ref="warrantyPeriod" name="warranty_period" type="number" min="1" class="block w-24"
                                  :value="old('warranty_period', $product->warranty_period ?? '')" placeholder="12" />
                    <select name="warranty_unit" class="rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        @foreach (['days' => 'Days', 'months' => 'Months', 'years' => 'Years'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('warranty_unit', $product->warranty_unit ?? 'months') === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('warranty_period')" />
                <x-input-error class="mt-1" :messages="$errors->get('warranty_unit')" />
            </div>

            <!-- Guarantee -->
            <div class="rounded-lg border border-slate-200 p-3">
                <label class="flex items-start gap-3">
                    <input type="checkbox" x-model="hasGuarantee"
                           @change="if (!hasGuarantee) { $refs.guaranteePeriod.value = ''; }"
                           class="mt-0.5 rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                    <span>
                        <span class="block text-sm font-semibold text-brand-900">Has guarantee</span>
                        <span class="block text-xs text-slate-400">Replacement coverage duration</span>
                    </span>
                </label>
                <div class="mt-3 flex items-center gap-2" x-show="hasGuarantee" x-cloak>
                    <x-text-input x-ref="guaranteePeriod" name="guarantee_period" type="number" min="1" class="block w-24"
                                  :value="old('guarantee_period', $product->guarantee_period ?? '')" placeholder="6" />
                    <select name="guarantee_unit" class="rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                        @foreach (['days' => 'Days', 'months' => 'Months', 'years' => 'Years'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('guarantee_unit', $product->guarantee_unit ?? 'months') === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error class="mt-1" :messages="$errors->get('guarantee_period')" />
                <x-input-error class="mt-1" :messages="$errors->get('guarantee_unit')" />
            </div>
        </div>

        @include('admin.products._variants')
    </div>
```

> `@include('admin.products._variants')` is created in Task 7. Create an empty placeholder
> file now so the include resolves: `resources/views/admin/products/_variants.blade.php`
> containing a single blank line. Task 7 fills it in.

- [ ] **Step 4: Add `x-cloak` styling**

Confirm `[x-cloak]{display:none!important;}` exists in the app CSS (search `resources/css/app.css`). If absent, add it so `x-cloak` sections stay hidden until Alpine initializes:
```css
[x-cloak] { display: none !important; }
```

- [ ] **Step 5: Run the render test**

Run: `php artisan test --filter=test_create_form_shows_feature_controls`
Expected: PASS.

- [ ] **Step 6: Verify in the browser (preview)**

Start the dev server (preview_start with the app's dev config), open `/products/create`,
and confirm: the "Product Type & Features" card renders; checking **Track expiry date**
also checks and disables **Track batches**; checking **Has warranty** reveals the period +
unit inputs. Capture a screenshot for the record.

- [ ] **Step 7: Commit**

```bash
git add resources/views/admin/products/_form.blade.php resources/views/admin/products/_variants.blade.php resources/css/app.css tests/Feature/ProductFeatureFlagsTest.php
git commit -m "feat: product form features card for flags and warranty/guarantee"
```

---

## Task 7: Product form — variant builder (Alpine generate table)

**Files:**
- Modify: `resources/views/admin/products/_variants.blade.php` (replace placeholder)
- Modify: `resources/js/app.js` (register the `productForm` Alpine component)
- Test: assert variant builder scaffolding renders; end-to-end verify in browser.

**Interfaces:**
- Consumes: `attributes` (from `formOptions()`, Task 4) — each `Attribute` with `values`.
- Produces: variant rows submitted as `variants[i][sku|barcode|selling_price|estimated_cost|status]`
  and `variants[i][values][<attribute_id>] = <attribute_value_id>`, plus `variants[i][id]`
  on edit — exactly the shape Task 5 validates.

- [ ] **Step 1: Write the failing scaffolding test**

Add to `tests/Feature/ProductVariantTest.php`:
```php
    public function test_create_form_renders_variant_builder_for_attributes(): void
    {
        Category::create(['name' => 'General']);
        Unit::create(['name' => 'Piece', 'short_name' => 'pc']);
        $color = Attribute::create(['name' => 'Color']);
        $color->values()->create(['value' => 'Red']);

        $this->actingAs($this->admin())
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('Generate Variants', false)
            ->assertSee('data-attribute-id="'.$color->id.'"', false);
    }
```
(Add `use App\Models\User;` and `use Database\Seeders\RolePermissionSeeder;` if not already
imported, and the `admin()` helper from Task 5 must be present in this file.)

- [ ] **Step 2: Run to verify failure**

Run: `php artisan test --filter=test_create_form_renders_variant_builder_for_attributes`
Expected: FAIL — placeholder `_variants.blade.php` is empty.

- [ ] **Step 3: Fill in `_variants.blade.php`**

Replace the contents of `resources/views/admin/products/_variants.blade.php`:
```blade
{{-- Variant builder. Shown only when "Has variants" is checked. --}}
<div class="mt-6 border-t border-slate-100 pt-6" x-show="hasVariants" x-cloak>
    <h4 class="font-semibold text-brand-900">Variants</h4>
    <p class="text-xs text-slate-400 mb-4">Pick attributes and values, then generate the combinations. Each variant has its own price, SKU and barcode.</p>

    @if ($attributes->isEmpty())
        <p class="text-sm text-amber-600">No attributes yet — <a href="{{ route('attributes.index') }}" class="underline font-semibold">create Color/Size first</a>.</p>
    @else
        {{-- Attribute + value pickers --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($attributes as $attribute)
                <div class="rounded-lg border border-slate-200 p-3" data-attribute-id="{{ $attribute->id }}">
                    <p class="text-sm font-semibold text-brand-900">{{ $attribute->name }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($attribute->values as $value)
                            <label class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs">
                                <input type="checkbox"
                                       @change="toggleValue({{ $attribute->id }}, {{ $value->id }}, '{{ addslashes($value->value) }}', $event.target.checked)"
                                       class="rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                                {{ $value->value }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <button type="button" @click="generateVariants()"
                class="mt-4 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">Generate Variants</button>

        {{-- Generated variant rows --}}
        <div class="mt-4 overflow-x-auto" x-show="variants.length" x-cloak>
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-3">Variant</th>
                        <th class="py-2 pr-3">SKU</th>
                        <th class="py-2 pr-3">Barcode</th>
                        <th class="py-2 pr-3">Selling Price</th>
                        <th class="py-2 pr-3">Cost</th>
                        <th class="py-2 pr-3">Active</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(variant, i) in variants" :key="variant.key">
                        <tr class="border-t border-slate-100">
                            <td class="py-2 pr-3 font-medium text-brand-900" x-text="variant.label"></td>
                            <td class="py-2 pr-3">
                                <input type="hidden" :name="'variants[' + i + '][id]'" :value="variant.id || ''">
                                <template x-for="(valueId, attrId) in variant.values" :key="attrId">
                                    <input type="hidden" :name="'variants[' + i + '][values][' + attrId + ']'" :value="valueId">
                                </template>
                                <input type="text" :name="'variants[' + i + '][sku]'" x-model="variant.sku" required
                                       class="block w-32 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="text" :name="'variants[' + i + '][barcode]'" x-model="variant.barcode"
                                       class="block w-32 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="number" step="0.01" min="0" :name="'variants[' + i + '][selling_price]'" x-model="variant.selling_price" required
                                       class="block w-24 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="number" step="0.01" min="0" :name="'variants[' + i + '][estimated_cost]'" x-model="variant.estimated_cost"
                                       class="block w-24 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                            </td>
                            <td class="py-2 pr-3">
                                <input type="hidden" :name="'variants[' + i + '][status]'" :value="variant.status ? '1' : '0'">
                                <input type="checkbox" x-model="variant.status"
                                       class="rounded border-slate-300 text-accent-600 focus:ring-accent-500">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <x-input-error class="mt-2" :messages="$errors->get('variants')" />
        </div>
    @endif
</div>
```

- [ ] **Step 4: Register the `productForm` Alpine component**

`resources/js/app.js` registers components inline with `Alpine.data(...)` just before the
final `Alpine.start()` call (see the existing `barcodeForm` component, ~line 22). Follow the
same pattern — add this registration immediately after the `barcodeForm` block and BEFORE
`window.Alpine = Alpine;` / `Alpine.start();`:
```js
Alpine.data('productForm', (initial) => ({
        ...initial,
        // { [attributeId]: { [valueId]: label } } — selected values per attribute
        selected: {},
        // Existing variants (edit mode) injected from the server, if any
        variants: window.__existingVariants || [],

        toggleValue(attrId, valueId, label, checked) {
            this.selected[attrId] = this.selected[attrId] || {};
            if (checked) {
                this.selected[attrId][valueId] = label;
            } else {
                delete this.selected[attrId][valueId];
                if (Object.keys(this.selected[attrId]).length === 0) delete this.selected[attrId];
            }
        },

        generateVariants() {
            const attrIds = Object.keys(this.selected);
            if (attrIds.length === 0) { this.variants = []; return; }

            // Cartesian product of selected values across attributes.
            let combos = [{ values: {}, labels: [] }];
            attrIds.forEach((attrId) => {
                const next = [];
                Object.entries(this.selected[attrId]).forEach(([valueId, label]) => {
                    combos.forEach((combo) => {
                        next.push({
                            values: { ...combo.values, [attrId]: Number(valueId) },
                            labels: [...combo.labels, label],
                        });
                    });
                });
                combos = next;
            });

            // Merge: keep existing rows whose value-combo matches; add new ones.
            const keyOf = (values) => Object.keys(values).sort().map((k) => k + ':' + values[k]).join('|');
            const existingByKey = {};
            this.variants.forEach((v) => { existingByKey[keyOf(v.values)] = v; });

            this.variants = combos.map((combo) => {
                const key = keyOf(combo.values);
                if (existingByKey[key]) return existingByKey[key];
                return {
                    key,
                    id: null,
                    label: combo.labels.join(' / '),
                    values: combo.values,
                    sku: '',
                    barcode: '',
                    selling_price: this.$root.querySelector('#selling_price')?.value || '',
                    estimated_cost: '',
                    status: true,
                };
            });
        },
    }));
```

> **Edit-mode preload (optional but recommended):** in `_form.blade.php`, just before the
> closing `</form>`-adjacent script area, emit existing variants so edit mode shows them:
> ```blade
> @if (($product ?? null) && $product->has_variants)
>     <script>
>         window.__existingVariants = @json($product->variants->map(fn ($v) => [
>             'key' => $v->attributeValues->map(fn ($av) => $av->pivot->attribute_id.':'.$av->id)->sort()->implode('|'),
>             'id' => $v->id,
>             'label' => $v->label,
>             'values' => $v->attributeValues->mapWithKeys(fn ($av) => [$av->pivot->attribute_id => $av->id]),
>             'sku' => $v->sku,
>             'barcode' => $v->barcode,
>             'selling_price' => $v->selling_price,
>             'estimated_cost' => $v->estimated_cost,
>             'status' => (bool) $v->status,
>         ]));
>     </script>
> @endif
> ```
> Eager-load in `edit()`: change `ProductController::edit()` to
> `$product->load('variants.attributeValues');` before passing to the view.

- [ ] **Step 5: Build assets**

Run: `npm run build`
Expected: build succeeds (Vite compiles `app.js`).

- [ ] **Step 6: Run the scaffolding test**

Run: `php artisan test --filter=test_create_form_renders_variant_builder_for_attributes`
Expected: PASS.

- [ ] **Step 7: End-to-end browser verification (preview)**

Start the dev server, open `/products/create`:
1. Check **Has variants** → variant section appears.
2. Select Color=Red,Blue and Size=S,M → click **Generate Variants** → a 4-row table
   (Red/S, Red/M, Blue/S, Blue/M) appears; edit prices/SKUs.
3. Fill the base fields, submit, and confirm the product + 4 variants persist (check the
   index or DB). Capture a screenshot.
4. Open the product's edit page → the 4 variants prefill; change one value selection and
   regenerate → untouched rows keep their entered prices.

- [ ] **Step 8: Run the full suite**

Run: `php artisan test`
Expected: PASS.

- [ ] **Step 9: Commit**

```bash
git add resources/views/admin/products/_variants.blade.php resources/views/admin/products/_form.blade.php resources/js/app.js app/Http/Controllers/Admin/ProductController.php tests/Feature/ProductVariantTest.php
git commit -m "feat: variant builder UI with cartesian generation and edit preload"
```

---

## Self-Review Notes

- **Spec coverage:** Global attributes + values (Tasks 1–2); per-product variants with full
  price/SKU/barcode (Tasks 3, 5, 7); feature flags batch/expiry/serial as toggles (Tasks 3,
  4, 6); warranty/guarantee durations (Tasks 3, 4, 6); expiry-implies-batch enforced
  server-side (Task 4) and mirrored in UI (Task 6); attributes admin under `inventory.*`
  (Task 2). All spec sections map to tasks.
- **Deferred (per spec):** Purchase/Production/Stock; variants on barcode-print page;
  cross-table SKU/barcode uniqueness — not in this plan by design.
- **Type consistency:** variant payload shape `variants[i][sku|barcode|selling_price|
  estimated_cost|status|id|values[attr]=value]` is identical across Task 5 (validation),
  Task 5 (`syncVariants`), and Task 7 (form fields). `ProductVariant::attributeValues()`
  `withPivot('attribute_id')` is used consistently by the label accessor and the edit
  preload.
- **Known follow-up within-plan:** Task 6 introduces `productForm(...)` in `x-data` but the
  component is registered in Task 7 — both land in the same branch/PR, so do not ship Task 6
  alone.
