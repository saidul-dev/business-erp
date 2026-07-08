<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // The product_variant_values table is created in a later task; guard
        // against its absence so this controller works standalone until then.
        $used = Schema::hasTable('product_variant_values')
            && DB::table('product_variant_values')->where('attribute_id', $attribute->id)->exists();

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
