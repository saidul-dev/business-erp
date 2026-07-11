<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerTransactionLine;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * One row per party with a non-zero Accounts Receivable (customer owes us)
 * or Accounts Payable (we owe supplier) balance — the same balances shown
 * individually on each party's ledger page (see Party::receivableBalance()/
 * payableBalance()), but aggregated here in one query instead of N+1 so the
 * whole book can be scanned at once.
 */
class DueReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view'),
        ];
    }

    public function index(Request $request)
    {
        $role = $request->get('role');
        $q = $request->get('q');

        $balances = LedgerTransactionLine::query()
            ->join('ledger_accounts', 'ledger_accounts.id', '=', 'ledger_transaction_lines.ledger_account_id')
            ->whereIn('ledger_accounts.code', ['accounts_receivable', 'accounts_payable'])
            ->whereNotNull('ledger_transaction_lines.party_id')
            ->selectRaw('ledger_transaction_lines.party_id, ledger_accounts.code,
                SUM(ledger_transaction_lines.debit) - SUM(ledger_transaction_lines.credit) as net')
            ->groupBy('ledger_transaction_lines.party_id', 'ledger_accounts.code')
            ->get()
            ->groupBy('party_id');

        $parties = Party::query()
            ->when($role === 'customer', fn ($qr) => $qr->where('is_customer', true))
            ->when($role === 'supplier', fn ($qr) => $qr->where('is_supplier', true))
            ->when($q, fn ($qr) => $qr->where(fn ($qr2) => $qr2
                ->where('name', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
            ))
            ->orderBy('name')
            ->get();

        $rows = $parties
            ->map(function (Party $party) use ($balances) {
                $lines = $balances->get($party->id, collect());
                $receivable = (float) ($lines->firstWhere('code', 'accounts_receivable')->net ?? 0);
                $payable = -(float) ($lines->firstWhere('code', 'accounts_payable')->net ?? 0);

                return (object) [
                    'party' => $party,
                    'receivable' => round($receivable, 2),
                    'payable' => round($payable, 2),
                ];
            })
            ->filter(fn ($row) => $row->receivable != 0 || $row->payable != 0)
            ->sortByDesc(fn ($row) => max($row->receivable, $row->payable, 0))
            ->values();

        $totalReceivable = $rows->sum(fn ($row) => max($row->receivable, 0));
        $totalPayable = $rows->sum(fn ($row) => max($row->payable, 0));

        return view('admin.due-report.index', compact('rows', 'role', 'q', 'totalReceivable', 'totalPayable'));
    }
}
