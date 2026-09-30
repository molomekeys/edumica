import { langue, locale, t, tc } from '@/lib/i18n';

const liste = new Intl.ListFormat(langue, { style: 'long', type: 'conjunction' });
const date = new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'long', year: 'numeric' });

/** « A, B et C » */
export function listeFr(elements: string[]): string {
    return liste.format(elements);
}

/** « 29 septembre 2026 » */
export function dateFr(iso: string): string {
    return date.format(new Date(iso));
}

/** « 1 question », « 3 questions » (clé de traduction au format de trans_choice). */
export function pluriel(nombre: number, cle: string): string {
    return tc(cle, nombre);
}

/** Libellé d'un niveau NCLC estimé ; null : sous le NCLC 5. */
export function libelleNclc(niveau: string | null, court = false): string {
    if (niveau) {
        return `NCLC ${niveau}`;
    }

    return court ? '< NCLC 5' : t('Sous le NCLC 5');
}
