<x-app-layout>
    <x-slot name="title">{{ __('Product Reviews') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Product Reviews') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ __('Ratings and comments submitted on the online store') }}
                @if ($pendingCount > 0)
                    &middot; <span class="font-semibold text-accent-600">{{ $pendingCount }} {{ __('pending') }}</span>
                @endif
            </p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 mb-6">
        <form method="GET" action="{{ route('product-reviews.index') }}" class="flex items-end gap-4">
            <div class="w-48">
                <x-input-label for="status" :value="__('Status')" />
                <select id="status" name="status" onchange="this.form.submit()"
                        class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected')] as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($status)
            <a href="{{ route('product-reviews.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">{{ __('Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[880px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('Product') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Reviewer') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Rating') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Comment') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Date') }}</th>
                    <th class="px-5 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reviews as $review)
                <tr class="hover:bg-slate-50 {{ $review->status === 'pending' ? 'bg-accent-50/40' : '' }}">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $review->product->name }}</td>
                    <td class="px-5 py-3">
                        <p class="text-slate-700">{{ $review->name }}</p>
                        <p class="text-xs text-slate-400">{{ $review->phone }}</p>
                        @if ($review->is_verified_purchase)
                        <span class="inline-flex mt-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 ring-1 ring-emerald-200">{{ __('Verified Purchase') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3"><x-star-rating :value="$review->rating" /></td>
                    <td class="px-5 py-3 text-slate-600">{{ Str::limit($review->comment, 60) ?: '—' }}</td>
                    <td class="px-5 py-3"><x-product-review-status-badge :status="$review->status" /></td>
                    <td class="px-5 py-3 text-slate-500">{{ $review->created_at->format('d M, Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('product-reviews.show', $review) }}" class="font-semibold text-accent-600 hover:text-accent-800">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-slate-400">{{ __('No reviews yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($reviews->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
