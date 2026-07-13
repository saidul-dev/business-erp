<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $campaignPage->headline }}</title>

    {{-- Open Graph tags — this is what Facebook reads to build the link
    preview card when the URL is pasted into a post or used as an ad
    destination. Without these it falls back to a blank/generic preview. --}}
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $campaignPage->headline }}">
    <meta property="og:description" content="{{ $campaignPage->subheadline ?: $product->short_description }}">
    <meta property="og:url" content="{{ route('campaign.show', $campaignPage) }}">
    @if ($campaignPage->banner_image_url)
    <meta property="og:image" content="{{ $campaignPage->banner_image_url }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-white text-brand-950"
      x-data="{
         variants: @js($variantOptions),
         selected: @js($variantOptions->first()['id'] ?? null),
         hasVariants: {{ $product->has_variants ? 'true' : 'false' }},
         quantity: 1,
         get current() {
            if (! this.hasVariants) return null;
            return this.variants.find(v => v.id === this.selected);
         },
         get price() {
            return this.hasVariants ? (this.current?.price ?? 0) : {{ (float) $product->selling_price }};
         },
         get inStock() {
            return this.hasVariants ? (this.current?.in_stock ?? false) : {{ $inStock ? 'true' : 'false' }};
         }
      }">

    <header class="border-b border-slate-100 py-4">
        <div class="mx-auto max-w-2xl px-4 flex items-center gap-2.5">
            <x-application-logo class="h-8 w-8" />
            <span class="font-bold text-brand-950">{{ $company->name ?? config('app.name') }}</span>
        </div>
    </header>

    <main class="mx-auto max-w-2xl px-4 py-8">
        @if (session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
            <span>{{ session('error') }}</span>
        </div>
        @endif

        @if ($campaignPage->banner_image_url)
        <img src="{{ $campaignPage->banner_image_url }}" alt="{{ $campaignPage->headline }}"
             class="w-full rounded-2xl object-cover aspect-[16/9]">
        @endif

        <h1 class="mt-6 text-3xl font-extrabold leading-tight text-brand-950">{{ $campaignPage->headline }}</h1>
        @if ($campaignPage->subheadline)
        <p class="mt-2 text-lg text-slate-600">{{ $campaignPage->subheadline }}</p>
        @endif

        <div class="mt-5 flex items-center gap-3">
            <p class="text-3xl font-bold text-accent-600" x-text="Number(price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></p>
            @if ($product->discount_percent)
            <p class="text-lg text-slate-400 line-through">{{ number_format($product->compare_at_price, 2) }}</p>
            <span class="rounded-full bg-rose-600 px-2.5 py-1 text-xs font-bold text-white">-{{ $product->discount_percent }}%</span>
            @endif
        </div>

        @if (!empty($product->review_count))
        <div class="mt-2 flex items-center gap-2 text-sm">
            <x-star-rating :value="$product->average_rating" />
            <span class="text-slate-500">{{ $product->average_rating }} ({{ $product->review_count }} {{ Str::plural('review', $product->review_count) }})</span>
        </div>
        @endif

        @if (!empty($campaignPage->feature_list))
        <ul class="mt-6 space-y-2">
            @foreach ($campaignPage->feature_list as $feature)
            <li class="flex items-start gap-2 text-sm text-slate-700">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                <span>{{ $feature }}</span>
            </li>
            @endforeach
        </ul>
        @endif

        <form method="POST" action="{{ route('campaign.buy', $campaignPage) }}" class="mt-8 space-y-4 rounded-2xl bg-slate-50 p-5 ring-1 ring-slate-200">
            @csrf

            @if ($product->has_variants)
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Options') }}</label>
                <select name="item" x-model="selected" required
                        class="w-full rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
                    @foreach ($variantOptions as $variant)
                    <option value="{{ $variant['id'] }}">{{ $variant['label'] }} — {{ number_format($variant['price'], 2) }}{{ $variant['in_stock'] ? '' : ' ('.__('Out of Stock').')' }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-center gap-3">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Qty') }}</label>
                <input type="number" name="quantity" x-model.number="quantity" min="1" step="1"
                       class="w-24 rounded-lg border-slate-300 text-sm focus:border-accent-500 focus:ring-accent-500">
            </div>

            <button type="submit" :disabled="!inStock"
                    class="w-full rounded-lg bg-accent-500 px-6 py-4 text-base font-bold text-brand-950 hover:bg-accent-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="inStock">{{ __('Order Now') }}</span>
                <span x-show="!inStock" x-cloak>{{ __('Out of Stock') }}</span>
            </button>
        </form>

        @if ($product->approvedReviews->isNotEmpty())
        <div class="mt-10">
            <h2 class="mb-4 text-lg font-bold text-brand-950">{{ __('What customers say') }}</h2>
            <div class="space-y-4">
                @foreach ($product->approvedReviews as $review)
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-semibold text-brand-950">{{ $review->name }}</span>
                        <span class="text-xs text-slate-400">{{ $review->created_at->format('d M, Y') }}</span>
                    </div>
                    <div class="mt-1"><x-star-rating :value="$review->rating" /></div>
                    @if ($review->comment)
                    <p class="mt-2 text-sm text-slate-600">{{ $review->comment }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <footer class="border-t border-slate-100 py-6 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} {{ $company->name ?? config('app.name') }}
    </footer>
</body>
</html>
