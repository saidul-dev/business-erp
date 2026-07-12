<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed guest cart for the storefront — no DB table, no login.
 * Only `{"product-5": 2, "variant-12": 1}`-style quantities are persisted;
 * name/price/image/stock are always resolved fresh from the DB in lines(),
 * the same "product-{id}"/"variant-{id}" key convention SaleController's
 * item picker uses.
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    public function add(string $itemKey, float $quantity): void
    {
        $items = $this->raw();
        $items[$itemKey] = ($items[$itemKey] ?? 0) + $quantity;
        $this->save($items);
    }

    public function update(string $itemKey, float $quantity): void
    {
        $items = $this->raw();

        if ($quantity <= 0) {
            unset($items[$itemKey]);
        } else {
            $items[$itemKey] = $quantity;
        }

        $this->save($items);
    }

    public function remove(string $itemKey): void
    {
        $items = $this->raw();
        unset($items[$itemKey]);
        $this->save($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return empty($this->raw());
    }

    public function count(): int
    {
        return (int) array_sum($this->raw());
    }

    /**
     * Every line resolved against the DB right now. A key pointing at a
     * product/variant that's been deleted or deactivated since it was
     * added is silently dropped rather than erroring the whole cart.
     */
    public function lines(): Collection
    {
        $items = $this->raw();

        if (empty($items)) {
            return collect();
        }

        $productIds = [];
        $variantIds = [];

        foreach (array_keys($items) as $key) {
            [$kind, $id] = array_pad(explode('-', $key, 2), 2, null);

            if ($kind === 'product') {
                $productIds[] = $id;
            } elseif ($kind === 'variant') {
                $variantIds[] = $id;
            }
        }

        $products = Product::where('status', true)->whereIn('id', $productIds)->get()->keyBy('id');
        $variants = ProductVariant::with(['product', 'attributeValues'])
            ->where('status', true)->whereIn('id', $variantIds)->get()->keyBy('id');

        $lines = collect();

        foreach ($items as $key => $quantity) {
            [$kind, $id] = explode('-', $key, 2);
            $quantity = (float) $quantity;

            if ($kind === 'product') {
                $product = $products->get($id);

                if (! $product || $product->has_variants) {
                    continue;
                }

                $price = (float) $product->selling_price;

                $lines->push([
                    'key' => $key,
                    'product' => $product,
                    'variant' => null,
                    'name' => $product->name,
                    'image_url' => $product->image_url,
                    'unit_price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => round($price * $quantity, 2),
                ]);
            } elseif ($kind === 'variant') {
                $variant = $variants->get($id);

                if (! $variant || ! $variant->product) {
                    continue;
                }

                $price = (float) ($variant->selling_price ?? $variant->product->selling_price);

                $lines->push([
                    'key' => $key,
                    'product' => $variant->product,
                    'variant' => $variant,
                    'name' => "{$variant->product->name} — {$variant->label}",
                    'image_url' => $variant->product->image_url,
                    'unit_price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => round($price * $quantity, 2),
                ]);
            }
        }

        return $lines;
    }

    public function subtotal(): float
    {
        return round((float) $this->lines()->sum('subtotal'), 2);
    }

    private function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    private function save(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
