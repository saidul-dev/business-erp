<x-app-layout>
    <x-slot name="title">{{ __('Contact Message') }}</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Message from :name', ['name' => $message->name]) }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $message->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <a href="{{ route('contact-messages.index') }}" class="text-sm font-medium text-brand-700 hover:text-brand-900">
                &larr; {{ __('Back to all messages') }}
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 p-6 space-y-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Email') }}</p>
                <p class="mt-1 text-slate-800">
                    <a href="mailto:{{ $message->email }}" class="hover:text-accent-600">{{ $message->email }}</a>
                </p>
            </div>
            @if ($message->phone)
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Phone') }}</p>
                <p class="mt-1 text-slate-800">{{ $message->phone }}</p>
            </div>
            @endif
        </div>

        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ __('Message') }}</p>
            <p class="mt-2 text-slate-700 whitespace-pre-line">{{ $message->message }}</p>
        </div>

        @can('website.delete')
        <div class="pt-4 border-t border-slate-100">
            <form method="POST" action="{{ route('contact-messages.destroy', $message) }}" onsubmit="return confirm('{{ __('Delete this message?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-100">
                    {{ __('Delete Message') }}
                </button>
            </form>
        </div>
        @endcan
    </div>
</x-app-layout>
