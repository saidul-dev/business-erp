<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\LedgerTransactionLine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Assets = Liabilities + Equity, as of a date. Unlike Trial Balance (which
 * just proves every voucher's debit/credit sides matched), this folds
 * income_expense group accounts into Equity as a computed Net Profit/Loss
 * line — there are no period-end closing entries in this ledger, so
 * Income/Expense balances stay open and have to be netted here instead of
 * read as their own balance sheet line items. See docs/accounting-foundation.md.
 */
class BalanceSheetController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view'),
        ];
    }

    public function index(Request $request)
    {
        $asOf = $request->filled('as_of') ? $request->date('as_of') : now();

        $sums = LedgerTransactionLine::query()
            ->join('ledger_transactions', 'ledger_transactions.id', '=', 'ledger_transaction_lines.ledger_transaction_id')
            ->whereDate('ledger_transactions.date', '<=', $asOf)
            ->selectRaw('ledger_transaction_lines.ledger_account_id, SUM(ledger_transaction_lines.debit) as total_debit, SUM(ledger_transaction_lines.credit) as total_credit')
            ->groupBy('ledger_transaction_lines.ledger_account_id')
            ->get()
            ->keyBy('ledger_account_id');

        $balance = function (LedgerAccount $account) use ($sums) {
            $sum = $sums->get($account->id);
            $raw = (float) ($sum->total_debit ?? 0) - (float) ($sum->total_credit ?? 0);

            return $account->nature === 'credit' ? -$raw : $raw;
        };

        $accounts = LedgerAccount::where('status', true)->orderBy('name')->get();

        $row = fn (LedgerAccount $account, float $amount) => (object) [
            'account' => $account,
            'amount' => round($amount, 2),
        ];

        $assets = $accounts
            ->filter(fn (LedgerAccount $a) => $a->group === 'cash_bank' || $a->group === 'inventory'
                || ($a->group === 'party' && $a->nature === 'debit'))
            ->map(fn (LedgerAccount $a) => $row($a, $balance($a)))
            ->filter(fn ($r) => $r->amount != 0)
            ->values();

        $liabilities = $accounts
            ->filter(fn (LedgerAccount $a) => $a->group === 'party' && $a->nature === 'credit')
            ->map(fn (LedgerAccount $a) => $row($a, $balance($a)))
            ->filter(fn ($r) => $r->amount != 0)
            ->values();

        // Credit-side value for every equity account — flips debit-nature
        // ones (Owner's Drawings) so it lands as a reduction, not an addition.
        $equity = $accounts
            ->filter(fn (LedgerAccount $a) => $a->group === 'equity_adjustment')
            ->map(function (LedgerAccount $a) use ($balance) {
                $raw = $balance($a);

                return (object) ['account' => $a, 'amount' => round($a->nature === 'credit' ? $raw : -$raw, 2)];
            })
            ->filter(fn ($r) => $r->amount != 0)
            ->values();

        $netProfit = round($accounts
            ->filter(fn (LedgerAccount $a) => $a->group === 'income_expense')
            ->sum(function (LedgerAccount $a) use ($balance) {
                $raw = $balance($a);

                return $a->nature === 'credit' ? $raw : -$raw;
            }), 2);

        $totalAssets = $assets->sum('amount');
        $totalLiabilities = $liabilities->sum('amount');
        $totalEquity = $equity->sum('amount') + $netProfit;

        return view('admin.balance-sheet.index', [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'netProfit' => $netProfit,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'asOf' => $asOf->toDateString(),
        ]);
    }
}
