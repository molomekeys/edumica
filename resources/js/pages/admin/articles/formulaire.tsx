import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, CalendarClock, ExternalLink, Eye, Globe, RefreshCw, Save, Send } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { ApercuArticle } from '@/components/article/apercu';
import { BadgeStatut } from '@/components/article/badge-statut';
import { ChampCouverture, TAILLE_MAX_COUVERTURE, useApercuCouverture } from '@/components/article/couverture';
import { EditeurArticle, MOTS_PAR_MINUTE } from '@/components/article/editeur';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { route } from '@/lib/routes';
import { cn } from '@/lib/utils';

type Statut = 'brouillon' | 'publie';

type Article = {
    id: number | null;
    titre: string;
    slug: string;
    extrait: string;
    contenu: string;
    categorie: string;
    statut: Statut;
    /** Format de <input type="datetime-local">, vide si non daté. */
    publie_le: string;
    meta_titre: string;
    meta_description: string;
    couverture: string | null;
    temps_lecture: number;
    /** Adresse publique, si l'article est en ligne. */
    url: string | null;
};

type Props = {
    article: Article;
    categories: string[];
};

const LIMITES = { extrait: 300, meta_titre: 70, meta_description: 160 };

/** « Préparer le TCF » → « preparer-le-tcf », comme Str::slug. */
function slugifier(texte: string): string {
    return texte
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/['’]/g, '-')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 180);
}

function valeursInitiales(article: Article) {
    return {
        titre: article.titre,
        slug: article.slug,
        extrait: article.extrait,
        contenu: article.contenu,
        categorie: article.categorie,
        statut: article.statut,
        publie_le: article.publie_le,
        meta_titre: article.meta_titre,
        meta_description: article.meta_description,
        couverture: null as File | null,
        supprimer_couverture: false,
    };
}

export default function FormulaireArticle({ article, categories }: Props) {
    const { auth } = usePage().props;
    const form = useForm(valeursInitiales(article));
    const { data, setData, errors, processing, isDirty } = form;

    // En création, le slug suit le titre tant qu'il n'a pas été saisi à la main.
    const [slugSaisi, setSlugSaisi] = useState(article.id !== null);
    const [mots, setMots] = useState(0);
    const [apercu, setApercu] = useState(false);

    const couvertureEnregistree = data.supprimer_couverture ? null : article.couverture;
    const imageCouverture = useApercuCouverture(data.couverture, couvertureEnregistree);
    const tempsLecture = Math.max(1, Math.ceil(mots / MOTS_PAR_MINUTE));
    const programme = data.statut === 'publie' && data.publie_le !== '' && new Date(data.publie_le) > new Date();
    const dejaPublie = article.statut === 'publie';

    const libelleEnregistrer =
        data.statut === 'brouillon' ? 'Enregistrer le brouillon' : programme ? 'Programmer' : dejaPublie ? 'Mettre à jour' : 'Publier';

    const enregistrer = useCallback(() => {
        const options = {
            preserveScroll: true,
            onError: () => toast.error('Certains champs sont à corriger.'),
            onSuccess: (page: { props: object }) => {
                const enregistre = (page.props as { article?: Article }).article;

                if (enregistre) {
                    const valeurs = valeursInitiales(enregistre);
                    form.setDefaults(valeurs);
                    form.setData(valeurs);
                    setSlugSaisi(true);
                }
            },
        };

        if (article.id) {
            // Fichier possible : envoi en multipart, donc POST avec la méthode PUT simulée.
            form.transform((valeurs) => ({ ...valeurs, _method: 'put' }));
            form.post(route('admin.articles.mettre-a-jour', article.id), options);
        } else {
            form.transform((valeurs) => valeurs);
            form.post(route('admin.articles.enregistrer'), options);
        }
    }, [article.id, form]);

    // Ctrl/Cmd + S enregistre.
    useEffect(() => {
        const raccourci = (evenement: KeyboardEvent) => {
            if ((evenement.metaKey || evenement.ctrlKey) && evenement.key.toLowerCase() === 's') {
                evenement.preventDefault();

                if (!processing) {
                    enregistrer();
                }
            }
        };

        window.addEventListener('keydown', raccourci);

        return () => window.removeEventListener('keydown', raccourci);
    }, [enregistrer, processing]);

    // Modifications non enregistrées : le navigateur demande confirmation avant de quitter.
    useEffect(() => {
        if (!isDirty) {
            return;
        }

        const avertir = (evenement: BeforeUnloadEvent) => evenement.preventDefault();
        window.addEventListener('beforeunload', avertir);

        return () => window.removeEventListener('beforeunload', avertir);
    }, [isDirty]);

    const changerTitre = (titre: string) => {
        setData((actuelles) => ({ ...actuelles, titre, slug: slugSaisi ? actuelles.slug : slugifier(titre) }));
    };

    const choisirCouverture = (fichier: File) => {
        if (fichier.size > TAILLE_MAX_COUVERTURE) {
            toast.error('La couverture dépasse 4 Mo.');

            return;
        }

        setData((actuelles) => ({ ...actuelles, couverture: fichier, supprimer_couverture: false }));
    };

    const titrePage = article.id ? "Modifier l'article" : 'Nouvel article';
    const statutAffiche = article.id ? (article.statut === 'brouillon' ? 'brouillon' : article.url ? 'publie' : 'programme') : null;

    return (
        <>
            <Head title={`Admin · ${titrePage}`} />

            <form
                onSubmit={(evenement) => {
                    evenement.preventDefault();
                    enregistrer();
                }}
                className="flex flex-1 flex-col gap-5 p-4 md:p-6"
            >
                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div className="flex min-w-0 flex-col gap-2">
                        <Link href={route('admin.articles')} className="inline-flex items-center gap-1 self-start text-sm font-bold text-muted-foreground hover:text-foreground">
                            <ArrowLeft className="size-4" />
                            Tous les articles
                        </Link>
                        <div className="flex flex-wrap items-center gap-3">
                            <h1 className="font-titre text-2xl leading-tight tracking-[-0.5px] md:text-[28px]">{titrePage}</h1>
                            {statutAffiche && <BadgeStatut statut={statutAffiche} />}
                            {isDirty && <span className="text-xs font-semibold text-muted-foreground">Modifications non enregistrées</span>}
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-2">
                        {article.url && (
                            <Button asChild variant="ghost">
                                <a href={article.url} target="_blank" rel="noreferrer">
                                    <ExternalLink />
                                    Voir en ligne
                                </a>
                            </Button>
                        )}
                        <Button type="button" variant="outline" onClick={() => setApercu(true)}>
                            <Eye />
                            Aperçu
                        </Button>
                        <Button type="submit" className="font-bold" disabled={processing}>
                            {data.statut === 'brouillon' ? <Save /> : programme ? <CalendarClock /> : <Send />}
                            {libelleEnregistrer}
                        </Button>
                    </div>
                </div>

                <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
                    {/* Rédaction */}
                    <div className="flex min-w-0 flex-col gap-4">
                        <div className="flex flex-col gap-1.5">
                            <Label htmlFor="titre" className="sr-only">
                                Titre
                            </Label>
                            <Textarea
                                id="titre"
                                rows={1}
                                value={data.titre}
                                onChange={(evenement) => changerTitre(evenement.target.value.replace(/\n/g, ' '))}
                                placeholder="Titre de l'article"
                                aria-invalid={Boolean(errors.titre)}
                                className="field-sizing-content min-h-0 resize-none border-0 bg-transparent px-0 font-titre text-[26px] leading-[1.15] tracking-[-0.5px] shadow-none placeholder:text-muted-foreground/50 focus-visible:ring-0 md:text-[34px]"
                            />
                            <InputError message={errors.titre} />
                        </div>

                        <div className="flex flex-col gap-1.5">
                            <EditeurArticle valeur={data.contenu} onChange={(html) => setData('contenu', html)} onMots={setMots} invalide={Boolean(errors.contenu)} />
                            <div className="flex items-center justify-between gap-3">
                                <InputError message={errors.contenu} />
                                <p className="ml-auto text-xs text-muted-foreground tabular-nums">
                                    {mots} mots · {tempsLecture} min de lecture
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Colonne latérale */}
                    <div className="flex flex-col gap-4 lg:sticky lg:top-4">
                        <Card className="gap-4 py-5">
                            <CardHeader className="px-5">
                                <CardTitle>Publication</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4 px-5">
                                <Champ label="Statut" erreur={errors.statut}>
                                    <div role="radiogroup" aria-label="Statut" className="grid grid-cols-2 gap-1 rounded-lg bg-muted p-1">
                                        {(
                                            [
                                                ['brouillon', 'Brouillon', Save],
                                                ['publie', 'Publié', Globe],
                                            ] as const
                                        ).map(([valeur, libelle, Icone]) => (
                                            <button
                                                key={valeur}
                                                type="button"
                                                role="radio"
                                                aria-checked={data.statut === valeur}
                                                onClick={() => setData('statut', valeur)}
                                                className={cn(
                                                    'flex h-9 items-center justify-center gap-1.5 rounded-md text-sm font-semibold transition-colors',
                                                    data.statut === valeur ? 'bg-card text-foreground shadow-xs' : 'text-muted-foreground hover:text-foreground',
                                                )}
                                            >
                                                <Icone className="size-4" />
                                                {libelle}
                                            </button>
                                        ))}
                                    </div>
                                </Champ>
                                <Champ
                                    label="Date de publication"
                                    htmlFor="publie_le"
                                    erreur={errors.publie_le}
                                    aide={
                                        data.statut === 'brouillon'
                                            ? 'Un brouillon reste invisible sur le site.'
                                            : programme
                                              ? "L'article apparaîtra sur le site à cette date."
                                              : data.publie_le
                                                ? 'Date affichée sur l’article.'
                                                : 'Vide : publié dès l’enregistrement.'
                                    }
                                >
                                    <Input
                                        id="publie_le"
                                        type="datetime-local"
                                        value={data.publie_le}
                                        onChange={(evenement) => setData('publie_le', evenement.target.value)}
                                        aria-invalid={Boolean(errors.publie_le)}
                                    />
                                </Champ>
                                <Champ label="Catégorie" htmlFor="categorie" erreur={errors.categorie}>
                                    <Select value={data.categorie} onValueChange={(valeur) => setData('categorie', valeur)}>
                                        <SelectTrigger id="categorie" className="w-full" aria-invalid={Boolean(errors.categorie)}>
                                            <SelectValue placeholder="Choisir une catégorie" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((categorie) => (
                                                <SelectItem key={categorie} value={categorie}>
                                                    {categorie}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Champ>
                            </CardContent>
                        </Card>

                        <Card className="gap-4 py-5">
                            <CardHeader className="px-5">
                                <CardTitle>Couverture</CardTitle>
                                <CardDescription>Sans image, une illustration aux couleurs de la catégorie est utilisée.</CardDescription>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-1.5 px-5">
                                <ChampCouverture
                                    apercu={imageCouverture}
                                    onChoisir={choisirCouverture}
                                    onRetirer={() => setData((actuelles) => ({ ...actuelles, couverture: null, supprimer_couverture: true }))}
                                    invalide={Boolean(errors.couverture)}
                                />
                                <InputError message={errors.couverture} />
                            </CardContent>
                        </Card>

                        <Card className="gap-4 py-5">
                            <CardHeader className="px-5">
                                <CardTitle>Présentation et référencement</CardTitle>
                            </CardHeader>
                            <CardContent className="flex flex-col gap-4 px-5">
                                <Champ label="Slug (adresse)" htmlFor="slug" erreur={errors.slug}>
                                    <div className="flex items-center gap-1">
                                        <div className="flex min-w-0 flex-1 items-center rounded-md border border-input bg-card shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50">
                                            <span className="shrink-0 pl-3 text-sm text-muted-foreground">/articles/</span>
                                            <input
                                                id="slug"
                                                value={data.slug}
                                                onChange={(evenement) => {
                                                    setSlugSaisi(true);
                                                    setData('slug', evenement.target.value);
                                                }}
                                                onBlur={() => setData('slug', slugifier(data.slug))}
                                                aria-invalid={Boolean(errors.slug)}
                                                className="h-9 min-w-0 flex-1 bg-transparent pr-3 text-sm outline-none"
                                            />
                                        </div>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            className="shrink-0 text-muted-foreground"
                                            aria-label="Générer le slug depuis le titre"
                                            title="Générer depuis le titre"
                                            onClick={() => {
                                                setSlugSaisi(false);
                                                setData('slug', slugifier(data.titre));
                                            }}
                                        >
                                            <RefreshCw />
                                        </Button>
                                    </div>
                                </Champ>
                                <Champ label="Extrait" htmlFor="extrait" erreur={errors.extrait} compteur={[data.extrait.length, LIMITES.extrait]}>
                                    <Textarea
                                        id="extrait"
                                        rows={3}
                                        value={data.extrait}
                                        onChange={(evenement) => setData('extrait', evenement.target.value)}
                                        placeholder="Deux phrases qui donnent envie de lire, affichées sur les cartes."
                                        aria-invalid={Boolean(errors.extrait)}
                                    />
                                </Champ>
                                <Champ label="Titre SEO" htmlFor="meta_titre" erreur={errors.meta_titre} compteur={[data.meta_titre.length, LIMITES.meta_titre]}>
                                    <Input
                                        id="meta_titre"
                                        value={data.meta_titre}
                                        onChange={(evenement) => setData('meta_titre', evenement.target.value)}
                                        placeholder={data.titre || 'Titre de l’article par défaut'}
                                    />
                                </Champ>
                                <Champ
                                    label="Description SEO"
                                    htmlFor="meta_description"
                                    erreur={errors.meta_description}
                                    compteur={[data.meta_description.length, LIMITES.meta_description]}
                                >
                                    <Textarea
                                        id="meta_description"
                                        rows={3}
                                        value={data.meta_description}
                                        onChange={(evenement) => setData('meta_description', evenement.target.value)}
                                        placeholder={data.extrait || 'Extrait par défaut'}
                                    />
                                </Champ>

                                {/* Aperçu du résultat dans un moteur de recherche. */}
                                <div className="flex flex-col gap-0.5 rounded-lg bg-muted/60 p-3" aria-label="Aperçu dans les résultats de recherche">
                                    <span className="truncate text-xs text-muted-foreground">
                                        {window.location.host}/articles/{data.slug || '…'}
                                    </span>
                                    <span className="line-clamp-1 text-[15px] font-semibold text-[#1a0dab]">
                                        {(data.meta_titre || data.titre || 'Titre de l’article') + ' · Edumica'}
                                    </span>
                                    <span className="line-clamp-2 text-xs text-muted-foreground">{data.meta_description || data.extrait || 'Description de l’article.'}</span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </form>

            <ApercuArticle
                ouvert={apercu}
                onFermer={() => setApercu(false)}
                titre={data.titre}
                extrait={data.extrait}
                contenu={data.contenu}
                categorie={data.categorie}
                couverture={imageCouverture}
                tempsLecture={tempsLecture}
                publieLe={data.publie_le}
                auteur={auth.user.name}
            />
        </>
    );
}

FormulaireArticle.layout = (props: Props) => ({
    breadcrumbs: [
        { title: 'Articles', href: route('admin.articles') },
        {
            title: props.article.id ? "Modifier l'article" : 'Nouvel article',
            href: props.article.id ? route('admin.articles.modifier', props.article.id) : route('admin.articles.creer'),
        },
    ],
});

function Champ({
    label,
    aide,
    htmlFor,
    erreur,
    compteur,
    children,
}: {
    label: string;
    aide?: string;
    htmlFor?: string;
    erreur?: string;
    /** [longueur, limite] */
    compteur?: [number, number];
    children: ReactNode;
}) {
    return (
        <div className="flex flex-col gap-1.5">
            <div className="flex items-baseline justify-between gap-2">
                <Label htmlFor={htmlFor}>{label}</Label>
                {compteur && (
                    <span className={cn('text-xs tabular-nums', compteur[0] > compteur[1] ? 'font-semibold text-destructive' : 'text-muted-foreground')}>
                        {compteur[0]}/{compteur[1]}
                    </span>
                )}
            </div>
            {children}
            {aide && <p className="text-xs text-muted-foreground">{aide}</p>}
            <InputError message={erreur} />
        </div>
    );
}
