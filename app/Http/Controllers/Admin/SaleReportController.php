<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Party;
use App\Models\Sale;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Sales within a date range, with Paid/Due resolved per row from
 * Collection::sale_id (unlike Purchases, a Sale's payments are tracked
 * against the specific invoice, not just the party's running balance —
 * see PurchaseReportController for why that report has no Due column).
 */
class SaleReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sales.view'),
        ];
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get(['id', 'name']);
        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);

        $from = $request->filled('from') ? $request->date('from')->toDateString() : now()->startOfMonth()->toDateString();
        $to = $request->filled('to') ? $request->date('to')->toDateString() : now()->toDateString();
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $partyId = $request->filled('party_id') ? $request->integer('party_id') : null;
        $channel = in_array($request->get('channel'), Sale::CHANNELS, true) ? $request->get('channel') : null;

        $baseQuery = fn () => Sale::query()
            ->whereBetween('order_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->when($partyId, fn ($q) => $q->where('party_id', $partyId))
            ->when($channel, fn ($q) => $q->where('channel', $channel));

        $totals = $baseQuery()->selectRaw('
                COUNT(*) as count,
                COALESCE(SUM(subtotal_amount), 0) as subtotal,
                COALESCE(SUM(discount_amount), 0) as discount,
                COALESCE(SUM(total_amount), 0) as total
            ')->first();

        // Paid is summed separately (not joined into the totals query above)
        // so a sale with several collections doesn't fan out and inflate
        // subtotal/discount/total.
        $totalPaid = (float) Collection::whereIn('sale_id', $baseQuery()->pluck('id'))->sum('amount');
        $totalDue = round((float) $totals->total - $totalPaid, 2);

        $sales = $baseQuery()
            ->with(['party', 'site'])
            ->withSum('collections as paid_amount', 'amount')
            ->orderByDesc('order_date')->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.sales.report', compact(
            'sites', 'customers', 'from', 'to', 'siteId', 'partyId', 'channel',
            'totals', 'totalPaid', 'totalDue', 'sales'
        ));
    }
}
