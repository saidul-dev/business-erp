<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierConsignment;
use App\Models\DeliveryPartner;
use App\Models\LedgerAccount;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Phase 1 (no live courier API yet — see the Sales User Manual): consignment
 * status and COD are tracked manually by whoever handles the courier
 * relationship day-to-day. The booking itself happens inside
 * SaleController::deliver(); this controller only ever updates status on an
 * already-booked consignment and reconciles its COD once delivered.
 */
class CourierConsignmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:delivery.view', only: ['index', 'show']),
            new Middleware('permission:delivery.edit', only: ['updateStatus', 'settleCod']),
        ];
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $partnerId = $request->filled('delivery_partner_id') ? $request->integer('delivery_partner_id') : null;

        $consignments = CourierConsignment::with(['delivery.sale.party', 'deliveryPartner'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($partnerId, fn ($q) => $q->where('delivery_partner_id', $partnerId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $partners = DeliveryPartner::orderBy('name')->get(['id', 'name']);

        return view('admin.courier-consignments.index', compact('consignments', 'partners', 'status', 'partnerId'));
    }

    public function show(CourierConsignment $courierConsignment)
    {
        $courierConsignment->load([
            'delivery.sale.party', 'delivery.sale.site', 'deliveryPartner', 'bookedBy',
        ]);

        $accounts = LedgerAccount::where('group', 'cash_bank')->where('status', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.courier-consignments.show', ['consignment' => $courierConsignment, 'accounts' => $accounts]);
    }

    /**
     * Manual status update — final states (delivered/returned/lost) are
     * still editable so a mistaken click can be corrected, but once COD has
     * been settled the record locks (see the guard below), since the
     * ledger entry it produced can't just be quietly re-pointed.
     */
    public function updateStatus(Request $request, CourierConsignment $courierConsignment)
    {
        if ($courierConsignment->cod_settled_at) {
            return back()->with('error', 'COD has already been settled for this consignment — status is locked.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(CourierConsignment::STATUSES)],
            'tracking_no' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $courierConsignment->update($validated);

        return back()->with('success', 'Consignment status updated.');
    }

    /**
     * Nets the courier's remittance against the customer's receivable in
     * one voucher: Dr Cash/Bank (net), Dr Courier Expense (fee),
     * Cr Accounts Receivable (full COD) — see LedgerAccountSeeder's
     * courier_expense account. Guarded to fire exactly once per consignment.
     */
    public function settleCod(Request $request, CourierConsignment $courierConsignment)
    {
        if (! $courierConsignment->canSettleCod()) {
            return back()->with('error', 'This consignment is not open for COD settlement.');
        }

        $validated = $request->validate([
            'ledger_account_id' => ['required', 'integer', 'exists:ledger_accounts,id'],
            'courier_fee' => ['nullable', 'numeric', 'min:0'],
            'settled_date' => ['required', 'date'],
        ]);

        $account = LedgerAccount::where('group', 'cash_bank')->findOrFail($validated['ledger_account_id']);
        $codAmount = (float) $courierConsignment->cod_amount;
        $fee = (float) ($validated['courier_fee'] ?? 0);

        if ($fee > $codAmount) {
            throw ValidationException::withMessages(['courier_fee' => 'Courier fee cannot exceed the COD amount.']);
        }

        $courierConsignment->load('delivery.sale');
        $sale = $courierConsignment->delivery->sale;
        $netAmount = round($codAmount - $fee, 2);

        DB::transaction(function () use ($courierConsignment, $validated, $account, $sale, $codAmount, $fee, $netAmount) {
            $lines = [
                ['account' => 'accounts_receivable', 'party_id' => $sale->party_id, 'credit' => $codAmount],
            ];

            if ($netAmount > 0) {
                $lines[] = ['account' => $account, 'debit' => $netAmount];
            }

            if ($fee > 0) {
                $lines[] = ['account' => 'courier_expense', 'debit' => $fee];
            }

            LedgerService::post([
                'type' => 'payment_in',
                'date' => $validated['settled_date'],
                // Same convention as CollectionController: the receiving
                // account's own site wins if it's tied to one, else fall
                // back to the site the original sale happened at.
                'site_id' => $account->site_id ?? $sale->site_id,
                'narration' => "COD remittance via {$courierConsignment->deliveryPartner->name} for {$sale->sale_no}",
                'reference' => $courierConsignment,
                'lines' => $lines,
            ]);

            $courierConsignment->update([
                'courier_fee' => $fee,
                'cod_settled_at' => $validated['settled_date'],
            ]);
        });

        return redirect()->route('courier-consignments.show', $courierConsignment)->with('success', 'COD settled.');
    }
}
