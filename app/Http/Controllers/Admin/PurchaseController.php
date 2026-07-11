<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\Site;
use App\Models\StockMovement;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sourcing.view', only: ['index', 'show', 'printOrder', 'printReceipt', 'printReturn', 'manual']),
            new Middleware('permission:sourcing.create', only: ['create', 'store']),
            new Middleware('permission:sourcing.approve', only: ['receiveForm', 'receive', 'returnForm', 'storeReturn']),
            new Middleware('permission:sourcing.edit', only: ['cancel']),
        ];
    }

    public function manual()
    {
        return view('admin.purchases.manual');
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $status = $request->get('status');
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $from = $request->get('from');
        $to = $request->get('to');
        $q = $request->get('q');

        $purchases = Purchase::with(['party', 'site'])
            ->when($siteId, fn ($qr) => $qr->where('site_id', $siteId))
            ->when($status, fn ($qr) => $qr->where('status', $status))
            ->when($from, fn ($qr) => $qr->whereDate('order_date', '>=', $from))
            ->when($to, fn ($qr) => $qr->whereDate('order_date', '<=', $to))
            ->when($q, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('purchase_no', 'like', "%{$q}%")
                ->orWhereHas('party', fn ($qp) => $qp->where('name', 'like', "%{$q}%"))
            ))
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.purchases.index', compact('purchases', 'sites', 'status', 'siteId', 'from', 'to', 'q'));
    }

    /**
     * Order form: Supplier + Site, then a cart-style item picker (search +
     * add) sourced from every active product/variant — unlike Stock
     * Transfer, there's no stock-balance filter, since ordering doesn't
     * require existing stock.
     */
    public function create()
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $suppliers = Party::where('is_supplier', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        return view('admin.purchases.create', compact('sites', 'suppliers', 'itemOptions'));
    }

    /**
     * Creates the Purchase Order only — no stock or ledger effect yet.
     * Those happen per-receipt (see receive()), since what's actually
     * owned/owed follows what physically arrives, not what was ordered.
     */
    public function store(Request $request)
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
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $party = Party::findOrFail($validated['party_id']);
        if (! $party->is_supplier) {
            throw ValidationException::withMessages(['party_id' => 'Pick a party marked as a Supplier.']);
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
                'unit_cost' => $row['unit_cost'],
                'subtotal' => round($row['quantity'] * $row['unit_cost'], 2),
            ];
        }

        $subtotal = collect($rows)->sum('subtotal');
        $discount = min((float) ($validated['discount_amount'] ?? 0), $subtotal);

        if ($discount < (float) ($validated['discount_amount'] ?? 0)) {
            throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the order subtotal.']);
        }

        $purchase = DB::transaction(function () use ($validated, $rows, $subtotal, $discount) {
            $purchase = Purchase::create([
                'purchase_no' => 'PENDING',
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
            $purchase->update(['purchase_no' => 'PO-'.str_pad($purchase->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($rows as $row) {
                $purchase->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'],
                    'subtotal' => $row['subtotal'],
                ]);
            }

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Purchase {$purchase->purchase_no} created — ".count($rows).' item(s).');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load([
            'party', 'site', 'creator',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
            'receipts.receivedBy', 'receipts.items.purchaseItem.product',
            'returns.returnedBy', 'returns.items.purchaseItem.product',
        ]);

        return view('admin.purchases.show', compact('purchase'));
    }

    public function printOrder(Purchase $purchase)
    {
        $purchase->load([
            'party', 'site', 'creator',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.purchases.print', compact('purchase', 'company'));
    }

    public function printReceipt(PurchaseReceipt $receipt)
    {
        $receipt->load([
            'purchase.party', 'purchase.site', 'receivedBy',
            'items.purchaseItem.product.stockUnit', 'items.purchaseItem.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.purchases.receipt-print', compact('receipt', 'company'));
    }

    /**
     * Receiving form: every line still has a remaining quantity to fill in
     * — leaving a line at 0 simply skips it, so the same form covers both
     * a full receipt and a partial one.
     */
    public function receiveForm(Purchase $purchase)
    {
        if (! in_array($purchase->status, ['pending', 'partial'], true)) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'This purchase is not open to receive.');
        }

        $purchase->load(['party', 'site', 'items.product.stockUnit', 'items.productVariant.attributeValues']);

        return view('admin.purchases.receive', compact('purchase'));
    }

    /**
     * Posts everything checked in on this receiving pass: one
     * stock_movements row per line (goods physically arrive) plus exactly
     * one ledger_transactions entry for the whole receipt (Dr Inventory,
     * Cr Accounts Payable) via LedgerService::post() — see
     * docs/accounting-foundation.md. Quantities not received now stay
     * open for a later receipt. The order's discount (see
     * Purchase::discountRate()) is baked directly into each line's
     * unit_cost for this receipt — unlike Sale's separate Discount
     * Allowed line, a purchase discount just lowers the real cost, so
     * Inventory/Accounts Payable and the stock_movements valuation used
     * for average costing stay in agreement.
     */
    public function receive(Request $request, Purchase $purchase)
    {
        if (! in_array($purchase->status, ['pending', 'partial'], true)) {
            return back()->with('error', 'This purchase is not open to receive.');
        }

        $validated = $request->validate([
            'received_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_no' => ['nullable', 'string', 'max:100'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.serial_no' => ['nullable', 'string', 'max:100'],
        ]);

        $purchase->load('items.product');

        $lines = [];
        foreach ($validated['items'] as $purchaseItemId => $row) {
            $quantity = (float) ($row['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $item = $purchase->items->firstWhere('id', (int) $purchaseItemId);

            if (! $item) {
                throw ValidationException::withMessages(["items.{$purchaseItemId}" => 'Invalid item.']);
            }

            if ($quantity > $item->remaining() + 0.00001) {
                throw ValidationException::withMessages([
                    "items.{$purchaseItemId}.quantity" => "{$item->product->name}: cannot receive more than the remaining {$item->remaining()}.",
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

        DB::transaction(function () use ($purchase, $validated, $lines) {
            $receipt = PurchaseReceipt::create([
                'purchase_id' => $purchase->id,
                'receipt_no' => 'PENDING',
                'received_date' => $validated['received_date'],
                'note' => $validated['note'] ?? null,
                'received_by' => Auth::id(),
            ]);
            $receipt->update(['receipt_no' => 'GRN-'.str_pad($receipt->id, 6, '0', STR_PAD_LEFT)]);

            $discountRate = $purchase->discountRate();
            $totalValue = 0;

            foreach ($lines as $row) {
                $item = $row['item'];
                $netUnitCost = round((float) $item->unit_cost * (1 - $discountRate), 4);

                $receipt->items()->create([
                    'purchase_item_id' => $item->id,
                    'quantity' => $row['quantity'],
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                ]);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'site_id' => $purchase->site_id,
                    'type' => 'purchase',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $netUnitCost,
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                    'moved_at' => $validated['received_date'],
                    'created_by' => Auth::id(),
                    'reference_type' => PurchaseReceipt::class,
                    'reference_id' => $receipt->id,
                ]);

                $item->update(['received_quantity' => $item->received_quantity + $row['quantity']]);

                $totalValue += round($row['quantity'] * $netUnitCost, 2);
            }

            LedgerService::post([
                'type' => 'purchase',
                'date' => $validated['received_date'],
                'site_id' => $purchase->site_id,
                'narration' => "Goods received against {$purchase->purchase_no}",
                'reference' => $receipt,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => 'inventory', 'debit' => $totalValue],
                    ['account' => 'accounts_payable', 'credit' => $totalValue, 'party_id' => $purchase->party_id],
                ],
            ]);

            $purchase->recalculateStatus();
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Goods received.');
    }

    public function printReturn(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load([
            'purchase.party', 'purchase.site', 'returnedBy',
            'items.purchaseItem.product.stockUnit', 'items.purchaseItem.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.purchases.return-print', ['purchaseReturn' => $purchaseReturn, 'company' => $company]);
    }

    /**
     * Return form: every line shows how much is still eligible to send
     * back (received so far, minus what's already been returned) — same
     * "leave a line at 0 to skip it" convention as receiveForm().
     */
    public function returnForm(Purchase $purchase)
    {
        $purchase->load(['party', 'site', 'items.product.stockUnit', 'items.productVariant.attributeValues']);

        if ($purchase->items->sum(fn ($item) => $item->returnable()) <= 0) {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Nothing on this purchase is eligible to return.');
        }

        return view('admin.purchases.return', compact('purchase'));
    }

    /**
     * Posts everything checked in on this return pass: one
     * stock_movements row per line (`purchase_return`, goods physically
     * leave again) plus exactly one ledger_transactions entry for the
     * whole return — Dr Accounts Payable, Cr Inventory, the mirror image
     * of receive()'s posting — via LedgerService::post(). Valued at each
     * line's original unit_cost from the purchase, not the (possibly
     * since-changed) current average cost.
     */
    public function storeReturn(Request $request, Purchase $purchase)
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

        $purchase->load('items.product');

        $lines = [];
        foreach ($validated['items'] as $purchaseItemId => $row) {
            $quantity = (float) ($row['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $item = $purchase->items->firstWhere('id', (int) $purchaseItemId);

            if (! $item) {
                throw ValidationException::withMessages(["items.{$purchaseItemId}" => 'Invalid item.']);
            }

            if ($quantity > $item->returnable() + 0.00001) {
                throw ValidationException::withMessages([
                    "items.{$purchaseItemId}.quantity" => "{$item->product->name}: cannot return more than the returnable {$item->returnable()}.",
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

        DB::transaction(function () use ($purchase, $validated, $lines) {
            $return = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_no' => 'PENDING',
                'return_date' => $validated['return_date'],
                'note' => $validated['note'] ?? null,
                'returned_by' => Auth::id(),
            ]);
            $return->update(['return_no' => 'RTN-'.str_pad($return->id, 6, '0', STR_PAD_LEFT)]);

            $discountRate = $purchase->discountRate();
            $totalValue = 0;

            foreach ($lines as $row) {
                $item = $row['item'];
                $netUnitCost = round((float) $item->unit_cost * (1 - $discountRate), 4);

                $return->items()->create([
                    'purchase_item_id' => $item->id,
                    'quantity' => $row['quantity'],
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                ]);

                StockMovement::create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'site_id' => $purchase->site_id,
                    'type' => 'purchase_return',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $netUnitCost,
                    'batch_no' => $row['batch_no'],
                    'expiry_date' => $row['expiry_date'],
                    'serial_no' => $row['serial_no'],
                    'moved_at' => $validated['return_date'],
                    'created_by' => Auth::id(),
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $return->id,
                ]);

                $item->update(['returned_quantity' => $item->returned_quantity + $row['quantity']]);

                $totalValue += round($row['quantity'] * $netUnitCost, 2);
            }

            LedgerService::post([
                'type' => 'purchase_return',
                'date' => $validated['return_date'],
                'site_id' => $purchase->site_id,
                'narration' => "Goods returned against {$purchase->purchase_no}",
                'reference' => $return,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => 'accounts_payable', 'debit' => $totalValue, 'party_id' => $purchase->party_id],
                    ['account' => 'inventory', 'credit' => $totalValue],
                ],
            ]);
        });

        return redirect()->route('purchases.show', $purchase)->with('success', 'Return posted.');
    }

    /**
     * Only ever cancels the *remaining* (un-received) portion — nothing
     * needs reversing since nothing was posted for it. Whatever was
     * already received via earlier receipts stands.
     */
    public function cancel(Purchase $purchase)
    {
        if (! in_array($purchase->status, ['pending', 'partial'], true)) {
            return back()->with('error', 'Only a pending or partially received purchase can be cancelled.');
        }

        $purchase->update(['status' => 'cancelled']);

        return back()->with('success', "Purchase {$purchase->purchase_no} cancelled.");
    }

    /**
     * Every active simple product / variant, as options for the cart
     * builder's item picker — no stock-balance filter (unlike Stock
     * Transfer), since a Purchase is how stock is created in the first
     * place.
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
                'cost' => (float) $p->estimated_cost,
            ]);
        }

        foreach ($variants as $v) {
            $options->push([
                'id' => "variant-{$v->id}",
                'name' => "{$v->product->name} — {$v->label} ({$v->sku})",
                'unit' => $v->product->stockUnit?->short_name,
                'cost' => (float) ($v->estimated_cost ?? $v->product->estimated_cost),
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
}
