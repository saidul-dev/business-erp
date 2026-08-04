<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * "Bank Accounts" screen — the only Phase 1 UI over ledger_accounts. Scoped
 * to the cash_bank group only; every other group is system-seeded (see
 * LedgerAccountSeeder) and has no CRUD screen yet. See
 * docs/accounting-foundation.md.
 */
class LedgerAccountController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view', only: ['index', 'ledger']),
            new Middleware('permission:accounts.create', only: ['create', 'store']),
            new Middleware('permission:accounts.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:accounts.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $bankAccounts = LedgerAccount::where('group', 'cash_bank')
            ->with('branch:id,name')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    /**
     * Per-account transaction register (a bank passbook, in effect) — the
     * drill-down the list's balance figure doesn't show on its own. Same
     * "no stored counter" rule as everywhere else: the running balance is
     * computed by walking the lines in date order, never read from a
     * column.
     */
    public function ledger(LedgerAccount $bankAccount)
    {
        $lines = $bankAccount->lines()
            ->with('transaction')
            ->join('ledger_transactions', 'ledger_transactions.id', '=', 'ledger_transaction_lines.ledger_transaction_id')
            ->orderBy('ledger_transactions.date')
            ->orderBy('ledger_transaction_lines.id')
            ->select('ledger_transaction_lines.*')
            ->get();

        $running = 0.0;
        $lines = $lines->map(function ($line) use (&$running) {
            $running += (float) $line->debit - (float) $line->credit;
            $line->running_balance = $running;

            return $line;
        });

        return view('admin.bank-accounts.ledger', [
            'bankAccount' => $bankAccount,
            'lines' => $lines,
            'balance' => $bankAccount->balance(),
        ]);
    }

    public function create()
    {
        return view('admin.bank-accounts.create', ['branches' => Branch::orderBy('name')->get(['id', 'name'])]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['is_cash'] = $request->boolean('is_cash');

        $bankAccount = LedgerAccount::create($validated + ['group' => 'cash_bank', 'nature' => 'debit']);

        return redirect()->route('bank-accounts.index')->with('success', "Bank account \"{$bankAccount->name}\" created.");
    }

    public function edit(LedgerAccount $bankAccount)
    {
        return view('admin.bank-accounts.edit', [
            'bankAccount' => $bankAccount,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, LedgerAccount $bankAccount)
    {
        $validated = $this->validated($request);
        $validated['is_cash'] = $request->boolean('is_cash');

        $bankAccount->update($validated);

        return redirect()->route('bank-accounts.index')->with('success', "Bank account \"{$bankAccount->name}\" updated.");
    }

    public function toggleStatus(LedgerAccount $bankAccount)
    {
        $bankAccount->update(['status' => ! $bankAccount->status]);

        return back()->with('success', "Bank account \"{$bankAccount->name}\" is now ".($bankAccount->status ? 'active' : 'inactive').'.');
    }

    public function destroy(LedgerAccount $bankAccount)
    {
        if ($bankAccount->is_system) {
            return back()->with('error', "\"{$bankAccount->name}\" is a system account and can't be deleted.");
        }

        if ($bankAccount->lines()->exists()) {
            return back()->with('error', "\"{$bankAccount->name}\" already has transactions and can't be deleted.");
        }

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')->with('success', "Bank account \"{$bankAccount->name}\" deleted.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);
    }
}
