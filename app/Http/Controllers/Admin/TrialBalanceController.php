<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\LedgerTransactionLine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Trial Balance — every ledger_accounts row's cumulative balance as of a
 * date, bucketed into a Debit or Credit column by the *raw* sign of
 * SUM(debit) - SUM(credit) (not LedgerAccount::balance()'s nature-flipped
 * value, which would put every "normal" balance in the same visual column
 * regardless of which side it actually sits on). Total Debit must equal
 * Total Credit — LedgerService::post() already enforces that on every
 * individual voucher, so this is really just a confirmation, not a
 * reconciliation tool. Pure aggregation over existing rows — no new
 * schema. See docs/accounting-foundation.md.
 */
class TrialBalanceController extends Controller implements HasMiddleware
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

        $rows = LedgerAccount::where('status', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->map(function (LedgerAccount $account) use ($sums) {
                $sum = $sums->get($account->id);
                $raw = (float) ($sum->total_debit ?? 0) - (float) ($sum->total_credit ?? 0);

                return (object) [
                    'account' => $account,
                    'debit' => $raw > 0 ? $raw : 0.0,
                    'credit' => $raw < 0 ? -$raw : 0.0,
                ];
            })
            ->groupBy(fn ($row) => $row->account->group);

        $totalDebit = $rows->flatten()->sum('debit');
        $totalCredit = $rows->flatten()->sum('credit');

        return view('admin.trial-balance.index', [
            'rows' => $rows,
            'groups' => LedgerAccount::GROUPS,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'asOf' => $asOf->toDateString(),
        ]);
    }
}
