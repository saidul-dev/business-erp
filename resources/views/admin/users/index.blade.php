<x-app-layout>
    <x-slot name="title">Users</x-slot>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">Users</h2>
                <p class="text-sm text-slate-500 mt-0.5">Team members and their assigned roles</p>
            </div>
            @can('users.create')
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add User
            </a>
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
                    <th class="px-5 py-3 font-semibold text-right">Actions</th>
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
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-1">
                            @can('users.edit')
                            <a href="{{ route('users.edit', $user) }}" title="Edit"
                               class="rounded-lg p-2 text-slate-400 hover:bg-brand-50 hover:text-brand-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                            </a>
                            @endcan
                            @can('users.delete')
                            @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('Delete user \'{{ $user->name }}\'? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete"
                                        class="rounded-lg p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </form>
                            @endif
                            @endcan
                        </div>
                    </td>
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
