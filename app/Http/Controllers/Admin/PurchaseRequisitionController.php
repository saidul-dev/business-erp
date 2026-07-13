<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Party;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseRequisition;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequisitionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:purchase-requisitions.view', only: ['index', 'show', 'print']),
            new Middleware('permission:purchase-requisitions.create', only: ['create', 'store']),
            new Middleware('permission:purchase-requisitions.edit', only: ['edit', 'update', 'cancel']),
            new Middleware('permission:purchase-requisitions.approve', only: ['approve', 'reject']),
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

        $requisitions = PurchaseRequisition::with(['site', 'party'])
            ->when($siteId, fn ($qr) => $qr->where('site_id', $siteId))
            ->when($status, fn ($qr) => $qr->where('status', $status))
            ->when($from, fn ($qr) => $qr->whereDate('request_date', '>=', $from))
            ->when($to, fn ($qr) => $qr->whereDate('request_date', '<=', $to))
            ->when($q, fn ($qr) => $qr->where('requisition_no', 'like', "%{$q}%"))
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.purchase-requisitions.index', compact('requisitions', 'sites', 'status', 'siteId', 'from', 'to', 'q'));
    }

    public function create(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $suppliers = Party::where('is_supplier', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        return view('admin.purchase-requisitions.create', compact('sites', 'suppliers', 'itemOptions'));
    }

    /**
     * Auto-approved when the company setting requires no approval, else
     * left pending for a Manager to review — see
     * CompanySetting::purchase_requisition_approval_required.
     */
    public function store(Request $request)
    {
        [$validated, $rows, $estimatedTotal] = $this->validateRequisitionRequest($request);

        $approvalRequired = (bool) CompanySetting::current()->purchase_requisition_approval_required;

        $requisition = DB::transaction(function () use ($validated, $rows, $estimatedTotal, $approvalRequired) {
            $requisition = PurchaseRequisition::create([
                'requisition_no' => 'PENDING',
                'site_id' => $validated['site_id'],
                'party_id' => $validated['party_id'] ?? null,
                'status' => $approvalRequired ? 'pending' : 'approved',
                'request_date' => $validated['request_date'],
                'required_by_date' => $validated['required_by_date'] ?? null,
                'note' => $validated['note'] ?? null,
                'estimated_total_amount' => $estimatedTotal,
                'created_by' => Auth::id(),
                'reviewed_by' => $approvalRequired ? null : Auth::id(),
                'reviewed_at' => $approvalRequired ? null : now(),
            ]);
            $requisition->update(['requisition_no' => 'PR-'.str_pad($requisition->id, 6, '0', STR_PAD_LEFT)]);

            foreach ($rows as $row) {
                $requisition->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'estimated_unit_cost' => $row['estimated_unit_cost'],
                    'note' => $row['note'] ?? null,
                ]);
            }

            return $requisition;
        });

        return redirect()->route('purchase-requisitions.show', $requisition)
            ->with('success', "Requisition {$requisition->requisition_no} created — ".count($rows).' item(s).');
    }

    public function edit(PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return redirect()->route('purchase-requisitions.show', $purchaseRequisition)->with('error', 'Only a pending requisition can be edited.');
        }

        $purchaseRequisition->load(['items.product.stockUnit', 'items.productVariant.attributeValues']);

        $sites = Site::where('status', true)->orderBy('name')->get();
        $suppliers = Party::where('is_supplier', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $itemOptions = $this->itemOptions();

        $items = $purchaseRequisition->items->map(fn ($item) => [
            'id' => $item->product_variant_id ? "variant-{$item->product_variant_id}" : "product-{$item->product_id}",
            'name' => $item->productVariant
                ? "{$item->product->name} — {$item->productVariant->label} ({$item->productVariant->sku})"
                : "{$item->product->name} ({$item->product->sku})",
            'unit' => $item->product->stockUnit?->short_name,
            'quantity' => (float) $item->quantity,
        ])->values();

        return view('admin.purchase-requisitions.edit', compact('purchaseRequisition', 'sites', 'suppliers', 'itemOptions', 'items'));
    }

    public function update(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return back()->with('error', 'Only a pending requisition can be edited.');
        }

        [$validated, $rows, $estimatedTotal] = $this->validateRequisitionRequest($request);

        DB::transaction(function () use ($purchaseRequisition, $validated, $rows, $estimatedTotal) {
            $purchaseRequisition->update([
                'site_id' => $validated['site_id'],
                'party_id' => $validated['party_id'] ?? null,
                'request_date' => $validated['request_date'],
                'required_by_date' => $validated['required_by_date'] ?? null,
                'note' => $validated['note'] ?? null,
                'estimated_total_amount' => $estimatedTotal,
            ]);

            $purchaseRequisition->items()->delete();

            foreach ($rows as $row) {
                $purchaseRequisition->items()->create([
                    'product_id' => $row['product']->id,
                    'product_variant_id' => $row['variant']?->id,
                    'quantity' => $row['quantity'],
                    'estimated_unit_cost' => $row['estimated_unit_cost'],
                    'note' => $row['note'] ?? null,
                ]);
            }
        });

        return redirect()->route('purchase-requisitions.show', $purchaseRequisition)->with('success', "Requisition {$purchaseRequisition->requisition_no} updated.");
    }

    public function show(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->load([
            'site', 'party', 'creator', 'reviewer',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
            'purchase',
        ]);

        return view('admin.purchase-requisitions.show', compact('purchaseRequisition'));
    }

    public function print(PurchaseRequisition $purchaseRequisition)
    {
        $purchaseRequisition->load([
            'site', 'party', 'creator',
            'items.product.stockUnit', 'items.productVariant.attributeValues',
        ]);
        $company = CompanySetting::current();

        return view('admin.purchase-requisitions.print', compact('purchaseRequisition', 'company'));
    }

    public function approve(PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return back()->with('error', 'Only a pending requisition can be approved.');
        }

        $purchaseRequisition->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Requisition {$purchaseRequisition->requisition_no} approved.");
    }

    public function reject(Request $request, PurchaseRequisition $purchaseRequisition)
    {
        if ($purchaseRequisition->status !== 'pending') {
            return back()->with('error', 'Only a pending requisition can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $purchaseRequisition->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('success', "Requisition {$purchaseRequisition->requisition_no} rejected.");
    }

    public function cancel(PurchaseRequisition $purchaseRequisition)
    {
        if (! in_array($purchaseRequisition->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Only a pending or approved requisition can be cancelled.');
        }

        $purchaseRequisition->update(['status' => 'cancelled']);

        return back()->with('success', "Requisition {$purchaseRequisition->requisition_no} cancelled.");
    }

    /**
     * Every active simple product / variant, as options for the cart
     * builder's item picker — identical shape to PurchaseController's, kept
     * as its own copy rather than a shared trait, matching how
     * PurchaseController/SaleController each keep their own.
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
     * Shared by store() and update(): validates the requisition form,
     * resolves each picker row back into a Product/ProductVariant, and
     * snapshots the product's estimated cost for budget visibility (the
     * requester never enters a price — this isn't a priced order).
     * Returns [$validated, $rows, $estimatedTotal].
     */
    protected function validateRequisitionRequest(Request $request): array
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'request_date' => ['required', 'date'],
            'required_by_date' => ['nullable', 'date', 'after_or_equal:request_date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.note' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($validated['party_id'])) {
            $party = Party::findOrFail($validated['party_id']);
            if (! $party->is_supplier) {
                throw ValidationException::withMessages(['party_id' => 'Pick a party marked as a Supplier.']);
            }
        }

        $rows = [];
        foreach ($validated['items'] as $i => $row) {
            [$product, $variant] = $this->resolveItem($row['item']);

            if (! $product) {
                throw ValidationException::withMessages(["items.{$i}.item" => 'Pick a valid product or variant.']);
            }

            $estimatedUnitCost = (float) ($variant?->estimated_cost ?? $product->estimated_cost);

            $rows[] = [
                'product' => $product,
                'variant' => $variant,
                'quantity' => $row['quantity'],
                'estimated_unit_cost' => $estimatedUnitCost,
                'note' => $row['note'] ?? null,
            ];
        }

        $estimatedTotal = collect($rows)->sum(fn ($row) => round($row['quantity'] * $row['estimated_unit_cost'], 2));

        return [$validated, $rows, $estimatedTotal];
    }
}
