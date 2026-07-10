<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerTransaction;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * "Day Book" — a chronological register of every voucher posted through
 * LedgerService::post() (Purchase, Sale, Payment, Collection, Opening
 * Balance, ...), the one place that shows all of them together instead of
 * jumping between each module's own list. Read-only; purely a new
 * aggregation over existing ledger_transactions rows — see
 * docs/accounting-foundation.md.
 */
class DayBookController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view'),
        ];
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $type = $request->get('type');
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now();

        $transactions = LedgerTransaction::with(['site', 'creator', 'reference'])
            ->withSum('lines as total_debit', 'debit')
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.day-book.index', [
            'transactions' => $transactions,
            'sites' => $sites,
            'type' => $type,
            'siteId' => $siteId,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'types' => LedgerTransaction::TYPES,
        ]);
    }
}
