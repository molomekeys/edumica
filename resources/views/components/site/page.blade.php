{{-- Gabarit des pages du site : en-tête, contenu, pied de page. --}}
<div class="flex min-h-dvh flex-col">
    <x-site.header />

    <main {{ $attributes->merge(['class' => 'flex-1']) }}>
        {{ $slot }}
    </main>

    <x-site.footer />
</div>
