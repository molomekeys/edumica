<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#D3F4DF">

    <title>{{ isset($title) ? $title.' · Edumica' : 'Edumica · Préparation au TCF Canada et TCF Tout public' }}</title>
    <meta name="description" content="Quiz gratuits et tests blancs chronométrés pour le TCF Canada et le TCF Tout public, avec un niveau estimé CECRL et NCLC.">

    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=Figtree:wght@400;500;600;700;800&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh">
    {{ $slot }}
</body>
</html>
