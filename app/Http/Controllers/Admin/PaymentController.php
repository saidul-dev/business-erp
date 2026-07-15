<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\LedgerAccount;
use App\Models\Party;
use App\Models\Payment;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Payment Out — settles the Accounts Payable a Purchase (or a Supplier's
 * opening balance) created. Symmetric counterpart to CollectionController
 * (Payment In). See docs/accounting-foundation.md.
 */
class PaymentController extends Controller implements HasMiddleware
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
        $partyId = $request->filled('party_id') ? $request->integer('party_id') : null;

        $payments = Payment::with(['party', 'account'])
            ->when($partyId, fn ($q) => $q->where('party_id', $partyId))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Party::where('is_supplier', true)->where('status', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.payments.index', compact('payments', 'suppliers', 'partyId'));
    }

    /**
     * Picking a Supplier reveals their current payable balance so the
     * amount can be judged against it — not enforced as a hard cap, since
     * paying more than currently due is a valid advance-to-supplier case
     * (the signed balance math already handles that correctly).
     */
    public function create(Request $request)
    {
        $suppliers = Party::where('is_supplier', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $accounts = LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']);

        $party = $request->filled('party_id') ? Party::findOrFail($request->integer('party_id')) : null;
        $payable = $party?->payableBalance();

        return view('admin.payments.create', compact('suppliers', 'accounts', 'party', 'payable'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_id' => ['required', 'integer', 'exists:parties,id'],
            'ledger_account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $party = Party::findOrFail($validated['party_id']);
        if (! $party->is_supplier) {
            throw ValidationException::withMessages(['party_id' => 'Pick a party marked as a Supplier.']);
        }

        $account = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['ledger_account_id']);

        $payment = DB::transaction(function () use ($validated, $party, $account) {
            $payment = Payment::create([
                'payment_no' => 'PENDING',
                'party_id' => $party->id,
                'ledger_account_id' => $account->id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $payment->update(['payment_no' => 'PMT-'.str_pad($payment->id, 6, '0', STR_PAD_LEFT)]);

            LedgerService::post([
                'type' => 'payment_out',
                'date' => $validated['payment_date'],
                // Same convention as Collection: use the paying account's
                // own site when it's tied to one, so Day Book isn't blank.
                'site_id' => $account->site_id,
                'narration' => "Payment to {$party->name} ({$payment->payment_no})",
                'reference' => $payment,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => 'accounts_payable', 'party_id' => $party->id, 'debit' => $validated['amount']],
                    ['account' => $account, 'credit' => $validated['amount']],
                ],
            ]);

            return $payment;
        });

        return redirect()->route('payments.show', $payment)->with('success', "Payment {$payment->payment_no} recorded.");
    }

    public function show(Payment $payment)
    {
        $payment->load(['party', 'account', 'creator', 'ledgerTransaction.lines.account']);

        return view('admin.payments.show', compact('payment'));
    }

    public function print(Payment $payment)
    {
        $payment->load(['party', 'account', 'creator']);
        $company = CompanySetting::current();

        return view('admin.payments.print', compact('payment', 'company'));
    }
}
