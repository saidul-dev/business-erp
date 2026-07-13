<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

class DeliveryPartnerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:delivery.view', only: ['index']),
            new Middleware('permission:delivery.create', only: ['create', 'store']),
            new Middleware('permission:delivery.edit', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('permission:delivery.delete', only: ['destroy']),
        ];
    }

    public function index()
    {
        $partners = DeliveryPartner::withCount('consignments')->orderBy('name')->paginate(15);

        return view('admin.delivery-partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.delivery-partners.create');
    }

    public function store(Request $request)
    {
        $partner = DeliveryPartner::create($this->validated($request));

        return redirect()->route('delivery-partners.index')->with('success', "Delivery partner \"{$partner->name}\" created.");
    }

    public function edit(DeliveryPartner $deliveryPartner)
    {
        return view('admin.delivery-partners.edit', ['partner' => $deliveryPartner]);
    }

    public function update(Request $request, DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->update($this->validated($request, $deliveryPartner));

        return redirect()->route('delivery-partners.index')->with('success', "Delivery partner \"{$deliveryPartner->name}\" updated.");
    }

    public function toggleStatus(DeliveryPartner $deliveryPartner)
    {
        $deliveryPartner->update(['status' => ! $deliveryPartner->status]);

        return back()->with('success', "Delivery partner \"{$deliveryPartner->name}\" is now ".($deliveryPartner->status ? 'active' : 'inactive').'.');
    }

    public function destroy(DeliveryPartner $deliveryPartner)
    {
        if ($deliveryPartner->consignments()->exists()) {
            return back()->with('error', "Delivery partner \"{$deliveryPartner->name}\" already has consignments — deactivate it instead.");
        }

        $deliveryPartner->delete();

        return redirect()->route('delivery-partners.index')->with('success', "Delivery partner \"{$deliveryPartner->name}\" deleted.");
    }

    protected function validated(Request $request, ?DeliveryPartner $partner = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('delivery_partners', 'code')->ignore($partner?->id)],
            'provider' => ['required', Rule::in(DeliveryPartner::PROVIDERS)],
            'api_key' => ['required_unless:provider,manual', 'nullable', 'string', 'max:500'],
            'secret_key' => ['required_unless:provider,manual', 'nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
