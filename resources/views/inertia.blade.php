<!DOCTYPE html>
{{-- Vue racine des dashboards Inertia (espace utilisateur, test blanc, admin), rendus côté client. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Support\Langue::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#EEF8F2">
    <meta name="robots" content="noindex">

    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">
    <link rel="manifest" href="/site.webmanifest">
    <x-polices />

    @viteReactRefresh
    @vite(['resources/css/dashboard.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
    <x-inertia::head>
        <title>Edumica</title>
    </x-inertia::head>
</head>
<body>
    <x-inertia::app />
</body>
</html>
