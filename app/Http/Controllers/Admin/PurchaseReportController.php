<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Purchase;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Purchases within a date range. No Paid/Due column here, unlike Sale
 * Report: Payment only carries a party_id, not a purchase_id — a payment
 * settles the supplier's overall payable balance, not one specific
 * purchase invoice, so there's no reliable way to attribute how much of a
 * given purchase has been paid. That figure already exists per-supplier
 * on the Due Report.
 */
class PurchaseReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sourcing.view'),
        ];
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get(['id', 'name']);
        $suppliers = Party::where('is_supplier', true)->where('status', true)->orderBy('name')->get(['id', 'name']);

        $from = $request->filled('from') ? $request->date('from')->toDateString() : now()->startOfMonth()->toDateString();
        $to = $request->filled('to') ? $request->date('to')->toDateString() : now()->toDateString();
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $partyId = $request->filled('party_id') ? $request->integer('party_id') : null;

        $baseQuery = fn () => Purchase::query()
            ->whereBetween('order_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->when($partyId, fn ($q) => $q->where('party_id', $partyId));

        $totals = $baseQuery()->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(subtotal_amount), 0) as subtotal,
                COALESCE(SUM(discount_amount), 0) as discount,
                COALESCE(SUM(total_amount), 0) as total
            ')->first();

        $purchases = $baseQuery()
            ->with(['party', 'site'])
            ->orderByDesc('order_date')->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.purchases.report', compact(
            'sites', 'suppliers', 'from', 'to', 'siteId', 'partyId', 'totals', 'purchases'
        ));
    }
}
