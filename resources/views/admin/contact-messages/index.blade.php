<x-app-layout>
    <x-slot name="title">{{ __('Contact Messages') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('Contact Messages') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ __('Messages submitted through the public website contact form') }}
                @if ($unreadCount > 0)
                    &middot; <span class="font-semibold text-accent-600">{{ $unreadCount }} {{ __('unread') }}</span>
                @endif
            </p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[700px] text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">{{ __('From') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Message') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Received') }}</th>
                    <th class="px-5 py-3 font-semibold">{{ __('Status') }}</th>
                    <th class="px-5 py-3 font-semibold text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($messages as $message)
                <tr class="hover:bg-slate-50 {{ $message->is_read ? '' : 'bg-accent-50/40' }}">
                    <td class="px-5 py-3">
                        <p class="font-semibold text-slate-800">{{ $message->name }}</p>
                        <p class="text-xs text-slate-500">{{ $message->email }}</p>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ Str::limit($message->message, 60) }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $message->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1
                            {{ $message->is_read ? 'bg-slate-100 text-slate-500 ring-slate-200' : 'bg-accent-50 text-accent-600 ring-accent-200' }}">
                            {{ $message->is_read ? __('Read') : __('Unread') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="inline-flex items-center gap-3">
                            <a href="{{ route('contact-messages.show', $message) }}" class="font-medium text-brand-700 hover:text-brand-900">{{ __('View') }}</a>
                            @can('website.delete')
                            <form method="POST" action="{{ route('contact-messages.destroy', $message) }}" onsubmit="return confirm('{{ __('Delete this message?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-rose-600 hover:text-rose-800">{{ __('Delete') }}</button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-400">{{ __('No messages yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        @if ($messages->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $messages->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
