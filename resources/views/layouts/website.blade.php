<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? '' ? ($company->name ?? config('app.name', 'Business ERP')).' — '.$title : ($company->name ?? config('app.name', 'Business ERP')) }}</title>
        <meta name="description" content="{{ $description ?? $company->tagline ?? $company->about_text ?? 'Quality products and trusted service.' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800|fraunces:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased" x-data="{ mobileNavOpen: false }">

        @include($company->ecommerce_enabled ? 'website.partials.header-shop' : 'website.partials.header-default')

        <main>
            {{ $slot }}
        </main>

        @include($company->ecommerce_enabled ? 'website.partials.footer-shop' : 'website.partials.footer-default')
    </body>
</html>
