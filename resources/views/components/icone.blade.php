@props(['nom', 'epaisseur' => 2])

@php
    $traces = [
        'boussole' => '<circle cx="12" cy="12" r="9"/><path d="M15.5 8.5l-2 5-5 2 2-5z"/>',
        'chrono' => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/><path d="M9 2h6"/>',
        'horloge' => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2"/>',
        'valide' => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
        'jauge' => '<path d="M4 16a8 8 0 1 1 16 0"/><path d="M12 16l4-5"/>',
        'telephone' => '<rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18h2"/>',
        'casque' => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/>',
        'livre' => '<path d="M3 5h7a2 2 0 0 1 2 2v12a2 2 0 0 0-2-2H3z"/><path d="M21 5h-7a2 2 0 0 0-2 2v12a2 2 0 0 1 2-2h7z"/>',
        'crayon' => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13 7l4 4"/>',
        'micro' => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0"/><path d="M12 18v3"/>',
        'coche' => '<path d="M5 12l5 5L20 7"/>',
        'croix' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'droite' => '<path d="M9 6l6 6-6 6"/>',
        'gauche' => '<path d="M15 6l-6 6 6 6"/>',
        'bas' => '<path d="M6 9l6 6 6-6"/>',
        'partager' => '<path d="M12 3v12"/><path d="M7 8l5-5 5 5"/><path d="M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a14 14 0 0 1 0 18a14 14 0 0 1 0-18"/>',
        'fleche' => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
        'article' => '<path d="M6 3h8l5 5v13H6z"/><path d="M14 3v5h5"/><path d="M9 13h7M9 17h5"/>',
        'question' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.1"/><path d="M12 17h.01"/>',
        'enveloppe' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M4 7l8 6 8-6"/>',
        'relancer' => '<path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/>',
        'sortie' => '<path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/><path d="M10 16l-4-4 4-4"/><path d="M6 12h10"/>',
        'maison' => '<path d="M4 11l8-7 8 7"/><path d="M6 10v10h12V10"/>',
        'bouclier' => '<path d="M12 3l8 3v6c0 4.5-3.5 8-8 9-4.5-1-8-4.5-8-9V6z"/>',
    ];
@endphp

{{-- Les flèches horizontales se retournent en arabe (lecture de droite à gauche). --}}
<svg {{ $attributes->class(['rtl:-scale-x-100' => in_array($nom, ['droite', 'gauche', 'fleche'])]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $epaisseur }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $traces[$nom] !!}</svg>
