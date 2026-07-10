<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerAccount;
use App\Models\Site;
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
            new Middleware('permission:accounts.view', only: ['index']),
            new Middleware('permission:accounts.create', only: ['create', 'store']),
            new Middleware('permission:accounts.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:accounts.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $bankAccounts = LedgerAccount::where('group', 'cash_bank')
            ->with('site:id,name')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.bank-accounts.index', compact('bankAccounts'));
    }

    public function create()
    {
        return view('admin.bank-accounts.create', ['sites' => Site::orderBy('name')->get(['id', 'name'])]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $bankAccount = LedgerAccount::create($validated + ['group' => 'cash_bank', 'nature' => 'debit']);

        return redirect()->route('bank-accounts.index')->with('success', "Bank account \"{$bankAccount->name}\" created.");
    }

    public function edit(LedgerAccount $bankAccount)
    {
        return view('admin.bank-accounts.edit', [
            'bankAccount' => $bankAccount,
            'sites' => Site::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, LedgerAccount $bankAccount)
    {
        $validated = $this->validated($request);

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
            'site_id' => ['nullable', 'exists:sites,id'],
        ]);
    }
}
