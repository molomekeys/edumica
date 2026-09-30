import { usePage } from '@inertiajs/react';

/** URL de la page courante : chemin et paramètres de requête, pour marquer la navigation active. */
export function useCurrentUrl() {
    const url = new URL(usePage().url, window.location.origin);

    return {
        chemin: url.pathname,
        parametres: url.searchParams,
        estActif: (href: string) => url.pathname === new URL(href, window.location.origin).pathname,
        estSousChemin: (...prefixes: string[]) =>
            prefixes.some((prefixe) => url.pathname === prefixe || url.pathname.startsWith(`${prefixe}/`)),
    };
}
