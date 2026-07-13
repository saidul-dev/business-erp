<x-app-layout>
    <x-slot name="title">{{ __('Review') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Review') }} — {{ $review->product->name }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Submitted') }} {{ $review->created_at->format('d M, Y H:i') }}</p>
        </div>
    </x-slot>

    @php
        $statusAccent = [
            'pending' => 'border-slate-300',
            'approved' => 'border-emerald-400',
            'rejected' => 'border-rose-400',
        ][$review->status] ?? 'border-slate-300';
    @endphp

    @if (session('success'))
    <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="mb-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 ring-1 ring-rose-200">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border-l-4 {{ $statusAccent }} bg-white shadow-sm ring-1 ring-slate-200 p-6 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3 text-sm">
                <span class="font-semibold text-slate-800">{{ $review->name }}</span>
                <span class="text-slate-400">{{ $review->phone }}</span>
                @if ($review->is_verified_purchase)
                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 ring-1 ring-emerald-200">{{ __('Verified Purchase') }}</span>
                @endif
                <x-product-review-status-badge :status="$review->status" />
            </div>
            <a href="{{ route('shop.product', $review->product) }}" target="_blank" class="text-sm font-semibold text-accent-600 hover:text-accent-800">{{ __('View on Store') }} &rarr;</a>
        </div>

        <div class="mt-5">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Product') }}</span>
            <p class="mt-1 font-semibold text-slate-800">{{ $review->product->name }}</p>
        </div>

        <div class="mt-4">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Rating') }}</span>
            <div class="mt-1"><x-star-rating :value="$review->rating" size="lg" /></div>
        </div>

        @if ($review->comment)
        <div class="mt-4">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Comment') }}</span>
            <p class="mt-1 whitespace-pre-line text-slate-700">{{ $review->comment }}</p>
        </div>
        @endif

        @if ($review->status === 'rejected' && $review->rejection_reason)
        <p class="mt-3 text-sm text-rose-600"><strong>{{ __('Rejection reason') }}:</strong> {{ $review->rejection_reason }}</p>
        @endif

        @if ($review->reviewer)
        <p class="mt-3 text-xs text-slate-400">
            {{ __('Reviewed by') }} {{ $review->reviewer->name }} {{ __('on') }} {{ $review->reviewed_at?->format('d M, Y H:i') }}
        </p>
        @endif

        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-slate-100 pt-5">
            @if ($review->status === 'pending')
                @can('product-reviews.approve')
                <form method="POST" action="{{ route('product-reviews.approve', $review) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        {{ __('Approve') }}
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('reject-form').classList.toggle('hidden')"
                        class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100">
                    {{ __('Reject') }}
                </button>
                @endcan
            @endif

            @can('product-reviews.delete')
            <form method="POST" action="{{ route('product-reviews.destroy', $review) }}"
                  onsubmit="return confirm('{{ __('Delete this review permanently?') }}');" class="sm:ml-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-500 hover:bg-slate-50">
                    {{ __('Delete') }}
                </button>
            </form>
            @endcan
        </div>

        @can('product-reviews.approve')
        <form id="reject-form" method="POST" action="{{ route('product-reviews.reject', $review) }}" class="hidden mt-4 border-t border-slate-100 pt-4">
            @csrf
            <x-input-label for="rejection_reason" :value="__('Rejection reason (optional)')" />
            <textarea id="rejection_reason" name="rejection_reason" rows="2"
                      class="mt-1 block w-full max-w-xl rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500"></textarea>
            <button type="submit" class="mt-2 inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                {{ __('Confirm Rejection') }}
            </button>
        </form>
        @endcan
    </div>
</x-app-layout>
