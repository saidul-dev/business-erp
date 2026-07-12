@if (session('success'))
    <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('contact.submit') }}" class="space-y-4">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" required
                   class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
            @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Your email" required
                   class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
            @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
        </div>
    </div>
    <div>
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="Phone (optional)"
               class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
        @error('phone') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <textarea name="message" rows="4" placeholder="How can we help?" required
                  class="w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">{{ old('message') }}</textarea>
        @error('message') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
    </div>
    <button type="submit" class="w-full sm:w-auto rounded-lg bg-brand-900 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-800">
        Send Message
    </button>
</form>
