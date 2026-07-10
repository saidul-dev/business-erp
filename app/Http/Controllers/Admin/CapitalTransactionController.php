<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapitalTransaction;
use App\Models\CompanySetting;
use App\Models\LedgerAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Owner capital movements — Investment (owner puts money in) and Drawing
 * (owner takes money out), the two sides of one screen since they only
 * ever swap direction between the same pair of accounts. Posts as an
 * `investment` or `drawing` voucher via LedgerService::post() — no party,
 * no operating category, just the cash_bank account and the matching
 * owner-equity account. See docs/accounting-foundation.md.
 */
class CapitalTransactionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view', only: ['index', 'show', 'print']),
            new Middleware('permission:accounts.create', only: ['create', 'store']),
        ];
    }

    public function index(Request $request)
    {
        $type = $request->get('type');

        $transactions = CapitalTransaction::with('account')
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.capital-transactions.index', [
            'transactions' => $transactions,
            'type' => $type,
        ]);
    }

    public function create()
    {
        return view('admin.capital-transactions.create', [
            'accounts' => LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']),
            'netCapital' => $this->netCapital(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(CapitalTransaction::TYPES))],
            'account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $account = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['account_id']);
        $isInvestment = $validated['type'] === 'investment';
        $equityAccount = LedgerAccount::where('code', $isInvestment ? 'owner_capital' : 'owner_drawings')->firstOrFail();

        $transaction = DB::transaction(function () use ($validated, $account, $equityAccount, $isInvestment) {
            $transaction = CapitalTransaction::create([
                'transaction_no' => 'PENDING',
                'type' => $validated['type'],
                'account_id' => $account->id,
                'amount' => $validated['amount'],
                'transaction_date' => $validated['transaction_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $transaction->update(['transaction_no' => 'CAP-'.str_pad($transaction->id, 6, '0', STR_PAD_LEFT)]);

            $label = $isInvestment ? 'Owner investment' : 'Owner drawings';

            LedgerService::post([
                'type' => $isInvestment ? 'investment' : 'drawing',
                'date' => $validated['transaction_date'],
                'narration' => "{$label} — {$account->name} ({$transaction->transaction_no})",
                'reference' => $transaction,
                'created_by' => Auth::id(),
                'lines' => $isInvestment
                    ? [
                        ['account' => $account, 'debit' => $validated['amount']],
                        ['account' => $equityAccount, 'credit' => $validated['amount']],
                    ]
                    : [
                        ['account' => $equityAccount, 'debit' => $validated['amount']],
                        ['account' => $account, 'credit' => $validated['amount']],
                    ],
            ]);

            return $transaction;
        });

        return redirect()->route('capital-transactions.show', $transaction)->with('success', "{$transaction->transaction_no} recorded.");
    }

    public function show(CapitalTransaction $capitalTransaction)
    {
        $capitalTransaction->load(['account', 'creator', 'ledgerTransaction.lines.account']);

        return view('admin.capital-transactions.show', ['transaction' => $capitalTransaction]);
    }

    public function print(CapitalTransaction $capitalTransaction)
    {
        $capitalTransaction->load(['account', 'creator']);
        $company = CompanySetting::current();

        return view('admin.capital-transactions.print', ['transaction' => $capitalTransaction, 'company' => $company]);
    }

    /**
     * Owner's Capital balance minus Owner's Drawings balance — the net
     * amount the owner currently has invested in the business.
     */
    protected function netCapital(): float
    {
        $capital = LedgerAccount::where('code', 'owner_capital')->first()?->balance() ?? 0;
        $drawings = LedgerAccount::where('code', 'owner_drawings')->first()?->balance() ?? 0;

        return $capital - $drawings;
    }
}
