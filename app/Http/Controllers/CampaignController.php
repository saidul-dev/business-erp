<?php

namespace App\Http\Controllers;

use App\Models\CampaignPage;
use App\Models\CompanySetting;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The public side of a campaign landing page (see
 * Admin\CampaignPageController for the admin CRUD that manages these).
 * Deliberately outside the normal storefront layout — no header, nav, or
 * footer — since these pages exist to convert ad traffic on a single
 * product with as little distraction as possible.
 */
class CampaignController extends Controller
{
    public function show(CampaignPage $campaignPage)
    {
        abort_unless($campaignPage->status && $campaignPage->product->status, 404);

        $company = CompanySetting::current();
        $product = $campaignPage->product->load(['approvedReviews' => fn ($q) => $q->orderByDesc('id')]);

        $variantOptions = collect();

        if ($product->has_variants) {
            $variants = $product->variants()->where('status', true)->with('attributeValues.attribute')->get();

            $variantOptions = $variants->map(fn (ProductVariant $variant) => [
                'id' => "variant-{$variant->id}",
                'label' => $variant->label,
                'price' => (float) ($variant->selling_price ?? $product->selling_price),
                'in_stock' => StockMovement::balanceFor($product->id, $variant->id, $company->online_site_id) >= 1,
            ]);
        } else {
            $inStock = StockMovement::balanceFor($product->id, null, $company->online_site_id) >= 1;
        }

        return view('website.campaign-page', [
            'campaignPage' => $campaignPage,
            'product' => $product,
            'variantOptions' => $variantOptions,
            'inStock' => $inStock ?? false,
            'company' => $company,
        ]);
    }

    public function buyNow(Request $request, CampaignPage $campaignPage)
    {
        abort_unless($campaignPage->status && $campaignPage->product->status, 404);

        $product = $campaignPage->product;

        $validated = $request->validate([
            'item' => ['nullable', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
        ]);

        if ($product->has_variants) {
            $variantId = str($validated['item'] ?? '')->after('variant-')->toString();

            if (! $variantId || ! $product->variants()->where('status', true)->whereKey($variantId)->exists()) {
                throw ValidationException::withMessages(['item' => 'Please choose an option.']);
            }

            $itemKey = "variant-{$variantId}";
        } else {
            $itemKey = "product-{$product->id}";
        }

        app(Cart::class)->add($itemKey, (float) $validated['quantity']);

        return redirect()->route('checkout');
    }
}
