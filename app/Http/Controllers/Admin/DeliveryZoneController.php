<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Delivery areas + flat charges shown to the customer at checkout (see
 * website/checkout.blade.php). Same permission gate as Website Settings /
 * Hero Slides — this is storefront content, not accounting: the charge is
 * never added to a Sale's total_amount or the ledger (see the DeliveryZone
 * model docblock).
 */
class DeliveryZoneController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['index']),
            new Middleware('permission:settings.edit', only: ['create', 'store', 'edit', 'update', 'destroy', 'toggleStatus']),
        ];
    }

    public function index()
    {
        $zones = DeliveryZone::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.delivery-zones.index', compact('zones'));
    }

    public function create()
    {
        return view('admin.delivery-zones.create');
    }

    public function store(Request $request)
    {
        DeliveryZone::create($this->validated($request));

        return redirect()->route('delivery-zones.index')->with('success', 'Delivery zone added.');
    }

    public function edit(DeliveryZone $deliveryZone)
    {
        return view('admin.delivery-zones.edit', ['zone' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $deliveryZone->update($this->validated($request));

        return redirect()->route('delivery-zones.index')->with('success', 'Delivery zone updated.');
    }

    public function toggleStatus(DeliveryZone $deliveryZone)
    {
        $deliveryZone->update(['status' => ! $deliveryZone->status]);

        return back()->with('success', 'Delivery zone is now '.($deliveryZone->status ? 'active' : 'inactive').'.');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $deliveryZone->delete();

        return redirect()->route('delivery-zones.index')->with('success', 'Delivery zone deleted.');
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'charge' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'status' => ['nullable', 'boolean'],
        ]);

        // Checkboxes omit the field entirely when unchecked, so read it directly.
        $validated['status'] = $request->boolean('status');

        return $validated;
    }
}
