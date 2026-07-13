<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:product-reviews.view', only: ['index', 'show']),
            new Middleware('permission:product-reviews.approve', only: ['approve', 'reject']),
            new Middleware('permission:product-reviews.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $status = $request->get('status');

        $reviews = ProductReview::with('product')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = ProductReview::where('status', 'pending')->count();

        return view('admin.product-reviews.index', compact('reviews', 'status', 'pendingCount'));
    }

    public function show(ProductReview $productReview)
    {
        $productReview->load('product', 'reviewer');

        return view('admin.product-reviews.show', ['review' => $productReview]);
    }

    public function approve(ProductReview $productReview)
    {
        if ($productReview->status !== 'pending') {
            return back()->with('error', 'Only a pending review can be approved.');
        }

        $productReview->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Review approved.');
    }

    public function reject(Request $request, ProductReview $productReview)
    {
        if ($productReview->status !== 'pending') {
            return back()->with('error', 'Only a pending review can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $productReview->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('success', 'Review rejected.');
    }

    public function destroy(ProductReview $productReview)
    {
        $productReview->delete();

        return redirect()->route('product-reviews.index')->with('success', 'Review deleted.');
    }
}
