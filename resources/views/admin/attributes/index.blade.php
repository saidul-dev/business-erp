<x-app-layout>
    <x-slot name="title">{{ __('Attributes') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Attributes') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Reusable variant options like Color and Size') }}</p>
            </div>
            @can('inventory.create')
                <a href="{{ route('attributes.create') }}" class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">{{ __('Add Attribute') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">{{ __('Name') }}</th>
                    <th class="px-4 py-3">{{ __('Values') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attributes as $attribute)
                    <tr>
                        <td class="px-4 py-3 font-medium text-brand-900">{{ $attribute->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $attribute->values_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('inventory.edit')
                                <a href="{{ route('attributes.edit', $attribute) }}" class="text-brand-700 hover:underline">{{ __('Edit') }}</a>
                            @endcan
                            @can('inventory.delete')
                                <form method="POST" action="{{ route('attributes.destroy', $attribute) }}" class="inline" onsubmit="return confirm('{{ __('Delete this attribute?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="ml-3 text-rose-600 hover:underline">{{ __('Delete') }}</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">{{ __('No attributes yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attributes->links() }}</div>
</x-app-layout>
