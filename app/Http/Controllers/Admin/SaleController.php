<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\CourierConsignment;
use App\Models\DeliveryPartner;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleDelivery;
use App\Models\SaleReturn;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales.view', only: ['index', 'show', 'printOrder', 'printDelivery', 'printReturn', 'manual']),
            new Middleware('permission:sales.create', only: ['create', 'store']),
            new Middleware('permission:sales.approve', only: ['deliverForm', 'deliver', 'returnForm', 'storeReturn']),
            new Middleware('permission:sales.edit', only: ['edit', 'update', 'cancel']),
        ];
    }

    public function manual()
    {
        return view('admin.sales.manual');
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $status = $request->get('status');
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $channel = $request->get('channel');
        $from = $request->get('from');
        $to = $request->get('to');
        $q = $request->get('q');

        $sales = Sale::with(['party', 'site'])
            ->when($siteId, fn ($qr) => $qr->where('site_id', $siteId))
            ->when($status, fn ($qr) => $qr->where('status', $status))
            ->when($channel, fn ($qr) => $qr->where('channel', $channel))
            ->when($from, fn ($qr) => $qr->whereDate('order_date', '>=', $from))
            ->when($to, fn ($qr) => $qr->whereDate('order_date', '<=', $to))
            ->when($q, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('sale_no', 'like', "%{$q}%")
                ->orWhereHas('party', fn ($qp) => $qp->where('name', 'like', "%{$q}%"))
            ))
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sales.index', compact('sales', 'sites', 'status', 'siteId', 'channel', 'from', 'to', 'q'));
    }

    /**
     * Order form: Customer + Site, then a cart-style item picker (search +
     * add) sourced from every active product/variant, prefilling unit price
     * from Product/Variant `selling_price` — no stock-balance filter, since
     * ordering doesn't require checking stock (checked at deliver() time).
     */
    public function create()
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        return view('admin.sales.create', compact('sites', 'customers', 'itemOptions'));
    }

    /**
     * Creates the Sales Order only — no stock or ledger effect yet. Those
     * happen per-delivery (see deliver()), since revenue/COGS follow what's
     * actually shipped, not what was ordered.
     */
    public function store(Request $request)
    {
        [$validated, $rows, $subtotal, $discount] = $this->validateOrderRequest($request);

        $sale = DB::transaction(function () use ($validated, $rows, $subtotal, $discount) {
            $sale = Sale::create([
                'sale_no' => 'PENDING',
                'party_id' => $validated['party_id'],
                'site_id' => $validated['site_id'],
                'status' => 'pending',
                'order_date' => $validated['order_date'],
                'note' => $validated['note'] ?? null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $subtotal - $discount,
                'created_by' => Auth::id(),
            ]);
            $sale->update(['sale_no' => 'SO-'.str_pad($sale->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($rows as $row) {
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

        return redirect()->route('sales.show', $sale)
            ->with('success', "Sale {$sale->sale_no} created — ".count($rows).' item(s).');
    }

    /**
     * Only open while nothing has been delivered yet — status stays
     * 'pending' until the first deliver() call, at which point
     * Sale::recalculateStatus() moves it to 'partial'/'delivered' and this
     * becomes a dead end, since a stock_movements/ledger row would already
     * reference the old item quantities.
     */
    public function edit(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return redirect()->route('sales.show', $sale)->with('error', 'Only a pending sale (nothing delivered yet) can be edited.');
        }

        $sale->load(['items.product.stockUnit', 'items.productVariant.attributeValues']);

        $sites = Site::where('status', true)->orderBy('name')->get();
        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        $items = $sale->items->map(fn ($item) => [
            'id' => $item->product_variant_id ? "variant-{$item->product_variant_id}" : "product-{$item->product_id}",
            'name' => $item->productVariant
                ? "{$item->product->name} — {$item->productVariant->label} ({$item->productVariant->sku})"
                : "{$item->product->name} ({$item->product->sku})",
            'unit' => $item->product->stockUnit?->short_name,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
        ])->values();

        return view('admin.sales.edit', compact('sale', 'sites', 'customers', 'itemOptions', 'items'));
    }

    /**
     * Replaces every line wholesale rather than diffing — safe only because
     * edit() already gates on status === 'pending', which guarantees zero
     * deliveries exist to reference the old sale_item rows.
     */
    public function update(Request $request, Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return back()->with('error', 'Only a pending sale (nothing delivered yet) can be edited.');
        }

        [$validated, $rows, $subtotal, $discount] = $this->validateOrderRequest($request);

        DB::transaction(function () use ($sale, $validated, $rows, $subtotal, $discount) {
            $sale->update([
                'party_id' => $validated['party_id'],
                'site_id' => $validated['site_id'],
                'order_date' => $validated['order_date'],
                'note' => $validated['note'] ?? null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $subtotal - $discount,
            ]);

            $sale->items()->delete();

            foreach ($rows as $row) {
                $sale->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }
        });

        return redirect()->route('sales.show', $sale)->with('success', "Sale {$sale->sale_no} updated.");
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'party', 'site', 'creator',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
            'deliveries.deliveredBy', 'deliveries.items.saleItem.product', 'deliveries.consignment.deliveryPartner',
            'returns.returnedBy', 'returns.items.saleItem.product',
        ]);

        return view('admin.sales.show', compact('sale'));
    }

    public function printOrder(Sale $sale)
    {
        $sale->load([
            'party', 'site', 'creator',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.sales.print', compact('sale', 'company'));
    }

    public function printDelivery(SaleDelivery $delivery)
    {
        $delivery->load([
            'sale.party', 'sale.site', 'deliveredBy',
            'items.saleItem.product.stockUnit', 'items.saleItem.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.sales.delivery-print', compact('delivery', 'company'));
    }

    /**
     * Delivery form: every line still has a remaining quantity to fill in
     * — leaving a line at 0 simply skips it, so the same form covers both
     * a full delivery and a partial one.
     */
    public function deliverForm(Sale $sale)
    {
        if (! in_array($sale->status, ['pending', 'partial'], true)) {
            return redirect()->route('sales.show', $sale)->with('error', 'This sale is not open to deliver.');
        }

        $sale->load(['party', 'site', 'items.product.stockUnit', 'items.productVariant.attributeValues']);
        $deliveryPartners = DeliveryPartner::where('status', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.sales.deliver', compact('sale', 'deliveryPartners'));
    }

    /**
     * Posts everything checked in on this delivery pass: one
     * stock_movements row per line (goods physically leave) plus exactly
     * one ledger_transactions entry for the whole delivery — Dr Accounts
     * Receivable + Dr Discount Allowed / Cr Sales Revenue (the discount
     * prorated onto this delivery's share of the order, see
     * Sale::discountRate()), and Dr Cost of Goods Sold / Cr Inventory
     * valued at each product's current average cost — via
     * LedgerService::post(). Quantities not delivered now stay open for a
     * later delivery. See docs/accounting-foundation.md.
     */
    public function deliver(Request $request, Sale $sale)
    {
        if (! in_array($sale->status, ['pending', 'partial'], true)) {
            return back()->with('error', 'This sale is not open to deliver.');
        }

        $validated = $request->validate([
            'delivered_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'fulfillment_type' => ['required', Rule::in(SaleDelivery::FULFILLMENT_TYPES)],
            'delivery_partner_id' => ['required_if:fulfillment_type,courier', 'nullable', 'integer', 'exists:delivery_partners,id'],
            'cod_amount' => ['nullable', 'numeric', 'min:0'],
            'tracking_no' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_no' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.serial_no' => ['nullable', 'string', 'max:100'],
        ]);

        $sale->load('items.product');

        $lines = [];
        foreach ($validated['items'] as $saleItemId => $row) {
            $quantity = (float) ($row['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $item = $sale->items->firstWhere('id', (int) $saleItemId);

            if (! $item) {
                throw ValidationException::withMessages(["items.{$saleItemId}" => 'Invalid item.']);
            }

            if ($quantity > $item->remaining() + 0.00001) {
                throw ValidationException::withMessages([
                    "items.{$saleItemId}.quantity" => "{$item->product->name}: cannot deliver more than the remaining {$item->remaining()}.",
                ]);
            }

            $lines[] = [
                'item' => $item,
                'quantity' => $quantity,
                'batch_no' => $row['batch_no'] ?? null,
                'expiry_date' => $row['expiry_date'] ?? null,
                'serial_no' => $row['serial_no'] ?? null,
            ];
        }

        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Enter a quantity for at least one item.']);
        }

        DB::transaction(function () use ($sale, $validated, $lines) {
            $delivery = SaleDelivery::create([
                'sale_id' => $sale->id,
                'delivery_no' => 'PENDING',
                'fulfillment_type' => $validated['fulfillment_type'],
                'delivered_date' => $validated['delivered_date'],
                'note' => $validated['note'] ?? null,
                'delivered_by' => Auth::id(),
            ]);
            $delivery->update(['delivery_no' => 'INV-'.str_pad($delivery->id, 6, '0', STR_PAD_LEFT)]);

            if ($validated['fulfillment_type'] === 'courier') {
                CourierConsignment::create([
                    'sale_delivery_id' => $delivery->id,
                    'delivery_partner_id' => $validated['delivery_partner_id'],
                    'tracking_no' => $validated['tracking_no'] ?? null,
                    'cod_amount' => $validated['cod_amount'] ?? 0,
                    'status' => 'booked',
                    'booked_by' => Auth::id(),
                ]);
            }

            $grossRevenue = 0;
            $cogsValue = 0;

            foreach ($lines as $row) {
                $item = $row['item'];
                $avgCost = (float) ($item->productVariant?->estimated_cost ?? $item->product->estimated_cost);

                $delivery->items()->create([
                    'sale_item_id' => $item->id,
                    'quantity' => $row['quantity'],
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                ]);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'site_id' => $sale->site_id,
                    'type' => 'sale',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $avgCost,
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                    'moved_at' => $validated['delivered_date'],
                    'created_by' => Auth::id(),
                    'reference_type' => SaleDelivery::class,
                    'reference_id' => $delivery->id,
                ]);

                $item->update(['delivered_quantity' => $item->delivered_quantity + $row['quantity']]);

                $grossRevenue += round($row['quantity'] * $item->unit_price, 2);
                $cogsValue += round($row['quantity'] * $avgCost, 2);
            }

            $discountForDelivery = round($grossRevenue * $sale->discountRate(), 2);
            $netReceivable = round($grossRevenue - $discountForDelivery, 2);

            $revenueLines = [
                ['account' => 'accounts_receivable', 'debit' => $netReceivable, 'party_id' => $sale->party_id],
            ];

            if ($discountForDelivery > 0) {
                $revenueLines[] = ['account' => 'discount_allowed', 'debit' => $discountForDelivery];
            }

            $revenueLines[] = ['account' => 'sales_revenue', 'credit' => $grossRevenue];
            $revenueLines[] = ['account' => 'cost_of_goods_sold', 'debit' => $cogsValue];
            $revenueLines[] = ['account' => 'inventory', 'credit' => $cogsValue];

            LedgerService::post([
                'type' => 'sale',
                'date' => $validated['delivered_date'],
                'site_id' => $sale->site_id,
                'narration' => "Goods delivered against {$sale->sale_no}",
                'reference' => $delivery,
                'created_by' => Auth::id(),
                'lines' => $revenueLines,
            ]);

            $sale->recalculateStatus();
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Goods delivered.');
    }

    public function printReturn(SaleReturn $saleReturn)
    {
        $saleReturn->load([
            'sale.party', 'sale.site', 'returnedBy',
            'items.saleItem.product.stockUnit', 'items.saleItem.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.sales.return-print', ['saleReturn' => $saleReturn, 'company' => $company]);
    }

    /**
     * Return form: every line shows how much is still eligible to accept
     * back (delivered so far, minus what's already been returned) — same
     * "leave a line at 0 to skip it" convention as deliverForm().
     */
    public function returnForm(Sale $sale)
    {
        $sale->load(['party', 'site', 'items.product.stockUnit', 'items.productVariant.attributeValues']);

        if ($sale->items->sum(fn ($item) => $item->returnable()) <= 0) {
            return redirect()->route('sales.show', $sale)->with('error', 'Nothing on this sale is eligible to return.');
        }

        return view('admin.sales.return', compact('sale'));
    }

    /**
     * Posts everything checked in on this return pass: one
     * stock_movements row per line (`sales_return`, goods physically come
     * back) plus exactly one ledger_transactions entry for the whole
     * return — Dr Sales Revenue / Cr Accounts Receivable (reversing the
     * revenue at the original unit_price) and Dr Inventory / Cr Cost of
     * Goods Sold (valued at the *current* average cost, not necessarily
     * what was booked at delivery time — a known simplification, same
     * spirit as Party::postOpeningBalanceToLedger()'s documented gaps).
     * The discount originally prorated onto the sale is deliberately not
     * reversed here, to keep this step simple; only the gross revenue and
     * COGS are unwound. See docs/accounting-foundation.md.
     */
    public function storeReturn(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'return_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_no' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.serial_no' => ['nullable', 'string', 'max:100'],
        ]);

        $sale->load('items.product');

        $lines = [];
        foreach ($validated['items'] as $saleItemId => $row) {
            $quantity = (float) ($row['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $item = $sale->items->firstWhere('id', (int) $saleItemId);

            if (! $item) {
                throw ValidationException::withMessages(["items.{$saleItemId}" => 'Invalid item.']);
            }

            if ($quantity > $item->returnable() + 0.00001) {
                throw ValidationException::withMessages([
                    "items.{$saleItemId}.quantity" => "{$item->product->name}: cannot return more than the returnable {$item->returnable()}.",
                ]);
            }

            $lines[] = [
                'item' => $item,
                'quantity' => $quantity,
                'batch_no' => $row['batch_no'] ?? null,
                'expiry_date' => $row['expiry_date'] ?? null,
                'serial_no' => $row['serial_no'] ?? null,
            ];
        }

        if (empty($lines)) {
            throw ValidationException::withMessages(['items' => 'Enter a quantity for at least one item.']);
        }

        DB::transaction(function () use ($sale, $validated, $lines) {
            $return = SaleReturn::create([
                'sale_id' => $sale->id,
                'return_no' => 'PENDING',
                'return_date' => $validated['return_date'],
                'note' => $validated['note'] ?? null,
                'returned_by' => Auth::id(),
            ]);
            $return->update(['return_no' => 'SRN-'.str_pad($return->id, 6, '0', STR_PAD_LEFT)]);

            $revenueValue = 0;
            $cogsValue = 0;

            foreach ($lines as $row) {
                $item = $row['item'];
                $avgCost = (float) ($item->productVariant?->estimated_cost ?? $item->product->estimated_cost);

                $return->items()->create([
                    'sale_item_id' => $item->id,
                    'quantity' => $row['quantity'],
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                ]);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'site_id' => $sale->site_id,
                    'type' => 'sales_return',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $avgCost,
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                    'moved_at' => $validated['return_date'],
                    'created_by' => Auth::id(),
                    'reference_type' => SaleReturn::class,
                    'reference_id' => $return->id,
                ]);

                $item->update(['returned_quantity' => $item->returned_quantity + $row['quantity']]);

                $revenueValue += round($row['quantity'] * $item->unit_price, 2);
                $cogsValue += round($row['quantity'] * $avgCost, 2);
            }

            LedgerService::post([
                'type' => 'sales_return',
                'date' => $validated['return_date'],
                'site_id' => $sale->site_id,
                'narration' => "Goods returned against {$sale->sale_no}",
                'reference' => $return,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => 'sales_revenue', 'debit' => $revenueValue],
                    ['account' => 'accounts_receivable', 'credit' => $revenueValue, 'party_id' => $sale->party_id],
                    ['account' => 'inventory', 'debit' => $cogsValue],
                    ['account' => 'cost_of_goods_sold', 'credit' => $cogsValue],
                ],
            ]);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Return posted.');
    }

    /**
     * Only ever cancels the *remaining* (un-delivered) portion — nothing
     * needs reversing since nothing was posted for it. Whatever was
     * already delivered via earlier deliveries stands.
     */
    public function cancel(Sale $sale)
    {
        if (! in_array($sale->status, ['pending', 'partial'], true)) {
            return back()->with('error', 'Only a pending or partially delivered sale can be cancelled.');
        }

        $sale->update(['status' => 'cancelled']);

        return back()->with('success', "Sale {$sale->sale_no} cancelled.");
    }

    /**
     * Every active simple product / variant, as options for the cart
     * builder's item picker — no stock-balance filter (checked instead at
     * deliver() time), and price defaults from `selling_price`.
     */
    protected function itemOptions(): Collection
    {
        $products = Product::with('stockUnit')
            ->where('status', true)->where('has_variants', false)->orderBy('name')->get();

        $variants = ProductVariant::with(['product.stockUnit', 'attributeValues'])
            ->where('status', true)
            ->whereHas('product', fn ($q) => $q->where('status', true)->where('has_variants', true))
            ->get();

        $options = collect();

        foreach ($products as $p) {
            $options->push([
                'id' => "product-{$p->id}",
                'name' => "{$p->name} ({$p->sku})",
                'unit' => $p->stockUnit?->short_name,
                'price' => (float) $p->selling_price,
            ]);
        }

        foreach ($variants as $v) {
            $options->push([
                'id' => "variant-{$v->id}",
                'name' => "{$v->product->name} — {$v->label} ({$v->sku})",
                'unit' => $v->product->stockUnit?->short_name,
                'price' => (float) ($v->selling_price ?? $v->product->selling_price),
            ]);
        }

        return $options->sortBy('name')->values();
    }

    /**
     * Split the "product-{id}"/"variant-{id}" picker value back into its
     * parent Product (and ProductVariant, when it's a variant row).
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
     * Shared by store() and update(): validates the order form, resolves
     * each picker row back into a Product/ProductVariant, and computes the
     * subtotal/discount/total. Returns [$validated, $rows, $subtotal, $discount].
     */
    protected function validateOrderRequest(Request $request): array
    {
        $validated = $request->validate([
            'party_id' => ['required', 'integer', 'exists:parties,id'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'order_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $party = Party::findOrFail($validated['party_id']);
        if (! $party->is_customer) {
            throw ValidationException::withMessages(['party_id' => 'Pick a party marked as a Customer.']);
        }

        $rows = [];
        foreach ($validated['items'] as $i => $row) {
            [$product, $variant] = $this->resolveItem($row['item']);

            if (! $product) {
                throw ValidationException::withMessages(["items.{$i}.item" => 'Pick a valid product or variant.']);
            }

            $rows[] = [
                'product' => $product,
                'variant' => $variant,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'subtotal' => round($row['quantity'] * $row['unit_price'], 2),
            ];
        }

        $subtotal = collect($rows)->sum('subtotal');
        $discount = min((float) ($validated['discount_amount'] ?? 0), $subtotal);

        if ($discount < (float) ($validated['discount_amount'] ?? 0)) {
            throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the order subtotal.']);
        }

        return [$validated, $rows, $subtotal, $discount];
    }
}
