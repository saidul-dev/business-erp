<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\LedgerAccount;
use App\Models\Party;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Payment In — settles the Accounts Receivable a Sale (or a Customer's
 * opening balance) created. Symmetric counterpart to PaymentController
 * (Payment Out). See docs/accounting-foundation.md.
 */
class CollectionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:accounts.view', only: ['index', 'show']),
            new Middleware('permission:accounts.create', only: ['create', 'store']),
        ];
    }

    public function index(Request $request)
    {
        $partyId = $request->filled('party_id') ? $request->integer('party_id') : null;

        $collections = Collection::with(['party', 'account'])
            ->when($partyId, fn ($q) => $q->where('party_id', $partyId))
            ->orderByDesc('collection_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.collections.index', compact('collections', 'customers', 'partyId'));
    }

    /**
     * Picking a Customer reveals their current receivable balance so the
     * amount can be judged against it — not enforced as a hard cap, since
     * collecting more than currently due is a valid advance-from-customer
     * case (the signed balance math already handles that correctly).
     */
    public function create(Request $request)
    {
        $customers = Party::where('is_customer', true)->where('status', true)->orderBy('name')->get(['id', 'name']);
        $accounts = LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']);

        $party = $request->filled('party_id') ? Party::findOrFail($request->integer('party_id')) : null;
        $receivable = $party?->receivableBalance();

        return view('admin.collections.create', compact('customers', 'accounts', 'party', 'receivable'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'party_id' => ['required', 'integer', 'exists:parties,id'],
            'ledger_account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'collection_date' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $party = Party::findOrFail($validated['party_id']);
        if (! $party->is_customer) {
            throw ValidationException::withMessages(['party_id' => 'Pick a party marked as a Customer.']);
        }

        $account = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['ledger_account_id']);

        $collection = DB::transaction(function () use ($validated, $party, $account) {
            $collection = Collection::create([
                'collection_no' => 'PENDING',
                'party_id' => $party->id,
                'ledger_account_id' => $account->id,
                'amount' => $validated['amount'],
                'collection_date' => $validated['collection_date'],
                'reference_no' => $validated['reference_no'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
            ]);
            $collection->update(['collection_no' => 'COL-'.str_pad($collection->id, 6, '0', STR_PAD_LEFT)]);

            LedgerService::post([
                'type' => 'payment_in',
                'date' => $validated['collection_date'],
                'narration' => "Collection from {$party->name} ({$collection->collection_no})",
                'reference' => $collection,
                'created_by' => Auth::id(),
                'lines' => [
                    ['account' => $account, 'debit' => $validated['amount']],
                    ['account' => 'accounts_receivable', 'party_id' => $party->id, 'credit' => $validated['amount']],
                ],
            ]);

            return $collection;
        });

        return redirect()->route('collections.show', $collection)->with('success', "Collection {$collection->collection_no} recorded.");
    }

    public function show(Collection $collection)
    {
        $collection->load(['party', 'account', 'creator']);

        return view('admin.collections.show', compact('collection'));
    }
}
