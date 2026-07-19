<x-app-layout>
    <x-slot name="title">{{ __('AI Terminal') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('AI Terminal') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Type a plain-language question and get today\'s figures instantly — e.g. "ajke balance koto", "today sale koto"') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-slate-950 shadow-sm ring-1 ring-slate-800 overflow-hidden"
         x-data="aiTerminal({ askUrl: '{{ route('ai-terminal.ask') }}' })">

        <!-- Window chrome -->
        <div class="flex items-center gap-2 px-4 py-3 bg-slate-900 border-b border-slate-800">
            <span class="h-3 w-3 rounded-full bg-rose-500"></span>
            <span class="h-3 w-3 rounded-full bg-amber-400"></span>
            <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
            <span class="ml-3 text-xs font-medium text-slate-400">{{ __('admin@erp — ai-terminal') }}</span>
        </div>

        <!-- Scrollback -->
        <div x-ref="scrollback" class="h-[28rem] overflow-y-auto px-5 py-4 font-mono text-sm space-y-3">
            <p class="text-slate-500">{{ __('Ask about today\'s sale, purchase, profit/loss, or balance. Bangla, Banglish, or English all work.') }}</p>

            <template x-for="(entry, index) in history" :key="index">
                <div class="space-y-1">
                    <p class="text-emerald-400">
                        <span class="text-slate-500">&gt;</span> <span x-text="entry.question"></span>
                    </p>
                    <p x-show="entry.reply" x-text="entry.reply" class="text-slate-100 pl-4"></p>
                    <p x-show="entry.error" x-text="entry.error" class="text-rose-400 pl-4"></p>
                </div>
            </template>

            <p x-show="asking" class="text-slate-500 pl-4">{{ __('…') }}</p>
        </div>

        <!-- Input -->
        <form @submit.prevent="ask" class="flex items-center gap-2 border-t border-slate-800 bg-slate-900 px-5 py-3">
            <span class="font-mono text-emerald-400">&gt;</span>
            <input x-ref="input" x-model="query" type="text" autocomplete="off"
                   :disabled="asking"
                   placeholder="{{ __('e.g. ajke balance koto') }}"
                   class="flex-1 border-0 bg-transparent font-mono text-sm text-slate-100 placeholder:text-slate-600 focus:ring-0">
        </form>
    </div>
</x-app-layout>
