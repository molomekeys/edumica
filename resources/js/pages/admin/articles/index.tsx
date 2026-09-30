import { Head, Link, router } from '@inertiajs/react';
import { createColumnHelper, tableFeatures, useTable } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Clock, ExternalLink, MoreHorizontal, Newspaper, Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { ReactNode } from 'react';
import { BadgeStatut } from '@/components/article/badge-statut';
import type { StatutArticle } from '@/components/article/badge-statut';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { dateFr } from '@/lib/format';
import { route } from '@/lib/routes';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

type ArticleLigne = {
    id: number;
    titre: string;
    slug: string;
    categorie: string;
    statut: StatutArticle;
    publie_le: string | null;
    couverture: string | null;
    temps_lecture: number;
};

type Filtres = { q: string; categorie: string; statut: string };

type Props = {
    articles: Paginated<ArticleLigne>;
    categories: string[];
    filtres: Filtres;
};

const TOUS = 'tous';

const statuts: { valeur: StatutArticle; libelle: string }[] = [
    { valeur: 'publie', libelle: 'Publiés' },
    { valeur: 'programme', libelle: 'Programmés' },
    { valeur: 'brouillon', libelle: 'Brouillons' },
];

const features = tableFeatures({});
const colonne = createColumnHelper<typeof features, ArticleLigne>();

/** Colonnes masquées sur petit écran. */
const classesColonnes: Record<string, string> = {
    categorie: 'hidden lg:table-cell',
    statut: 'hidden sm:table-cell',
    publie_le: 'hidden md:table-cell',
    actions: 'w-12 text-right',
};

export default function Articles({ articles, categories, filtres }: Props) {
    const [recherche, setRecherche] = useState(filtres.q);
    const [aSupprimer, setASupprimer] = useState<ArticleLigne | null>(null);
    const [suppression, setSuppression] = useState(false);

    const filtrer = (changements: Partial<Filtres>) => {
        router.get(
            route('admin.articles', { ...filtres, ...changements }),
            {},
            { preserveState: true, preserveScroll: true, replace: true, only: ['articles', 'filtres'] },
        );
    };

    // Recherche au fil de la frappe, sans surcharger le serveur.
    useEffect(() => {
        if (recherche.trim() === filtres.q) {
            return;
        }

        const minuteur = window.setTimeout(() => filtrer({ q: recherche.trim() }), 300);

        return () => window.clearTimeout(minuteur);
    }, [recherche, filtres]);

    const colonnes = useMemo(
        () =>
            colonne.columns([
                colonne.accessor('titre', {
                    header: 'Article',
                    cell: ({ row }) => (
                        <div className="flex min-w-0 items-center gap-3 whitespace-normal">
                            <div className="hidden size-12 shrink-0 overflow-hidden rounded-lg bg-menthe sm:block">
                                {row.original.couverture ? (
                                    <img src={row.original.couverture} alt="" className="size-full object-cover" />
                                ) : (
                                    <div className="flex size-full items-center justify-center text-vert">
                                        <Newspaper className="size-5" aria-hidden="true" />
                                    </div>
                                )}
                            </div>
                            <div className="flex min-w-0 flex-col gap-0.5">
                                <Link href={route('admin.articles.modifier', row.original.id)} className="line-clamp-2 font-semibold hover:underline">
                                    {row.original.titre}
                                </Link>
                                <span className="flex flex-wrap items-center gap-x-2 text-xs text-muted-foreground">
                                    <span className="truncate">/articles/{row.original.slug}</span>
                                    <span className="inline-flex items-center gap-1">
                                        <Clock className="size-3" aria-hidden="true" />
                                        {row.original.temps_lecture} min
                                    </span>
                                    <span className="sm:hidden">
                                        <BadgeStatut statut={row.original.statut} />
                                    </span>
                                </span>
                            </div>
                        </div>
                    ),
                }),
                colonne.accessor('categorie', {
                    header: 'Catégorie',
                    cell: (info) => <Badge variant="secondary">{info.getValue()}</Badge>,
                }),
                colonne.accessor('statut', {
                    header: 'Statut',
                    cell: (info) => <BadgeStatut statut={info.getValue()} />,
                }),
                colonne.accessor('publie_le', {
                    header: 'Publication',
                    cell: (info) => {
                        const date = info.getValue();

                        return date ? <span className="tabular-nums">{dateFr(date)}</span> : <span className="text-muted-foreground">—</span>;
                    },
                }),
                colonne.display({
                    id: 'actions',
                    header: () => <span className="sr-only">Actions</span>,
                    cell: ({ row }) => (
                        <DropdownMenu modal={false}>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" className="size-8" aria-label={`Actions pour « ${row.original.titre} »`}>
                                    <MoreHorizontal />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-48">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuItem asChild>
                                    <Link href={route('admin.articles.modifier', row.original.id)} className="cursor-pointer">
                                        <Pencil />
                                        Modifier
                                    </Link>
                                </DropdownMenuItem>
                                {row.original.statut === 'publie' && (
                                    <DropdownMenuItem asChild>
                                        <a href={route('article', row.original.slug)} target="_blank" rel="noreferrer" className="cursor-pointer">
                                            <ExternalLink />
                                            Voir sur le site
                                        </a>
                                    </DropdownMenuItem>
                                )}
                                <DropdownMenuSeparator />
                                <DropdownMenuItem variant="destructive" className="cursor-pointer" onSelect={() => setASupprimer(row.original)}>
                                    <Trash2 />
                                    Supprimer
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    ),
                }),
            ]),
        [],
    );

    const table = useTable({
        features,
        columns: colonnes,
        data: articles.data,
        getRowId: (article) => String(article.id),
    });

    const supprimer = () => {
        if (!aSupprimer) {
            return;
        }

        router.delete(route('admin.articles.supprimer', aSupprimer.id), {
            preserveState: true,
            preserveScroll: true,
            onStart: () => setSuppression(true),
            onFinish: () => {
                setSuppression(false);
                setASupprimer(null);
            },
        });
    };

    return (
        <>
            <Head title="Admin · Articles" />

            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                <Heading surtitre="Panel admin" titre="Articles" description="Les articles du blog, publiés, programmés ou en brouillon.">
                    <Button asChild className="font-bold">
                        <Link href={route('admin.articles.creer')}>
                            <Plus />
                            Nouvel article
                        </Link>
                    </Button>
                </Heading>

                <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <div className="relative flex-1 sm:max-w-sm">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                        <Input
                            type="search"
                            value={recherche}
                            onChange={(event) => setRecherche(event.target.value)}
                            placeholder="Rechercher un titre ou un extrait…"
                            aria-label="Rechercher"
                            className="h-10 bg-card pl-9"
                        />
                    </div>
                    <Select value={filtres.categorie || TOUS} onValueChange={(valeur) => filtrer({ categorie: valeur === TOUS ? '' : valeur })}>
                        <SelectTrigger className="h-10! w-full bg-card sm:w-56" aria-label="Filtrer par catégorie">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TOUS}>Toutes les catégories</SelectItem>
                            {categories.map((categorie) => (
                                <SelectItem key={categorie} value={categorie}>
                                    {categorie}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={filtres.statut || TOUS} onValueChange={(valeur) => filtrer({ statut: valeur === TOUS ? '' : valeur })}>
                        <SelectTrigger className="h-10! w-full bg-card sm:w-44" aria-label="Filtrer par statut">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TOUS}>Tous les statuts</SelectItem>
                            {statuts.map(({ valeur, libelle }) => (
                                <SelectItem key={valeur} value={valeur}>
                                    {libelle}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

                <div className="overflow-hidden rounded-xl border bg-card shadow-xs">
                    <Table>
                        <TableHeader className="bg-muted/60">
                            {table.getHeaderGroups().map((groupe) => (
                                <TableRow key={groupe.id} className="hover:bg-transparent">
                                    {groupe.headers.map((entete) => (
                                        <TableHead key={entete.id} className={cn('px-4 font-bold', classesColonnes[entete.column.id])}>
                                            {entete.isPlaceholder ? null : <table.FlexRender header={entete} />}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {table.getRowModel().rows.length ? (
                                table.getRowModel().rows.map((ligne) => (
                                    <TableRow key={ligne.id}>
                                        {ligne.getAllCells().map((cellule) => (
                                            <TableCell key={cellule.id} className={cn('px-4 py-3', classesColonnes[cellule.column.id])}>
                                                <table.FlexRender cell={cellule} />
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={colonnes.length} className="h-28 text-center text-muted-foreground">
                                        Aucun article trouvé.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground">
                    <p className="tabular-nums">{articles.total ? `${articles.from}–${articles.to} sur ${articles.total} articles` : '0 article'}</p>
                    <div className="flex items-center gap-2">
                        <span className="tabular-nums">
                            Page {articles.current_page} sur {articles.last_page}
                        </span>
                        <LienPage href={articles.prev_page_url} libelle="Page précédente">
                            <ChevronLeft />
                        </LienPage>
                        <LienPage href={articles.next_page_url} libelle="Page suivante">
                            <ChevronRight />
                        </LienPage>
                    </div>
                </div>
            </div>

            <Dialog open={aSupprimer !== null} onOpenChange={(ouvert) => !ouvert && setASupprimer(null)}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Supprimer cet article&nbsp;?</DialogTitle>
                        <DialogDescription>
                            « {aSupprimer?.titre} » et sa couverture seront supprimés du site. Cette action est définitive.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setASupprimer(null)}>
                            Annuler
                        </Button>
                        <Button variant="destructive" disabled={suppression} onClick={supprimer}>
                            <Trash2 />
                            Supprimer
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

Articles.layout = {
    breadcrumbs: [{ title: 'Articles', href: route('admin.articles') }],
};

/** Page précédente ou suivante : rechargement partiel de la liste seulement. */
function LienPage({ href, libelle, children }: { href: string | null; libelle: string; children: ReactNode }) {
    if (!href) {
        return (
            <Button variant="outline" size="icon" className="size-8" disabled aria-label={libelle}>
                {children}
            </Button>
        );
    }

    return (
        <Button variant="outline" size="icon" className="size-8" asChild>
            <Link href={href} preserveScroll only={['articles']} aria-label={libelle}>
                {children}
            </Link>
        </Button>
    );
}
