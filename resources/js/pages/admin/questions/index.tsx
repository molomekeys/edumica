import { Head, Link, router } from '@inertiajs/react';
import { createColumnHelper, tableFeatures, useTable } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Headphones, MoreHorizontal, Pencil, Plus, Search, Trash2, Type, Volume2 } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import type { ReactNode } from 'react';
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
import { route } from '@/lib/routes';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

type QuestionLigne = {
    id: number;
    enonce: string;
    categorie: string;
    ordre: number;
    epreuve: { slug: string; nom: string };
    choix: number;
    bonne_reponse: string | null;
    support: 'audio' | 'voix' | 'texte' | null;
};

type Filtres = { epreuve: string; q: string };

type Props = {
    questions: Paginated<QuestionLigne>;
    epreuves: { id: number; slug: string; nom: string; questions_count: number }[];
    filtres: Filtres;
};

const TOUTES = 'toutes';

const features = tableFeatures({});
const colonne = createColumnHelper<typeof features, QuestionLigne>();

/** Colonnes masquées sur petit écran. */
const classesColonnes: Record<string, string> = {
    epreuve: 'hidden sm:table-cell',
    categorie: 'hidden lg:table-cell',
    support: 'hidden md:table-cell',
    actions: 'w-12 text-right',
};

const supports = {
    audio: { libelle: 'Fichier audio', icone: Headphones },
    voix: { libelle: 'Voix de synthèse', icone: Volume2 },
    texte: { libelle: 'Document', icone: Type },
};

export default function Questions({ questions, epreuves, filtres }: Props) {
    const [recherche, setRecherche] = useState(filtres.q);
    const [aSupprimer, setASupprimer] = useState<QuestionLigne | null>(null);
    const [suppression, setSuppression] = useState(false);

    const filtrer = (changements: Partial<Filtres>) => {
        router.get(
            route('admin.questions', { ...filtres, ...changements }),
            {},
            { preserveState: true, preserveScroll: true, replace: true, only: ['questions', 'filtres'] },
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
                colonne.accessor('enonce', {
                    header: 'Question',
                    cell: ({ row }) => (
                        <div className="flex min-w-0 flex-col gap-0.5 whitespace-normal">
                            <Link href={route('admin.questions.modifier', row.original.id)} className="line-clamp-2 font-semibold hover:underline">
                                {row.original.enonce}
                            </Link>
                            <span className="text-xs text-muted-foreground">
                                <span className="sm:hidden">{row.original.epreuve.nom} · </span>
                                {row.original.choix} choix · réponse : {row.original.bonne_reponse ?? '?'}
                            </span>
                        </div>
                    ),
                }),
                colonne.accessor('epreuve.nom', {
                    id: 'epreuve',
                    header: 'Épreuve',
                    cell: (info) => <Badge variant="secondary">{info.getValue()}</Badge>,
                }),
                colonne.accessor('categorie', {
                    header: 'Catégorie',
                    cell: (info) => <Badge variant="outline">{info.getValue()}</Badge>,
                }),
                colonne.accessor('support', {
                    header: 'Support',
                    cell: (info) => {
                        const support = info.getValue();

                        if (!support) {
                            return <span className="text-muted-foreground">—</span>;
                        }

                        const { libelle, icone: Icone } = supports[support];

                        return (
                            <Badge variant="outline" className="gap-1 text-muted-foreground">
                                <Icone aria-hidden="true" />
                                {libelle}
                            </Badge>
                        );
                    },
                }),
                colonne.display({
                    id: 'actions',
                    header: () => <span className="sr-only">Actions</span>,
                    cell: ({ row }) => (
                        <DropdownMenu modal={false}>
                            <DropdownMenuTrigger asChild>
                                <Button variant="ghost" size="icon" className="size-8" aria-label={`Actions pour « ${row.original.enonce} »`}>
                                    <MoreHorizontal />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-44">
                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                <DropdownMenuItem asChild>
                                    <Link href={route('admin.questions.modifier', row.original.id)} className="cursor-pointer">
                                        <Pencil />
                                        Modifier
                                    </Link>
                                </DropdownMenuItem>
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
        data: questions.data,
        getRowId: (question) => String(question.id),
    });

    const supprimer = () => {
        if (!aSupprimer) {
            return;
        }

        router.delete(route('admin.questions.supprimer', aSupprimer.id), {
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
            <Head title="Admin · Questions" />

            <div className="flex flex-1 flex-col gap-5 p-4 md:p-6">
                <Heading surtitre="Panel admin" titre="Questions" description="Banque de questions des quiz et des tests blancs.">
                    <Button asChild className="font-bold">
                        <Link href={route('admin.questions.creer', { epreuve: filtres.epreuve })}>
                            <Plus />
                            Nouvelle question
                        </Link>
                    </Button>
                </Heading>

                <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div className="relative flex-1 sm:max-w-sm">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
                        <Input
                            type="search"
                            value={recherche}
                            onChange={(event) => setRecherche(event.target.value)}
                            placeholder="Rechercher une question ou une catégorie…"
                            aria-label="Rechercher"
                            className="h-10 bg-card pl-9"
                        />
                    </div>
                    <Select value={filtres.epreuve || TOUTES} onValueChange={(valeur) => filtrer({ epreuve: valeur === TOUTES ? '' : valeur })}>
                        <SelectTrigger className="h-10! w-full bg-card sm:w-60" aria-label="Filtrer par épreuve">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={TOUTES}>Toutes les épreuves</SelectItem>
                            {epreuves.map((epreuve) => (
                                <SelectItem key={epreuve.id} value={epreuve.slug}>
                                    {epreuve.nom} <span className="text-muted-foreground tabular-nums">({epreuve.questions_count})</span>
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
                                        Aucune question trouvée.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </div>

                <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground">
                    <p className="tabular-nums">
                        {questions.total ? `${questions.from}–${questions.to} sur ${questions.total} questions` : '0 question'}
                    </p>
                    <div className="flex items-center gap-2">
                        <span className="tabular-nums">
                            Page {questions.current_page} sur {questions.last_page}
                        </span>
                        <LienPage href={questions.prev_page_url} libelle="Page précédente">
                            <ChevronLeft />
                        </LienPage>
                        <LienPage href={questions.next_page_url} libelle="Page suivante">
                            <ChevronRight />
                        </LienPage>
                    </div>
                </div>
            </div>

            <Dialog open={aSupprimer !== null} onOpenChange={(ouvert) => !ouvert && setASupprimer(null)}>
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>Supprimer cette question&nbsp;?</DialogTitle>
                        <DialogDescription>
                            « {aSupprimer?.enonce} » sera supprimée des quiz et des tests blancs. Cette action est définitive.
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

Questions.layout = {
    breadcrumbs: [{ title: 'Questions', href: route('admin.questions') }],
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
            <Link href={href} preserveScroll only={['questions']} aria-label={libelle}>
                {children}
            </Link>
        </Button>
    );
}
