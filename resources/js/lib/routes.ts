/**
 * URL des routes Laravel utilisées par les dashboards, sous les mêmes noms que dans routes/web.php.
 */
const routes = {
    accueil: () => '/',
    langue: (langue: 'fr' | 'ar') => `/langue/${langue}`,
    deconnexion: () => '/deconnexion',

    espace: () => '/espace',
    'espace.tests-blancs': () => '/espace/tests-blancs',
    'espace.resultats': () => '/espace/resultats',

    'test-blanc': (epreuve: string) => `/test-blanc/${epreuve}`,
    'test-blanc.complet': () => '/test-blanc',
    'test-blanc.sauvegarder': (tentative: number) => `/test-blanc/tentatives/${tentative}`,
    'test-blanc.terminer': (tentative: number) => `/test-blanc/tentatives/${tentative}/terminer`,
    'test-blanc.abandonner': (tentative: number) => `/test-blanc/tentatives/${tentative}`,
    'test-blanc.resultat': (tentative: number) => `/test-blanc/tentatives/${tentative}/resultat`,

    'admin.questions': (filtres: { epreuve?: string; q?: string } = {}) => avecRequete('/admin', filtres),
    'admin.questions.creer': (filtres: { epreuve?: string } = {}) => avecRequete('/admin/questions/creer', filtres),
    'admin.questions.enregistrer': () => '/admin/questions',
    'admin.questions.modifier': (question: number) => `/admin/questions/${question}`,
    'admin.questions.mettre-a-jour': (question: number) => `/admin/questions/${question}`,
    'admin.questions.supprimer': (question: number) => `/admin/questions/${question}`,

    'admin.articles': (filtres: { q?: string; categorie?: string; statut?: string } = {}) => avecRequete('/admin/articles', filtres),
    'admin.articles.creer': () => '/admin/articles/creer',
    'admin.articles.enregistrer': () => '/admin/articles',
    'admin.articles.image': () => '/admin/articles/images',
    'admin.articles.modifier': (article: number) => `/admin/articles/${article}`,
    'admin.articles.mettre-a-jour': (article: number) => `/admin/articles/${article}`,
    'admin.articles.supprimer': (article: number) => `/admin/articles/${article}`,

    // Site public (Livewire) : liens classiques, sans visite Inertia.
    article: (slug: string) => `/articles/${slug}`,
};

type Routes = typeof routes;

export function route<Nom extends keyof Routes>(nom: Nom, ...parametres: Parameters<Routes[Nom]>): string {
    return (routes[nom] as (...args: Parameters<Routes[Nom]>) => string)(...parametres);
}

/** Test blanc d'une épreuve, ou test complet sans épreuve. */
export function routeTestBlanc(epreuve?: string | null): string {
    return epreuve ? route('test-blanc', epreuve) : route('test-blanc.complet');
}

function avecRequete(chemin: string, parametres: Record<string, string | undefined>): string {
    const requete = new URLSearchParams(
        Object.entries(parametres).filter((entree): entree is [string, string] => Boolean(entree[1])),
    ).toString();

    return requete ? `${chemin}?${requete}` : chemin;
}
