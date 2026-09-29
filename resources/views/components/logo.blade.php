{{-- Le cadran : une jauge aux deux tiers, le niveau qui monte. --}}
@props(['inverse' => false])

<svg {{ $attributes }} viewBox="0 0 40 40" fill="none" aria-hidden="true">
    <circle cx="20" cy="20" r="16" stroke="{{ $inverse ? '#FFFFFF' : '#0E7A45' }}" stroke-opacity="{{ $inverse ? '0.2' : '0.18' }}" stroke-width="6"/>
    <path d="M20 4 A16 16 0 1 1 6.14 28" stroke="{{ $inverse ? '#D3F4DF' : '#0E7A45' }}" stroke-width="6" stroke-linecap="round"/>
    <circle cx="20" cy="20" r="4" fill="{{ $inverse ? '#FFB59E' : '#0B2E1C' }}"/>
</svg>
