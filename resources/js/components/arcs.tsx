import { cn } from '@/lib/utils';

/** Motif d'arcs du cadran, pour les grands aplats. */
export function Arcs({ couleur = '#0E7A45', className }: { couleur?: string; className?: string }) {
    return (
        <svg className={cn('pointer-events-none absolute -z-10', className)} viewBox="0 0 360 360" fill="none" aria-hidden="true">
            <circle cx="180" cy="180" r="60" stroke={couleur} strokeOpacity="0.13" strokeWidth="22" strokeDasharray="251 126" transform="rotate(-90 180 180)" />
            <circle cx="180" cy="180" r="112" stroke={couleur} strokeOpacity="0.1" strokeWidth="22" strokeDasharray="469 235" transform="rotate(-90 180 180)" />
            <circle cx="180" cy="180" r="164" stroke={couleur} strokeOpacity="0.07" strokeWidth="22" strokeDasharray="687 343" transform="rotate(-90 180 180)" />
        </svg>
    );
}
