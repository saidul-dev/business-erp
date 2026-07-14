<x-app-layout>
    <x-slot name="title">{{ __('Attributes') }}</x-slot>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">{{ __('Attributes') }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ __('Reusable variant options like Color and Size') }}</p>
            </div>
            @can('inventory.create')
                <button type="button" @click="$dispatch('open-modal', 'attribute-create')"
                        class="rounded-lg bg-brand-800 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-900">{{ __('Add Attribute') }}</button>
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

    @can('inventory.create')
    <x-modal name="attribute-create" max-width="md" :show="$errors->any()" focusable>
        <div class="p-6" x-data="{ values: {{ Illuminate\Support\Js::from(old('values', [''])) }} }">
            <h2 class="text-lg font-bold text-brand-900">{{ __('Add Attribute') }}</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ __('Added attributes appear in the list behind this — check it first to avoid duplicates.') }}</p>

            <form method="POST" action="{{ route('attributes.store') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <x-input-label for="modal_attribute_name" :value="__('Attribute Name')" />
                    <x-text-input id="modal_attribute_name" name="name" type="text" class="mt-1 block w-full"
                                  :value="old('name')" required autofocus placeholder="{{ __('e.g. Color') }}" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label :value="__('Values')" />
                    <p class="text-xs text-slate-400 mb-2">{{ __('e.g. Red, Blue, Black') }}</p>
                    <template x-for="(value, i) in values" :key="i">
                        <div class="mt-2 flex items-center gap-2">
                            <input type="text" :name="'values[' + i + ']'" x-model="values[i]"
                                   class="block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500" placeholder="{{ __('Value') }}">
                            <button type="button" @click="values.splice(i, 1)" x-show="values.length > 1"
                                    class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 ring-1 ring-rose-200 hover:bg-rose-50">{{ __('Remove') }}</button>
                        </div>
                    </template>
                    <button type="button" @click="values.push('')"
                            class="mt-3 rounded-lg bg-brand-800/5 px-4 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-800/10">+ {{ __('Add Value') }}</button>
                    <x-input-error class="mt-2" :messages="$errors->get('values')" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>{{ __('Create Attribute') }}</x-primary-button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </x-modal>
    @endcan
</x-app-layout>
