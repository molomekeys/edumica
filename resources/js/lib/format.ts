const liste = new Intl.ListFormat('fr', { style: 'long', type: 'conjunction' });
const date = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });

/** « A, B et C » */
export function listeFr(elements: string[]): string {
    return liste.format(elements);
}

/** « 29 septembre 2026 » */
export function dateFr(iso: string): string {
    return date.format(new Date(iso));
}

/** « 1 question », « 3 questions » */
export function pluriel(nombre: number, singulier: string, pluriel = `${singulier}s`): string {
    return `${nombre} ${nombre > 1 ? pluriel : singulier}`;
}

/** Libellé d'un niveau NCLC estimé ; null : sous le NCLC 5. */
export function libelleNclc(niveau: string | null, court = false): string {
    if (niveau) {
        return `NCLC ${niveau}`;
    }

    return court ? '< NCLC 5' : 'Sous le NCLC 5';
}
