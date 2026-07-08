<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Business ERP') }} — {{ $title ?? 'Dashboard' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen">

            <!-- Mobile sidebar backdrop -->
            <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-30 bg-brand-950/60 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

            <!-- Sidebar -->
            <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-gradient-to-b from-brand-900 via-brand-900 to-brand-950 text-brand-100 flex flex-col transform transition-all duration-200 lg:translate-x-0"
                   :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', $store.sidebar.collapsed ? 'lg:w-20' : 'lg:w-64']">

                <!-- Collapse/expand toggle (desktop only) -->
                <button type="button" @click="$store.sidebar.toggle()"
                        title="Collapse or expand the sidebar"
                        class="hidden lg:grid absolute -right-3 top-20 h-6 w-6 place-items-center rounded-full bg-brand-700 text-white ring-2 ring-brand-950 hover:bg-accent-500 z-10">
                    <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="$store.sidebar.collapsed && 'rotate-180'"
                         fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                    </svg>
                </button>

                <!-- Brand -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 h-16 border-b border-white/10 shrink-0"
                   :class="$store.sidebar.collapsed && 'lg:justify-center lg:px-0'">
                    <x-application-logo class="h-9 w-9 shrink-0" />
                    <div class="leading-tight nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">
                        <span class="block text-white font-bold tracking-wide">Business ERP</span>
                        <span class="block text-[11px] text-accent-400 font-medium">Enterprise Suite</span>
                    </div>
                </a>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-4 space-y-6">
                    <div>
                        <p class="nav-label px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-brand-300/70" :class="$store.sidebar.collapsed && 'lg:hidden'">Main</p>
                        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" title="Dashboard">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Dashboard</span>
                        </x-sidebar-link>
                    </div>

                    <div>
                        <p class="nav-label px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-brand-300/70" :class="$store.sidebar.collapsed && 'lg:hidden'">Operations</p>

                        @can('sourcing.view')
                        <x-sidebar-link href="#" title="Sourcing">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Sourcing</span>
                        </x-sidebar-link>
                        @endcan

                        @can('sales.view')
                        <x-sidebar-link href="#" title="Sales">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Sales</span>
                        </x-sidebar-link>
                        @endcan

                        @can('inventory.view')
                        <x-sidebar-dropdown title="Inventory" :active="request()->routeIs('products.*', 'categories.*', 'brands.*', 'units.*')">
                            <x-slot name="icon">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                            </x-slot>

                            <x-sidebar-sublink :href="route('products.index')" :active="request()->routeIs('products.*')">
                                Products
                            </x-sidebar-sublink>
                            <x-sidebar-sublink :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                                Categories
                            </x-sidebar-sublink>
                            <x-sidebar-sublink :href="route('brands.index')" :active="request()->routeIs('brands.*')">
                                Brands
                            </x-sidebar-sublink>
                            <x-sidebar-sublink :href="route('units.index')" :active="request()->routeIs('units.*')">
                                Units
                            </x-sidebar-sublink>
                        </x-sidebar-dropdown>
                        @endcan

                        @can('contacts.view')
                        <x-sidebar-link href="#" title="Contacts">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Contacts</span>
                        </x-sidebar-link>
                        @endcan

                        @can('accounts.view')
                        <x-sidebar-link href="#" title="Accounts">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Accounts</span>
                        </x-sidebar-link>
                        @endcan

                        @can('delivery.view')
                        <x-sidebar-link href="#" title="Delivery">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Delivery</span>
                        </x-sidebar-link>
                        @endcan

                        @can('tasks.view')
                        <x-sidebar-link href="#" title="Tasks">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Tasks</span>
                        </x-sidebar-link>
                        @endcan

                        @can('hrm.view')
                        <x-sidebar-link href="#" title="HRM">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">HRM</span>
                        </x-sidebar-link>
                        @endcan

                        @can('reports.view')
                        <x-sidebar-link href="#" title="Reports">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Reports</span>
                        </x-sidebar-link>
                        @endcan
                    </div>

                    @canany(['users.view', 'roles.view', 'settings.view', 'sites.view'])
                    <div>
                        <p class="nav-label px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-brand-300/70" :class="$store.sidebar.collapsed && 'lg:hidden'">Administration</p>

                        @can('sites.view')
                        <x-sidebar-link :href="route('sites.index')" :active="request()->routeIs('sites.*')" title="Sites">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6 21v-3.375c0-.621.504-1.125 1.125-1.125h1.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            <span class="nav-label" :class="$store.sidebar.collapsed && 'lg:hidden'">Sites</span>
                        </x-sidebar-link>
                        @endcan

                        @canany(['users.view', 'roles.view'])
                        <x-sidebar-dropdown title="User Management" :active="request()->routeIs('users.*') || request()->routeIs('roles.*')">
                            <x-slot name="icon">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </x-slot>

                            @can('users.view')
                            <x-sidebar-sublink :href="route('users.index')" :active="request()->routeIs('users.*')">
                                Users
                            </x-sidebar-sublink>
                            @endcan

                            @can('roles.view')
                            <x-sidebar-sublink :href="route('roles.index')" :active="request()->routeIs('roles.*')">
                                Roles &amp; Permissions
                            </x-sidebar-sublink>
                            @endcan
                        </x-sidebar-dropdown>
                        @endcanany

                        @can('settings.view')
                        <x-sidebar-dropdown title="Settings" :active="request()->routeIs('settings.*')">
                            <x-slot name="icon">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </x-slot>

                            <x-sidebar-sublink :href="route('settings.edit')" :active="request()->routeIs('settings.edit')">
                                General Settings
                            </x-sidebar-sublink>
                        </x-sidebar-dropdown>
                        @endcan
                    </div>
                    @endcanany
                </nav>

                <!-- Sidebar footer -->
                <div class="nav-label px-5 py-4 border-t border-white/10 text-[11px] text-brand-300/60 shrink-0" :class="$store.sidebar.collapsed && 'lg:hidden'">
                    v0.1 &middot; Phase 1 — Sellable Core
                </div>
            </aside>

            <!-- Main column -->
            <div class="flex flex-col min-h-screen transition-all duration-200" :class="$store.sidebar.collapsed ? 'lg:pl-20' : 'lg:pl-64'">

                <!-- Topbar -->
                <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-slate-200 flex items-center gap-2 sm:gap-4 px-3 sm:px-6">
                    <button class="lg:hidden shrink-0 text-brand-800" @click="sidebarOpen = true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>

                    <div class="flex-1 min-w-0 hidden sm:block">
                        <div class="relative max-w-md">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                            <input type="search" placeholder="Search invoices, customers, items…"
                                   class="w-full rounded-lg border-slate-200 bg-slate-50 pl-9 pr-4 py-2 text-sm focus:border-accent-500 focus:ring-accent-500 placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="ml-auto flex items-center gap-1.5 sm:gap-3 shrink-0">
                        @php
                            $siteSwitcherOptions = Auth::user()->seesAllSites()
                                ? \App\Models\Site::where('status', true)->orderBy('name')->get()
                                : Auth::user()->sites()->where('status', true)->orderBy('name')->get();
                        @endphp
                        @if ($siteSwitcherOptions->count() > 1 || Auth::user()->seesAllSites())
                        <!-- Site selector -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="hidden sm:flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-brand-800 ring-1 ring-slate-200 hover:bg-slate-50">
                                <svg class="h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                <span>{{ Auth::user()->currentSite->name ?? 'All Sites' }}</span>
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-56 rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-slate-200"
                                 style="display: none;">
                                <p class="px-4 pt-1 pb-2 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Current Site</p>
                                @foreach ($siteSwitcherOptions as $site)
                                <form method="POST" action="{{ route('sites.switch') }}">
                                    @csrf
                                    <input type="hidden" name="site_id" value="{{ $site->id }}">
                                    <button type="submit"
                                            class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50
                                            {{ Auth::user()->current_site_id === $site->id ? 'text-accent-600 font-semibold' : 'text-slate-700' }}">
                                        {{ $site->name }}
                                        @if (Auth::user()->current_site_id === $site->id)
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endforeach

                                @if (Auth::user()->seesAllSites())
                                <div class="my-1.5 border-t border-slate-100"></div>
                                <form method="POST" action="{{ route('sites.switch') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex w-full items-center justify-between px-4 py-2 text-sm hover:bg-slate-50
                                            {{ Auth::user()->current_site_id === null ? 'text-accent-600 font-semibold' : 'text-slate-700' }}">
                                        All Sites
                                        @if (Auth::user()->current_site_id === null)
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        @endif
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @endif

                        <button class="relative rounded-full p-2 text-slate-500 hover:bg-slate-100 hover:text-brand-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-accent-500 ring-2 ring-white"></span>
                        </button>

                        <!-- User dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 sm:gap-3 rounded-lg px-1.5 sm:px-2 py-1.5 hover:bg-slate-100">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-600 to-accent-500 text-sm font-bold text-white">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden md:block text-left">
                                    <span class="block text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</span>
                                    <span class="block text-[11px] font-medium text-accent-600">{{ Auth::user()->getRoleNames()->first() ?? 'No role' }}</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 rounded-xl bg-white py-1.5 shadow-lg ring-1 ring-slate-200"
                                 style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-800">Profile</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50 hover:text-brand-800">
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Heading -->
                @isset($header)
                    <div class="px-4 sm:px-6 pt-6">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Flash messages -->
                @if (session('success') || session('error'))
                <div class="px-4 sm:px-6 pt-4" x-data="{ show: true }" x-show="show" x-transition.opacity>
                    @if (session('success'))
                    <div class="flex items-center gap-3 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-200">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button @click="show = false" class="text-emerald-400 hover:text-emerald-600">&times;</button>
                    </div>
                    @else
                    <div class="flex items-center gap-3 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 ring-1 ring-rose-200">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button @click="show = false" class="text-rose-400 hover:text-rose-600">&times;</button>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Page Content -->
                <main class="flex-1 min-w-0 px-4 sm:px-6 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
