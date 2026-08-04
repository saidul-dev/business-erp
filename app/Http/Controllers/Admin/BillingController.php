<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = auth()->user()->tenant;

        return view('admin.billing.index', [
            'tenant' => $tenant,
            'subscription' => $tenant->currentSubscription(),
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
            'branchCount' => $tenant->branches()->count(),
        ]);
    }

    /**
     * "Purchase" a package. No payment gateway wired up yet — this records
     * the choice and activates it immediately (billing_cycle-length period
     * from today), so the rest of the app (branch limits, the subscription
     * gate) has something real to check against. Swap this method's body
     * for an actual gateway redirect/webhook flow later; nothing else in
     * the app needs to change when that happens.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $tenant = auth()->user()->tenant;
        $plan = Plan::findOrFail($validated['plan_id']);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_ends_at' => now()->addMonth(),
        ]);

        return redirect()->route('billing.index')->with('success', "Switched to the \"{$plan->name}\" package.");
    }
}
