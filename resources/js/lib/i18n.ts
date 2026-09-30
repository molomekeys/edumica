/**
 * Traductions des dashboards. Comme côté Laravel, le texte source est le français et sert de clé :
 * lang/ar.json donne sa traduction arabe. La langue est celle de la page (<html lang dir>), fixée par
 * le serveur ; changer de langue recharge la page, donc les traductions sont chargées une seule fois.
 */
type Remplacements = Record<string, string | number>;

const fichiers = import.meta.glob<Record<string, string>>('../../../lang/*.json', { import: 'default' });

let traductions: Record<string, string> = {};

export const langue = document.documentElement.lang || 'fr';

export const direction: 'ltr' | 'rtl' = document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr';

/** Locale des formats de nombres et de dates : chiffres latins aussi en arabe, comme sur le site. */
export const locale = langue === 'ar' ? 'ar-u-nu-latn' : 'fr-FR';

/** À attendre avant le premier rendu. Le français n'a pas de fichier : les clés sont déjà le texte. */
export async function chargerTraductions(): Promise<void> {
    const fichier = fichiers[`../../../lang/${langue}.json`];

    if (fichier) {
        traductions = await fichier();
    }
}

/** Traduit un texte français ; « :nom » est remplacé par remplacements.nom. */
export function t(cle: string, remplacements: Remplacements = {}): string {
    return remplacer(traductions[cle] ?? cle, remplacements);
}

/**
 * Traduit selon un nombre, au format de trans_choice de Laravel :
 * « :count question|:count questions », ou avec des plages « {0} …|{1} …|[2,10] …|[11,*] … ».
 */
export function tc(cle: string, nombre: number, remplacements: Remplacements = {}): string {
    const formes = (traductions[cle] ?? cle).split('|');
    let texte: string | undefined;
    const simples: string[] = [];

    for (const forme of formes) {
        const exacte = forme.match(/^\{(\d+)\}\s?([\s\S]*)$/);
        const plage = forme.match(/^\[(\d+|\*),\s*(\d+|\*)\]\s?([\s\S]*)$/);

        if (exacte) {
            if (Number(exacte[1]) === nombre) {
                texte ??= exacte[2];
            }
        } else if (plage) {
            const min = plage[1] === '*' ? -Infinity : Number(plage[1]);
            const max = plage[2] === '*' ? Infinity : Number(plage[2]);

            if (nombre >= min && nombre <= max) {
                texte ??= plage[3];
            }
        } else {
            simples.push(forme);
        }
    }

    // Sans plage : singulier jusqu'à 1 (règle du français), pluriel au-delà.
    texte ??= simples[nombre > 1 && simples.length > 1 ? 1 : 0] ?? formes[0];

    return remplacer(texte, { count: nombre, ...remplacements });
}

function remplacer(texte: string, remplacements: Remplacements): string {
    return Object.entries(remplacements)
        .sort(([a], [b]) => b.length - a.length)
        .reduce((resultat, [nom, valeur]) => resultat.replaceAll(`:${nom}`, String(valeur)), texte);
}
