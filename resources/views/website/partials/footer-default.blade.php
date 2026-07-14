<!-- Footer -->
<footer class="bg-brand-950 text-brand-200/80 border-t border-white/10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <div class="flex items-center gap-3 mb-3">
                <x-application-logo class="h-8 w-8" />
                <span class="text-base font-bold text-white">{{ $company->name ?? 'Business ERP' }}</span>
            </div>
            <p class="text-sm">{{ $company->tagline ?: __('Quality products, trusted service.') }}</p>
        </div>

        <div>
            <p class="text-sm font-semibold text-white mb-3">{{ __('Company') }}</p>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('about') }}" class="hover:text-white">{{ __('About') }}</a></li>
                <li><a href="{{ route('media') }}" class="hover:text-white">{{ __('Media') }}</a></li>
                <li><a href="{{ route('career') }}" class="hover:text-white">{{ __('Career') }}</a></li>
            </ul>
        </div>

        <div>
            <p class="text-sm font-semibold text-white mb-3">{{ __('Get in Touch') }}</p>
            <ul class="space-y-2 text-sm">
                <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('Contact Us') }}</a></li>
                <li><a href="{{ route('login') }}" class="hover:text-white">{{ __('Staff Login') }}</a></li>
            </ul>
        </div>

        <div>
            <p class="text-sm font-semibold text-white mb-3">{{ __('Contact') }}</p>
            <ul class="space-y-2 text-sm">
                @if ($company->email)
                    <li>{{ $company->email }}</li>
                @endif
                @if ($company->phone)
                    <li>{{ $company->phone }}</li>
                @endif
                @if ($company->address)
                    <li>{{ $company->address }}</li>
                @endif
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10 py-6 text-center text-xs text-brand-300/70">
        &copy; {{ date('Y') }} {{ $company->name ?? 'Business ERP' }} &middot; {{ __('Enterprise Suite') }}
        &middot; {{ __('Powered by') }} <a href="https://vexasoft.net" target="_blank" rel="noopener" class="font-semibold hover:text-white">Vexasoft</a>
    </div>
</footer>
