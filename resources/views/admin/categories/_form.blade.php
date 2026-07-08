@php $editing = isset($category); @endphp

<div class="max-w-lg rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 space-y-5">
    <div>
        <x-input-label for="name" value="Category Name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                      :value="old('name', $category->name ?? '')" required autofocus placeholder="e.g. Electronics" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="parent_id" value="Parent Category (optional)" />
        <select id="parent_id" name="parent_id"
                class="mt-1 block w-full rounded-lg border-slate-300 focus:border-accent-500 focus:ring-accent-500">
            <option value="">None — top-level category</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
    </div>
</div>

<div class="mt-5 flex items-center gap-3">
    <x-primary-button>{{ $editing ? 'Update Category' : 'Create Category' }}</x-primary-button>
    <a href="{{ route('categories.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50">Cancel</a>
</div>
