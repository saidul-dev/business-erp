<x-app-layout>
    <x-slot name="title">Users</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">Users</h2>
                <p class="text-sm text-slate-500 mt-0.5">Team members and their assigned roles</p>
            </div>
            @can('users.create')
            <button class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add User
            </button>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">User</th>
                    <th class="px-5 py-3 font-semibold">Email</th>
                    <th class="px-5 py-3 font-semibold">Role(s)</th>
                    <th class="px-5 py-3 font-semibold">Joined</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($users as $user)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-brand-600 to-accent-500 text-xs font-bold text-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                            <span class="font-semibold text-slate-800">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $user->email }}</td>
                    <td class="px-5 py-3">
                        @forelse ($user->roles as $role)
                            <span class="mr-1 inline-flex rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 ring-1 ring-brand-200">{{ $role->name }}</span>
                        @empty
                            <span class="text-xs text-slate-400">No role</span>
                        @endforelse
                    </td>
                    <td class="px-5 py-3 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($users->hasPages())
        <div class="border-t border-slate-100 px-5 py-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
