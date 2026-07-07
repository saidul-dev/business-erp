@props(['title', 'active' => false])

{{-- Opens automatically when a child route is active; chevron rotates with state.
     If the sidebar is collapsed to icon rail, clicking auto-expands it so the
     sublinks are readable instead of trying to clip/float a flyout panel. --}}
<div x-data="{ open: @js((bool) $active) }">
    <button type="button"
            @click="if ($store.sidebar.collapsed) { $store.sidebar.expand(); open = true; } else { open = !open; }"
            x-bind:class="$store.sidebar.collapsed && 'lg:justify-center lg:px-0'"
            x-bind:title="$store.sidebar.collapsed ? @js($title) : null"
            class="w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-colors {{ $active ? 'font-semibold bg-white/10 text-white border-l-2 border-accent-400' : 'font-medium text-brand-200 hover:bg-white/5 hover:text-white' }}">
        <span class="shrink-0">{{ $icon ?? '' }}</span>
        <span class="nav-label flex-1 text-left" x-bind:class="$store.sidebar.collapsed && 'lg:hidden'">{{ $title }}</span>
        <svg class="nav-label h-4 w-4 shrink-0 transition-transform duration-200"
             x-bind:class="[open && 'rotate-180', $store.sidebar.collapsed && 'lg:hidden']"
             fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div x-show="open" x-collapse.duration.200ms x-cloak
         class="mt-1 ml-5 space-y-0.5 border-l border-white/10 pl-3">
        {{ $slot }}
    </div>
</div>
