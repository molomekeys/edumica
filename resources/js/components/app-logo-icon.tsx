import type { SVGAttributes } from 'react';

/** Le cadran Edumica : une jauge aux deux tiers, le niveau qui monte. « inverse » pour les fonds forêt. */
export default function AppLogoIcon({ inverse = false, ...props }: SVGAttributes<SVGElement> & { inverse?: boolean }) {
    return (
        <svg viewBox="0 0 40 40" fill="none" aria-hidden="true" {...props}>
            <circle cx="20" cy="20" r="16" stroke={inverse ? '#FFFFFF' : '#0E7A45'} strokeOpacity={inverse ? 0.2 : 0.18} strokeWidth="6" />
            <path d="M20 4 A16 16 0 1 1 6.14 28" stroke={inverse ? '#D3F4DF' : '#0E7A45'} strokeWidth="6" strokeLinecap="round" />
            <circle cx="20" cy="20" r="4" fill={inverse ? '#FFB59E' : '#0B2E1C'} />
        </svg>
    );
}
