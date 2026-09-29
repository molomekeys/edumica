<!DOCTYPE html>
{{-- Vue racine des dashboards Inertia (espace utilisateur, test blanc, admin), rendus côté client. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#EEF8F2">
    <meta name="robots" content="noindex">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=Figtree:wght@400;500;600;700;800&display=swap">

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
