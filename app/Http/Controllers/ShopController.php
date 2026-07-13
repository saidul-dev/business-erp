<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\DeliveryZone;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * The e-commerce storefront: catalog, product detail, cart, guest
 * checkout, order confirmation and order tracking. Placing an order simply
 * creates a Sale (channel='online') — every downstream step (delivery,
 * courier, ledger) is the existing admin Sales pipeline, untouched here.
 */
class ShopController extends Controller
{
    public function index(Request $request)
    {
        $company = CompanySetting::current();

        $categories = Category::whereNull('parent_id')->where('status', true)
            ->withCount('products')
            ->orderBy('name')->get();

        $brands = Brand::where('status', true)
            ->whereHas('products', fn ($q) => $q->where('status', true))
            ->orderBy('name')->get();

        $categoryId = $request->filled('category_id') ? $request->integer('category_id') : null;
        $brandId = $request->filled('brand_id') ? $request->integer('brand_id') : null;
        $q = $request->get('q');
        $minPrice = $request->filled('min_price') ? (float) $request->get('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->get('max_price') : null;
        $sort = in_array($request->get('sort'), ['price_asc', 'price_desc', 'newest'], true) ? $request->get('sort') : 'newest';

        $products = Product::with(['category', 'brand', 'variants' => fn ($q) => $q->where('status', true)])
            ->where('status', true)
            ->when($categoryId, fn ($qr) => $qr->where('category_id', $categoryId))
            ->when($brandId, fn ($qr) => $qr->where('brand_id', $brandId))
            ->when($q, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('name', 'like', "%{$q}%")
                ->orWhere('sku', 'like', "%{$q}%")
            ))
            // A variable product's real price lives on its variants, not
            // its own selling_price — so the range filter matches on
            // either the product's own price or any active variant's.
            ->when($minPrice !== null, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('selling_price', '>=', $minPrice)
                ->orWhereHas('variants', fn ($v) => $v->where('status', true)->where('selling_price', '>=', $minPrice))
            ))
            ->when($maxPrice !== null, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('selling_price', '<=', $maxPrice)
                ->orWhereHas('variants', fn ($v) => $v->where('status', true)->where('selling_price', '<=', $maxPrice))
            ))
            ->when($sort === 'price_asc', fn ($qr) => $qr->orderBy('selling_price'))
            ->when($sort === 'price_desc', fn ($qr) => $qr->orderByDesc('selling_price'))
            ->when($sort === 'newest', fn ($qr) => $qr->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString();

        $stockBalances = $this->stockBalances($products->getCollection());

        $products->getCollection()->each(function (Product $product) use ($stockBalances) {
            $product->in_stock = ($stockBalances[$product->id] ?? 0) >= 1;
            [$product->price_from, $product->price_to] = $this->priceRange($product);
        });

        return view('website.shop', compact(
            'company', 'categories', 'brands', 'products',
            'categoryId', 'brandId', 'q', 'minPrice', 'maxPrice', 'sort'
        ));
    }

    public function show(Product $product)
    {
        abort_unless($product->status, 404);

        $product->load(['category', 'brand', 'stockUnit', 'images', 'variants' => fn ($q) => $q
            ->where('status', true)->with('attributeValues.attribute')]);

        $stockBalances = $this->stockBalances(collect([$product]));
        $inStock = ($stockBalances[$product->id] ?? 0) >= 1;

        $variantOptions = collect();

        if ($product->has_variants) {
            $variantBalances = $this->variantStockBalances($product->variants->pluck('id'));

            $variantOptions = $product->variants->map(fn (ProductVariant $variant) => [
                'id' => "variant-{$variant->id}",
                'label' => $variant->label,
                'price' => (float) ($variant->selling_price ?? $product->selling_price),
                'in_stock' => ($variantBalances[$variant->id] ?? 0) >= 1,
            ]);
        }

        $relatedProducts = $product->category_id
            ? Product::where('status', true)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->orderByDesc('id')
                ->limit(8)
                ->get()
            : collect();

        return view('website.product', compact('product', 'inStock', 'variantOptions', 'relatedProducts'));
    }

    public function cart()
    {
        $cart = new Cart;

        return view('website.cart', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'item' => ['required', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        [$product] = $this->resolveItem($validated['item']);

        if (! $product) {
            return back()->with('error', 'That product is no longer available.');
        }

        (new Cart)->add($validated['item'], (float) $validated['quantity']);

        return back()->with('success', 'Added to cart.');
    }

    public function updateCart(Request $request, string $itemKey)
    {
        $validated = $request->validate(['quantity' => ['required', 'numeric', 'min:0']]);

        (new Cart)->update($itemKey, (float) $validated['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function removeFromCart(string $itemKey)
    {
        (new Cart)->remove($itemKey);

        return back()->with('success', 'Item removed from cart.');
    }

    public function checkout()
    {
        $cart = new Cart;

        if ($cart->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        return view('website.checkout', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
            'company' => CompanySetting::current(),
            'deliveryZones' => DeliveryZone::where('status', true)->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    /**
     * Creates the order as a Sale (channel='online', status='pending') —
     * identical shape to a POS sale, just with no admin item-picker and a
     * guest customer resolved by phone instead of a picked Party. Nothing
     * is delivered/posted to the ledger yet; that happens exactly as it
     * does for a POS sale, from Admin > Online Orders.
     */
    public function placeOrder(Request $request)
    {
        $cart = new Cart;

        if ($cart->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $company = CompanySetting::current();

        if (! $company->online_site_id) {
            return back()->with('error', 'Sorry, the online store is not accepting orders right now. Please contact us directly.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'delivery_zone_id' => ['nullable', 'integer', Rule::exists('delivery_zones', 'id')->where('status', true)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $deliveryZone = isset($validated['delivery_zone_id'])
            ? DeliveryZone::find($validated['delivery_zone_id'])
            : null;

        $sale = DB::transaction(function () use ($validated, $lines, $company, $deliveryZone) {
            $party = Party::firstOrCreate(
                ['phone' => $validated['phone']],
                ['name' => $validated['name'], 'address' => $validated['address'], 'is_customer' => true]
            );

            // A phone match on a record that wasn't flagged as a customer
            // yet (e.g. a supplier-only Party) just gains the role.
            if (! $party->is_customer) {
                $party->update(['is_customer' => true]);
            }

            $subtotal = round((float) $lines->sum('subtotal'), 2);

            $sale = Sale::create([
                'sale_no' => 'PENDING',
                'party_id' => $party->id,
                'site_id' => $company->online_site_id,
                'status' => 'pending',
                'channel' => 'online',
                'shipping_name' => $validated['name'],
                'shipping_phone' => $validated['phone'],
                'shipping_address' => $validated['address'],
                // Snapshotted for the admin/courier's reference only — see
                // the delivery_zone_name/delivery_charge migration for why
                // this never touches subtotal_amount/total_amount.
                'delivery_zone_name' => $deliveryZone?->name,
                'delivery_charge' => $deliveryZone?->charge,
                'order_date' => now()->toDateString(),
                'note' => $validated['note'] ?? null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                'total_amount' => $subtotal,
                'created_by' => null,
            ]);
            $sale->update(['sale_no' => 'SO-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($lines as $row) {
                $sale->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }

            return $sale;
        });

        $cart->clear();
        session(['last_order_id' => $sale->id]);

        return redirect()->route('order.confirmation', $sale)->with('success', "Order {$sale->sale_no} placed!");
    }

    /**
     * Gated to the session flag set right after placeOrder() so an order
     * number alone (easy to guess sequentially) can't be used to browse
     * someone else's order — see track() for the intentional phone-gated
     * alternative for returning later.
     */
    public function confirmation(Sale $sale)
    {
        abort_unless($sale->channel === 'online' && session('last_order_id') === $sale->id, 404);

        $sale->load(['items.product', 'items.productVariant.attributeValues', 'site']);

        return view('website.order-confirmation', compact('sale'));
    }

    public function trackForm()
    {
        return view('website.track-order');
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'sale_no' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $sale = Sale::where('sale_no', $validated['sale_no'])
            ->where('shipping_phone', $validated['phone'])
            ->where('channel', 'online')
            ->with(['items.product', 'items.productVariant.attributeValues', 'deliveries.consignment.deliveryPartner', 'site'])
            ->first();

        if (! $sale) {
            throw ValidationException::withMessages(['sale_no' => "No online order found matching that order number and phone."]);
        }

        return view('website.track-order', ['sale' => $sale]);
    }

    /**
     * Split an "item" picker value ("product-{id}" / "variant-{id}") back
     * into its Product (and ProductVariant when it's a variant) — same
     * convention SaleController::resolveItem() uses.
     */
    protected function resolveItem(?string $item): array
    {
        if (! $item || ! str_contains($item, '-')) {
            return [null, null];
        }

        [$kind, $id] = explode('-', $item, 2);

        if ($kind === 'product') {
            $product = Product::where('status', true)->where('has_variants', false)->find($id);

            return [$product, null];
        }

        if ($kind === 'variant') {
            $variant = ProductVariant::with('product')->where('status', true)->find($id);

            return [$variant?->product, $variant];
        }

        return [null, null];
    }

    /**
     * Combined stock across every Site (not one branch's balance) — this
     * is only ever used to decide the catalog's "In Stock"/"Out of Stock"
     * badge, never to block placing an order (checked at deliver() time,
     * same as a POS sale).
     */
    protected function stockBalances(Collection $products): array
    {
        $simpleIds = $products->where('has_variants', false)->pluck('id');
        $variantIds = $products->where('has_variants', true)->flatMap(fn (Product $p) => $p->variants->pluck('id'));

        $simpleBalances = StockMovement::whereIn('product_id', $simpleIds)
            ->whereNull('product_variant_id')
            ->selectRaw("product_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
            ->groupBy('product_id')
            ->pluck('balance', 'product_id');

        $variantBalances = $this->variantStockBalances($variantIds);

        $result = [];
        foreach ($products as $product) {
            $result[$product->id] = $product->has_variants
                ? (float) $product->variants->sum(fn (ProductVariant $v) => (float) ($variantBalances[$v->id] ?? 0))
                : (float) ($simpleBalances[$product->id] ?? 0);
        }

        return $result;
    }

    protected function variantStockBalances(Collection $variantIds): Collection
    {
        if ($variantIds->isEmpty()) {
            return collect();
        }

        return StockMovement::whereIn('product_variant_id', $variantIds)
            ->selectRaw("product_variant_id, SUM(CASE WHEN direction = 'in' THEN quantity ELSE -quantity END) as balance")
            ->groupBy('product_variant_id')
            ->pluck('balance', 'product_variant_id');
    }

    /**
     * [min, max] selling price across a variable product's active
     * variants (falling back to the parent's own price for a variant that
     * doesn't override it), or just the product's own price for a simple
     * product.
     */
    protected function priceRange(Product $product): array
    {
        if (! $product->has_variants || $product->variants->isEmpty()) {
            return [(float) $product->selling_price, (float) $product->selling_price];
        }

        $prices = $product->variants->map(fn (ProductVariant $v) => (float) ($v->selling_price ?? $product->selling_price));

        return [$prices->min(), $prices->max()];
    }
}
