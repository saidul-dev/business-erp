<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    @if (app()->environment('local'))
        @php
            $demoAccounts = [
                ['role' => 'Super Admin', 'email' => 'admin@businesserp.test'],
                ['role' => 'Company Owner', 'email' => 'owner@businesserp.test'],
                ['role' => 'Branch Manager', 'email' => 'manager@businesserp.test'],
                ['role' => 'Head Accountant', 'email' => 'accountant@businesserp.test'],
                ['role' => 'Store Keeper', 'email' => 'storekeeper@businesserp.test'],
                ['role' => 'Sales Person', 'email' => 'sales@businesserp.test'],
                ['role' => 'HR Executive', 'email' => 'hr@businesserp.test'],
            ];
        @endphp
        <div class="mt-6 pt-4 border-t border-gray-200">
            <p class="text-sm text-gray-500 mb-2">{{ __('Demo accounts (dev only)') }}</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($demoAccounts as $account)
                    <button
                        type="button"
                        onclick="document.getElementById('email').value = '{{ $account['email'] }}'; document.getElementById('password').value = 'password';"
                        class="text-xs px-2 py-1.5 rounded border border-gray-300 text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        {{ $account['role'] }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</x-guest-layout>
