<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\LedgerTransactionLine;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Profit & Loss (Income Statement) for a date range — unlike Trial Balance
 * / Balance Sheet, which are cumulative "as of" a cutoff, income_expense
 * accounts here are summed only *within* the selected period, since this
 * ledger has no period-end closing entries (see BalanceSheetController) and
 * would otherwise show all-time figures instead of "this month's" P&L.
 * Pure aggregation over existing ledger_transactions/lines — no new schema.
 */
class ProfitLossController extends Controller implements HasMiddleware
{
    /**
     * How each system income_expense account rolls up into the statement.
     * Exhaustive over LedgerAccountSeeder's income_expense codes — any
     * future code not listed here falls back by nature (see index()).
     */
    protected const REVENUE_CODES = ['sales_revenue'];

    protected const CONTRA_REVENUE_CODES = ['discount_allowed'];

    protected const COGS_CODES = ['cost_of_goods_sold', 'purchase_expense'];

    protected const OTHER_INCOME_CODES = ['interest_income', 'rental_income', 'commission_income', 'other_income'];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view'),
        ];
    }

    public function index(Request $request)
    {
        $sites = Site::where('status', true)->orderBy('name')->get();
        $siteId = $request->filled('site_id') ? $request->integer('site_id') : null;
        $from = $request->filled('from') ? $request->date('from') : now()->startOfMonth();
        $to = $request->filled('to') ? $request->date('to') : now();

        $sums = LedgerTransactionLine::query()
            ->join('ledger_transactions', 'ledger_transactions.id', '=', 'ledger_transaction_lines.ledger_transaction_id')
            ->whereDate('ledger_transactions.date', '>=', $from)
            ->whereDate('ledger_transactions.date', '<=', $to)
            ->when($siteId, fn ($q) => $q->where('ledger_transactions.site_id', $siteId))
            ->selectRaw('ledger_transaction_lines.ledger_account_id, SUM(ledger_transaction_lines.debit) as total_debit, SUM(ledger_transaction_lines.credit) as total_credit')
            ->groupBy('ledger_transaction_lines.ledger_account_id')
            ->get()
            ->keyBy('ledger_account_id');

        // Same sign convention as BalanceSheetController::balance(): a
        // credit-nature account (revenue, other income) reads positive when
        // credits exceed debits; a debit-nature account (COGS, expense)
        // reads positive when debits exceed credits.
        $balance = function (LedgerAccount $account) use ($sums) {
            $sum = $sums->get($account->id);
            $raw = (float) ($sum->total_debit ?? 0) - (float) ($sum->total_credit ?? 0);

            return $account->nature === 'credit' ? -$raw : $raw;
        };

        $row = fn (LedgerAccount $account) => (object) [
            'account' => $account,
            'amount' => round($balance($account), 2),
        ];

        $accounts = LedgerAccount::where('group', 'income_expense')->where('status', true)->orderBy('name')->get();

        $bucket = fn (array $codes) => $accounts->filter(fn (LedgerAccount $a) => in_array($a->code, $codes, true))
            ->map($row)->filter(fn ($r) => $r->amount != 0)->values();

        $revenue = $bucket(self::REVENUE_CODES);
        $contraRevenue = $bucket(self::CONTRA_REVENUE_CODES);
        $cogs = $bucket(self::COGS_CODES);
        $otherIncome = $bucket(self::OTHER_INCOME_CODES);

        // Operating expenses = every remaining income_expense account not
        // already claimed above — covers the 6 seeded expense categories
        // and forward-compatibly catches any future debit-nature code too.
        $categorized = [...self::REVENUE_CODES, ...self::CONTRA_REVENUE_CODES, ...self::COGS_CODES, ...self::OTHER_INCOME_CODES];
        $operatingExpenses = $accounts->filter(fn (LedgerAccount $a) => ! in_array($a->code, $categorized, true))
            ->map($row)->filter(fn ($r) => $r->amount != 0)->values();

        $totalRevenue = $revenue->sum('amount');
        $totalContraRevenue = $contraRevenue->sum('amount');
        $netRevenue = round($totalRevenue - $totalContraRevenue, 2);

        $totalCogs = $cogs->sum('amount');
        $grossProfit = round($netRevenue - $totalCogs, 2);

        $totalOperatingExpenses = $operatingExpenses->sum('amount');
        $totalOtherIncome = $otherIncome->sum('amount');
        $netProfit = round($grossProfit - $totalOperatingExpenses + $totalOtherIncome, 2);

        return view('admin.profit-loss.index', [
            'sites' => $sites,
            'siteId' => $siteId,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'revenue' => $revenue,
            'contraRevenue' => $contraRevenue,
            'cogs' => $cogs,
            'operatingExpenses' => $operatingExpenses,
            'otherIncome' => $otherIncome,
            'totalRevenue' => $totalRevenue,
            'totalContraRevenue' => $totalContraRevenue,
            'netRevenue' => $netRevenue,
            'totalCogs' => $totalCogs,
            'grossProfit' => $grossProfit,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'totalOtherIncome' => $totalOtherIncome,
            'netProfit' => $netProfit,
        ]);
    }
}
