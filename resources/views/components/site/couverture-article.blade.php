{{-- Couverture d'un article : l'image téléversée, sinon une illustration aux couleurs de sa catégorie. --}}
@props(['article', 'chargement' => 'lazy'])

@php
    // Catégorie => [fond, couleur des arcs, icône, couleur de l'icône, fond de l'icône].
    [$fond, $arcs, $icone, $couleur, $pastille] = match ($article->categorie) {
        'Compréhension orale' => ['bg-menthe', '#0E7A45', 'casque', 'text-vert', 'bg-white'],
        'Compréhension écrite' => ['bg-peche', '#0B2E1C', 'livre', 'text-foret', 'bg-white/70'],
        'Expression écrite' => ['bg-foret', '#D3F4DF', 'crayon', 'text-foret', 'bg-menthe'],
        'Expression orale' => ['bg-brume', '#0E7A45', 'micro', 'text-white', 'bg-vert'],
        'Immigration Canada' => ['bg-peche-clair', '#0B2E1C', 'valide', 'text-white', 'bg-foret'],
        default => ['bg-vert', '#FFFFFF', 'boussole', 'text-foret', 'bg-peche'],
    };
@endphp

@if ($url = $article->urlCouverture())
    <img src="{{ $url }}" alt="" loading="{{ $chargement }}" {{ $attributes->merge(['class' => 'size-full object-cover']) }}>
@else
    <div {{ $attributes->merge(['class' => "relative isolate flex size-full items-center justify-center overflow-hidden {$fond}"]) }} aria-hidden="true">
        <x-arcs :couleur="$arcs" class="-right-[18%] -bottom-[45%] aspect-square w-[85%]" />
        <x-arcs :couleur="$arcs" class="-top-[55%] -left-[30%] aspect-square w-[70%] opacity-60" />
        <div class="flex aspect-square w-[22%] min-w-12 items-center justify-center rounded-full {{ $pastille }} {{ $couleur }} shadow-[0_18px_40px_rgba(11,46,28,0.12)]">
            <x-icone :nom="$icone" class="size-1/2" />
        </div>
    </div>
@endif
