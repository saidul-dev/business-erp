<x-app-layout>
    <x-slot name="title">{{ __('AI Terminal') }}</x-slot>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-bold text-brand-900">{{ __('AI Terminal') }}</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Type a plain-language question and get today\'s figures instantly — e.g. "ajke balance koto", "today sale koto"') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl bg-brand-950 shadow-sm ring-1 ring-brand-900 overflow-hidden"
         x-data="aiTerminal({ askUrl: '{{ route('ai-terminal.ask') }}' })">

        <!-- Header bar -->
        <div class="flex items-center justify-between gap-3 px-5 py-3 bg-brand-900 border-b border-white/5">
            <div class="flex items-center gap-2.5">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-accent-500/10 ring-1 ring-accent-400/30">
                    <svg class="h-4 w-4 text-accent-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5 9 9.75 6.75 12m4.5 3h4.5M4.5 18.75h15A2.25 2.25 0 0 0 21.75 16.5V6a2.25 2.25 0 0 0-2.25-2.25h-15A2.25 2.25 0 0 0 2.25 6v10.5A2.25 2.25 0 0 0 4.5 18.75Z" />
                    </svg>
                </span>
                <span class="text-sm font-semibold text-white">{{ __('AI Terminal') }}</span>
                <span class="flex items-center gap-1.5 text-[11px] text-brand-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400 motion-safe:animate-pulse"></span>
                    {{ __('Live') }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden sm:inline text-[11px] font-medium uppercase tracking-wider text-brand-400">{{ __('Powered by Gemini') }}</span>
                <button type="button" @click="clear()" x-show="history.length > 0" x-cloak
                        class="flex items-center gap-1 rounded-full bg-white/5 hover:bg-white/10 ring-1 ring-white/10 px-2.5 py-1 text-[11px] font-medium text-brand-300 hover:text-white transition-colors">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    {{ __('Clear') }}
                </button>
            </div>
        </div>

        <!-- Scrollback -->
        <div x-ref="scrollback" class="h-[28rem] overflow-y-auto px-5 py-4 space-y-4 text-sm">
            <div x-show="history.length === 0" x-cloak class="space-y-3">
                <p class="text-brand-300/90 leading-relaxed">{{ __('Ask about today\'s sale, purchase, profit/loss, or balance. Bangla, Banglish, or English all work.') }}</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="ask('ajke balance koto')"
                            class="rounded-full bg-white/5 hover:bg-white/10 ring-1 ring-white/10 px-3 py-1.5 text-xs font-medium text-accent-300 transition-colors">
                        {{ __('Balance') }}
                    </button>
                    <button type="button" @click="ask('today sale koto')"
                            class="rounded-full bg-white/5 hover:bg-white/10 ring-1 ring-white/10 px-3 py-1.5 text-xs font-medium text-accent-300 transition-colors">
                        {{ __('Today Sale') }}
                    </button>
                    <button type="button" @click="ask('today purchase koto')"
                            class="rounded-full bg-white/5 hover:bg-white/10 ring-1 ring-white/10 px-3 py-1.5 text-xs font-medium text-accent-300 transition-colors">
                        {{ __('Today Purchase') }}
                    </button>
                    <button type="button" @click="ask('ajke profit naki loss')"
                            class="rounded-full bg-white/5 hover:bg-white/10 ring-1 ring-white/10 px-3 py-1.5 text-xs font-medium text-accent-300 transition-colors">
                        {{ __('Today Profit/Loss') }}
                    </button>
                </div>
            </div>

            <template x-for="(entry, index) in history" :key="index">
                <div class="space-y-1.5">
                    <p class="flex items-baseline gap-2">
                        <span class="w-8 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-brand-400">{{ __('You') }}</span>
                        <span x-text="entry.question" class="font-mono text-white"></span>
                    </p>
                    <p x-show="entry.reply" class="flex items-baseline gap-2">
                        <span class="w-8 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-accent-400">{{ __('AI') }}</span>
                        <span x-text="entry.reply" class="font-mono font-semibold text-slate-100"></span>
                    </p>
                    <p x-show="entry.error" class="flex items-baseline gap-2">
                        <span class="w-8 shrink-0 text-[10px] font-semibold uppercase tracking-wide text-rose-400">{{ __('AI') }}</span>
                        <span x-text="entry.error" class="font-mono text-rose-400"></span>
                    </p>
                </div>
            </template>

            <p x-show="asking" class="flex items-center gap-1.5 pl-10">
                <span class="h-1.5 w-1.5 rounded-full bg-brand-400 motion-safe:animate-bounce [animation-delay:-0.3s]"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-brand-400 motion-safe:animate-bounce [animation-delay:-0.15s]"></span>
                <span class="h-1.5 w-1.5 rounded-full bg-brand-400 motion-safe:animate-bounce"></span>
            </p>
        </div>

        <!-- Input -->
        <form @submit.prevent="ask()" class="flex items-center gap-2 border-t border-white/5 bg-brand-900 px-5 py-3">
            <span class="font-mono text-sm text-accent-400 select-none">{{ __('admin@erp:~$') }}</span>
            <input x-ref="input" x-model="query" type="text" autocomplete="off"
                   :disabled="asking"
                   placeholder="{{ __('e.g. ajke balance koto') }}"
                   class="flex-1 border-0 bg-transparent font-mono text-sm text-white caret-accent-400 placeholder:text-brand-500/70 focus:ring-0">
        </form>
    </div>
</x-app-layout>
