import { Head, Link } from '@inertiajs/react';
import { Check, RotateCcw, X } from 'lucide-react';
import { Arcs } from '@/components/arcs';
import { IconeEpreuve } from '@/components/icone-epreuve';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { libelleNclc } from '@/lib/format';
import { t, tc } from '@/lib/i18n';
import { route, routeTestBlanc } from '@/lib/routes';
import { cn } from '@/lib/utils';

type ResultatEpreuve = {
    epreuve_id: number;
    nom: string;
    icone: string;
    bonnes: number;
    total: number;
    score: number | null;
    maximum: number | null;
    niveau: string | null;
    categories: { nom: string; bonnes: number; total: number }[];
};

type Correction = {
    index: number;
    epreuve_id: number;
    categorie: string;
    enonce: string;
    choix: string[];
    bonne_reponse: number;
    reponse: number | null;
    explication: string;
};

type Props = {
    epreuve: { nom: string; slug: string } | null;
    tentative: { id: number; bonnes: number; total: number; niveau: string | null; terminee_le: string };
    epreuves: ResultatEpreuve[];
    corrections: Correction[];
};

export default function ResultatTestBlanc({ epreuve, tentative, epreuves, corrections }: Props) {
    const complet = epreuve === null;
    const seule = !complet ? epreuves[0] : undefined;

    return (
        <>
            <Head title={t('Résultat · :test', { test: complet ? t('Test blanc complet') : epreuve.nom })} />

            <div className="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-8 p-4 md:p-6">
                <section className="relative isolate flex flex-col gap-4 overflow-hidden rounded-[24px] bg-foret px-6 py-8 text-white md:px-10 md:py-10">
                    <Arcs couleur="#FFB59E" className="-end-[110px] -top-[110px] size-[320px]" />
                    <div className="text-xs font-bold tracking-[0.14em] text-peche uppercase">{t('Test blanc terminé')}</div>
                    <h1 className="font-titre text-[26px] leading-tight tracking-[-0.5px] md:text-[32px]">{complet ? t('Toutes les épreuves') : epreuve.nom}</h1>
                    <div className="flex flex-wrap items-end gap-x-8 gap-y-3">
                        <div className="flex flex-col gap-1">
                            <div className="font-titre text-[60px] leading-none tracking-[-1.5px] md:text-[72px]" dir="ltr">
                                {tentative.bonnes}
                                <span className="text-[26px] text-white/50 md:text-[32px]"> / {tentative.total}</span>
                            </div>
                            <div className="text-sm font-semibold text-white/70">{tc('bonne réponse|bonnes réponses', tentative.bonnes)}</div>
                        </div>
                        <div className="flex flex-col gap-1 pb-1">
                            <span className="self-start rounded-full bg-peche px-3.5 py-1 text-sm font-extrabold text-foret">{libelleNclc(tentative.niveau)}</span>
                            <span className="text-sm font-semibold text-white/70">
                                {seule?.score != null
                                    ? t('Score estimé : :score / :maximum', { score: seule.score, maximum: seule.maximum ?? '' })
                                    : t('Niveau global : le plus faible des épreuves')}
                            </span>
                        </div>
                    </div>
                    <p className="text-[13px] text-white/55">{t('Estimation indicative calculée sur des questions d’entraînement.')}</p>
                </section>

                <section className="flex flex-col gap-3">
                    <h2 className="font-titre text-xl tracking-[-0.5px]">{complet ? t('Par épreuve') : t('Par catégorie')}</h2>
                    <div className={cn('grid gap-3', complet && 'md:grid-cols-2')}>
                        {epreuves.map((stats) => (
                            <Card key={stats.epreuve_id} className="gap-4 p-5">
                                <div className="flex items-center gap-3">
                                    <span className="flex size-11 shrink-0 items-center justify-center rounded-[14px] bg-foret text-peche">
                                        <IconeEpreuve nom={stats.icone} className="size-5" />
                                    </span>
                                    <div className="flex min-w-0 flex-1 flex-col">
                                        <span className="truncate font-bold">{stats.nom}</span>
                                        <span className="text-sm text-muted-foreground tabular-nums">
                                            {t(':bonnes / :total bonnes réponses', { bonnes: stats.bonnes, total: stats.total })}
                                            {stats.score !== null && ` · ${stats.score} / ${stats.maximum}`}
                                        </span>
                                    </div>
                                    {stats.score !== null && (
                                        <Badge className="shrink-0 bg-peche-clair text-[13px] font-extrabold text-foret">{libelleNclc(stats.niveau, true)}</Badge>
                                    )}
                                </div>
                                <div className={cn('grid gap-3', !complet && 'sm:grid-cols-2')}>
                                    {stats.categories.map((categorie) => (
                                        <div key={categorie.nom} className="flex flex-col gap-1.5">
                                            <div className="flex justify-between gap-2 text-sm font-semibold">
                                                <span>{categorie.nom}</span>
                                                <span className="text-muted-foreground tabular-nums" dir="ltr">
                                                    {categorie.bonnes} / {categorie.total}
                                                </span>
                                            </div>
                                            <div className="h-2 rounded-full bg-muted">
                                                <div className="h-2 rounded-full bg-vert" style={{ width: `${(categorie.bonnes / categorie.total) * 100}%` }} />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </Card>
                        ))}
                    </div>
                </section>

                <section className="flex flex-col gap-3">
                    <h2 className="font-titre text-xl tracking-[-0.5px]">{t('Correction')}</h2>
                    {epreuves.map((stats) => (
                        <div key={stats.epreuve_id} className="flex flex-col gap-2">
                            {complet && <h3 className="mt-2 text-[13px] font-bold tracking-[0.12em] text-muted-foreground uppercase">{stats.nom}</h3>}
                            <ol className="flex flex-col gap-2">
                                {corrections
                                    .filter((correction) => correction.epreuve_id === stats.epreuve_id)
                                    .map((correction) => (
                                        <LigneCorrection key={correction.index} correction={correction} numero={corrections.indexOf(correction) + 1} />
                                    ))}
                            </ol>
                        </div>
                    ))}
                </section>

                <div className="flex flex-col gap-2.5 sm:flex-row">
                    <Button asChild size="lg" className="h-14 flex-1 rounded-2xl text-[17px] font-bold">
                        <Link href={routeTestBlanc(epreuve?.slug)}>
                            <RotateCcw className="size-5" />
                            {t('Recommencer')}
                        </Link>
                    </Button>
                    <Button asChild size="lg" variant="outline" className="h-14 flex-1 rounded-2xl border-[1.5px] border-foret text-base font-bold">
                        <Link href={route('espace')}>{t('Retour à mon espace')}</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}

ResultatTestBlanc.layout = (props: Props) => ({
    breadcrumbs: [
        { title: 'Tests blancs', href: route('espace.tests-blancs') },
        { title: props.epreuve?.nom ?? 'Test complet', href: routeTestBlanc(props.epreuve?.slug) },
        { title: 'Résultat', href: route('test-blanc.resultat', props.tentative.id) },
    ],
});

function LigneCorrection({ correction, numero }: { correction: Correction; numero: number }) {
    const { reponse, bonne_reponse: bonne, choix } = correction;
    const correcte = reponse === bonne;

    return (
        <li className="flex items-start gap-3 rounded-2xl border bg-card px-4 py-3.5">
            {correcte ? (
                <span className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-foret text-white">
                    <Check className="size-4" strokeWidth={3} aria-label={t('Bonne réponse')} />
                </span>
            ) : reponse !== null ? (
                <span className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-peche text-foret">
                    <X className="size-4" strokeWidth={3} aria-label={t('Mauvaise réponse')} />
                </span>
            ) : (
                <span className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-muted text-[13px] font-bold text-muted-foreground" aria-label={t('Sans réponse')}>
                    –
                </span>
            )}
            <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                <span className="text-xs font-bold text-muted-foreground">
                    Q{numero} · {correction.categorie}
                </span>
                <span lang="fr" dir="ltr" className="text-start text-[15px] font-semibold">
                    {correction.enonce}
                </span>
                <span className="text-[13px] text-muted-foreground">
                    {reponse === null ? `${t('Sans réponse')} · ` : null}
                    {reponse !== null && !correcte && (
                        <>
                            {t('Ta réponse :')}{' '}
                            <span lang="fr" dir="ltr">
                                {choix[reponse] ?? '?'}
                            </span>{' '}
                            ·{' '}
                        </>
                    )}
                    {t('Bonne réponse :')}{' '}
                    <span lang="fr" dir="ltr" className="font-bold text-foreground">
                        {choix[bonne]}
                    </span>
                </span>
                <span lang="fr" dir="ltr" className="mt-1 text-start text-[13px] leading-normal">
                    {correction.explication}
                </span>
            </div>
        </li>
    );
}
