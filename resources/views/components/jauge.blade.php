{{-- Jauge CECRL de A1 à C2, aiguille sur B2. Avec « anime », les arcs se tracent et l'aiguille balaie jusqu'au niveau. --}}
@props(['anime' => false])

@php
    $arcs = [
        ['M 50.1 225.3 A 180 180 0 0 1 71.8 144.1', '#17A35F'],
        ['M 76.5 136.0 A 180 180 0 0 1 136.0 76.5', '#17A35F'],
        ['M 144.1 71.8 A 180 180 0 0 1 225.3 50.1', '#17A35F'],
        ['M 234.7 50.1 A 180 180 0 0 1 315.9 71.8', '#17A35F'],
        ['M 324.0 76.5 A 180 180 0 0 1 383.5 136.0', '#E1EFE6'],
        ['M 388.2 144.1 A 180 180 0 0 1 409.9 225.3', '#E1EFE6'],
    ];
    $libelles = [[1, 178, 'A1'], [61, 68, 'A2'], [169, 5, 'B1'], [291, 5, 'B2'], [399, 68, 'C1'], [459, 178, 'C2']];
@endphp

<svg {{ $attributes }} viewBox="-24 -34 508 270" fill="none" aria-hidden="true">
    <g stroke-width="42">
        @foreach ($arcs as $i => [$trace, $couleur])
            <path d="{{ $trace }}" stroke="{{ $couleur }}" @if ($anime) pathLength="1" class="trace" style="--delai: {{ 100 + $i * 110 }}ms" @endif />
        @endforeach
    </g>
    <g font-family="Figtree, sans-serif" font-size="24" font-weight="700" fill="#4B6657" text-anchor="middle">
        @foreach ($libelles as $i => [$x, $y, $niveau])
            <text x="{{ $x }}" y="{{ $y }}" @if ($niveau === 'B2') fill="#0B2E1C" @endif @if ($anime) class="jauge-libelle" style="--delai: {{ 250 + $i * 110 }}ms" @endif>{{ $niveau }}</text>
        @endforeach
    </g>
    <g transform="translate(230 230)">
        {{-- Le cercle invisible centre la boîte de l'aiguille sur son pivot. --}}
        <g @class(['jauge-aiguille' => $anime])>
            <circle r="130" />
            <path d="M0 0 L33.6 -125.6" stroke="#0B2E1C" stroke-width="7.5" stroke-linecap="round"/>
        </g>
        <circle r="15.5" fill="#0B2E1C"/>
    </g>
</svg>
