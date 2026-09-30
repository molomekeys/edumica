<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Support\Langue::direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#D3F4DF">

    <title>{{ isset($title) ? $title.' · Edumica' : __('Edumica · Préparation au TCF Canada et TCF Tout public') }}</title>
    <meta name="description" content="{{ $description ?? __('Quiz gratuits et tests blancs chronométrés pour le TCF Canada et le TCF Tout public, avec un niveau estimé CECRL et NCLC.') }}">
    {{-- Balises propres à la page : Open Graph, lien canonique, données structurées. --}}
    {{ $meta ?? '' }}

    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">
    <link rel="manifest" href="/site.webmanifest">
    <x-polices />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh">
    {{ $slot }}
</body>
</html>
