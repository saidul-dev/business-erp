<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Sale;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Guest product review submission — no customer login exists (see the
 * guest-checkout decision), so a reviewer is identified by phone, the same
 * way checkout resolves a Party. Every review starts 'pending' and only
 * becomes publicly visible once Admin > Product Reviews approves it.
 */
class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $phone = Phone::normalize($validated['phone']);

        if (! Phone::isValidBangladeshiMobile($phone)) {
            throw ValidationException::withMessages(['phone' => 'Enter a valid 11-digit mobile number (e.g. 01XXXXXXXXX).']);
        }

        if (ProductReview::where('product_id', $product->id)->where('phone', $phone)->exists()) {
            return back()->with('error', "You've already reviewed this product.");
        }

        $isVerifiedPurchase = Sale::where('shipping_phone', $phone)
            ->where('channel', 'online')
            ->where('status', 'delivered')
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();

        ProductReview::create([
            'product_id' => $product->id,
            'name' => $validated['name'],
            'phone' => $phone,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $isVerifiedPurchase,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thanks! Your review has been submitted and will appear once approved.');
    }
}
