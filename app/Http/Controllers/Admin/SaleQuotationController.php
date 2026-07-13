<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleQuotation;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleQuotationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sale-quotations.view', only: ['index', 'show', 'print']),
            new Middleware('permission:sale-quotations.create', only: ['create', 'store']),
            new Middleware('permission:sale-quotations.edit', only: ['edit', 'update', 'cancel']),
            new Middleware('permission:sale-quotations.approve', only: ['approve', 'reject']),
        ];
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $status = $request->get('status');
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $from = $request->get('from');
        $to = $request->get('to');
        $q = $request->get('q');

        $quotations = SaleQuotation::with(['party', 'site'])
            ->when($siteId, fn ($qr) => $qr->where('site_id', $siteId))
            ->when($status, fn ($qr) => $qr->where('status', $status))
            ->when($from, fn ($qr) => $qr->whereDate('quote_date', '>=', $from))
            ->when($to, fn ($qr) => $qr->whereDate('quote_date', '<=', $to))
            ->when($q, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('quotation_no', 'like', "%{$q}%")
                ->orWhereHas('party', fn ($qp) => $qp->where('name', 'like', "%{$q}%"))
            ))
            ->orderByDesc('quote_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.sale-quotations.index', compact('quotations', 'sites', 'status', 'siteId', 'from', 'to', 'q'));
    }

    public function create()
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        return view('admin.sale-quotations.create', compact('sites', 'customers', 'itemOptions'));
    }

    /**
     * Auto-approved when the company setting requires no approval, else
     * left pending for a Manager to review — see
     * CompanySetting::sale_quotation_approval_required.
     */
    public function store(Request $request)
    {
        [$validated, $rows, $subtotal, $discount] = $this->validateQuotationRequest($request);

        $approvalRequired = (bool) CompanySetting::current()->sale_quotation_approval_required;

        $quotation = DB::transaction(function () use ($validated, $rows, $subtotal, $discount, $approvalRequired) {
            $quotation = SaleQuotation::create([
                'quotation_no' => 'PENDING',
                'party_id' => $validated['party_id'],
                'site_id' => $validated['site_id'],
                'status' => $approvalRequired ? 'pending' : 'approved',
                'quote_date' => $validated['quote_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'note' => $validated['note'] ?? null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $subtotal - $discount,
                'created_by' => Auth::id(),
                'reviewed_by' => $approvalRequired ? null : Auth::id(),
                'reviewed_at' => $approvalRequired ? null : now(),
            ]);
            $quotation->update(['quotation_no' => 'QUO-'.str_pad($quotation->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($rows as $row) {
                $quotation->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }

            return $quotation;
        });

        return redirect()->route('sale-quotations.show', $quotation)
            ->with('success', "Quotation {$quotation->quotation_no} created — ".count($rows).' item(s).');
    }

    public function edit(SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'pending') {
            return redirect()->route('sale-quotations.show', $saleQuotation)->with('error', 'Only a pending quotation can be edited.');
        }

        $saleQuotation->load(['items.product.stockUnit', 'items.productVariant.attributeValues']);

        $sites = Site::where('status', true)->orderBy('name')->get();
        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        $items = $saleQuotation->items->map(fn ($item) => [
            'id' => $item->product_variant_id ? "variant-{$item->product_variant_id}" : "product-{$item->product_id}",
            'name' => $item->productVariant
                ? "{$item->product->name} — {$item->productVariant->label} ({$item->productVariant->sku})"
                : "{$item->product->name} ({$item->product->sku})",
            'unit' => $item->product->stockUnit?->short_name,
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
        ])->values();

        return view('admin.sale-quotations.edit', compact('saleQuotation', 'sites', 'customers', 'itemOptions', 'items'));
    }

    public function update(Request $request, SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'pending') {
            return back()->with('error', 'Only a pending quotation can be edited.');
        }

        [$validated, $rows, $subtotal, $discount] = $this->validateQuotationRequest($request);

        DB::transaction(function () use ($saleQuotation, $validated, $rows, $subtotal, $discount) {
            $saleQuotation->update([
                'party_id' => $validated['party_id'],
                'site_id' => $validated['site_id'],
                'quote_date' => $validated['quote_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'note' => $validated['note'] ?? null,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $subtotal - $discount,
            ]);

            $saleQuotation->items()->delete();

            foreach ($rows as $row) {
                $saleQuotation->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal'],
                ]);
            }
        });

        return redirect()->route('sale-quotations.show', $saleQuotation)->with('success', "Quotation {$saleQuotation->quotation_no} updated.");
    }

    public function show(SaleQuotation $saleQuotation)
    {
        $saleQuotation->load([
            'party', 'site', 'creator', 'reviewer',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
            'sale',
        ]);

        return view('admin.sale-quotations.show', compact('saleQuotation'));
    }

    public function print(SaleQuotation $saleQuotation)
    {
        $saleQuotation->load([
            'party', 'site', 'creator',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.sale-quotations.print', compact('saleQuotation', 'company'));
    }

    public function approve(SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'pending') {
            return back()->with('error', 'Only a pending quotation can be approved.');
        }

        $saleQuotation->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Quotation {$saleQuotation->quotation_no} approved.");
    }

    public function reject(Request $request, SaleQuotation $saleQuotation)
    {
        if ($saleQuotation->status !== 'pending') {
            return back()->with('error', 'Only a pending quotation can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $saleQuotation->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('success', "Quotation {$saleQuotation->quotation_no} rejected.");
    }

    public function cancel(SaleQuotation $saleQuotation)
    {
        if (! in_array($saleQuotation->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Only a pending or approved quotation can be cancelled.');
        }

        $saleQuotation->update(['status' => 'cancelled']);

        return back()->with('success', "Quotation {$saleQuotation->quotation_no} cancelled.");
    }

    /**
     * Every active simple product / variant, as options for the cart
     * builder's item picker, price defaulting from `selling_price` — same
     * shape as SaleController's, kept as its own copy.
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
     * Shared by store() and update(): validates the quotation form,
     * resolves each picker row back into a Product/ProductVariant, and
     * computes the subtotal/discount/total. Returns [$validated, $rows, $subtotal, $discount].
     */
    protected function validateQuotationRequest(Request $request): array
    {
        $validated = $request->validate([
            'party_id' => ['required', 'integer', 'exists:parties,id'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'quote_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quote_date'],
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
            throw ValidationException::withMessages(['discount_amount' => 'Discount cannot exceed the quotation subtotal.']);
        }

        return [$validated, $rows, $subtotal, $discount];
    }
}
