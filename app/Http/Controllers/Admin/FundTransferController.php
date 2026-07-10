<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\FundTransfer;
use App\Models\LedgerAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Moving money between the company's own cash/bank accounts (e.g.
 * depositing cash into the bank) — no party, no category, so it posts as
 * a `transfer` voucher: Dr [to account] / Cr [from account] via
 * LedgerService::post(). See docs/accounting-foundation.md.
 */
class FundTransferController extends Controller implements HasMiddleware
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
        $accountId = $request->filled('account_id') ? $request->integer('account_id') : null;

        $transfers = FundTransfer::with(['fromAccount', 'toAccount'])
            ->when($accountId, fn ($q) => $q->where(fn ($q2) => $q2->where('from_account_id', $accountId)->orWhere('to_account_id', $accountId)))
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.fund-transfers.index', [
            'transfers' => $transfers,
            'accounts' => $this->accountOptions(),
            'accountId' => $accountId,
        ]);
    }

    public function create()
    {
        return view('admin.fund-transfers.create', [
            'accounts' => $this->accountOptions(),
            'monthTotal' => FundTransfer::whereMonth('transfer_date', now()->month)->whereYear('transfer_date', now()->year)->sum('amount'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => ['required', 'integer', 'different:to_account_id', 'exists:ledger_accounts,id'],
            'to_account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transfer_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $fromAccount = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['from_account_id']);
        $toAccount = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['to_account_id']);

        $transfer = DB::transaction(function () use ($validated, $fromAccount, $toAccount) {
            $transfer = FundTransfer::create([
                'transfer_no' => 'PENDING',
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $validated['amount'],
                'transfer_date' => $validated['transfer_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $transfer->update(['transfer_no' => 'FT-'.str_pad($transfer->id, 6, '0', STR_PAD_LEFT)]);

            LedgerService::post([
                'type' => 'transfer',
                'date' => $validated['transfer_date'],
                'narration' => "Transfer from {$fromAccount->name} to {$toAccount->name} ({$transfer->transfer_no})",
                'reference' => $transfer,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => $toAccount, 'debit' => $validated['amount']],
                    ['account' => $fromAccount, 'credit' => $validated['amount']],
                ],
            ]);

            return $transfer;
        });

        return redirect()->route('fund-transfers.show', $transfer)->with('success', "Transfer {$transfer->transfer_no} recorded.");
    }

    public function show(FundTransfer $fundTransfer)
    {
        $fundTransfer->load(['fromAccount', 'toAccount', 'creator', 'ledgerTransaction.lines.account']);

        return view('admin.fund-transfers.show', ['transfer' => $fundTransfer]);
    }

    public function print(FundTransfer $fundTransfer)
    {
        $fundTransfer->load(['fromAccount', 'toAccount', 'creator']);
        $company = CompanySetting::current();

        return view('admin.fund-transfers.print', ['transfer' => $fundTransfer, 'company' => $company]);
    }

    protected function accountOptions()
    {
        return LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']);
    }
}
