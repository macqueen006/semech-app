<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @yield('meta')

    <link href="https://api.fontshare.com/v2/css?f[]=switzer@200,300,400,500,600,800,900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{asset('favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('site.webmanifest') }}" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    @stack('styles')
    <script>
        // This code should be added to <head>.
        // It's used to prevent page load glitches.
        const html = document.querySelector('html');
        const isLightOrAuto = localStorage.getItem('hs_theme') === 'light' || (localStorage.getItem('hs_theme') === 'auto' && !window.matchMedia('(prefers-color-scheme: dark)').matches);
        const isDarkOrAuto = localStorage.getItem('hs_theme') === 'dark' || (localStorage.getItem('hs_theme') === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isLightOrAuto && html.classList.contains('dark')) html.classList.remove('dark');
        else if (isDarkOrAuto && html.classList.contains('light')) html.classList.remove('light');
        else if (isDarkOrAuto && !html.classList.contains('dark')) html.classList.add('dark');
        else if (isLightOrAuto && !html.classList.contains('light')) html.classList.add('light');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="transition-[background] bg-primary text-body duration-500 ease-in-out font-display text-[16px] m-0  min-h-full font-[400]">
<x-layouts.header />
{{ $slot }}
<x-layouts.footer />
@stack('modal')
@stack('scripts')
</body>
</html>

