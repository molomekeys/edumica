import { Head, usePage } from '@inertiajs/react';
import { Award, ClipboardCheck, Target } from 'lucide-react';
import { Arcs } from '@/components/arcs';
import { CatalogueTestsBlancs } from '@/components/catalogue-tests-blancs';
import { StatCard } from '@/components/stat-card';
import { dateFr, libelleNclc } from '@/lib/format';
import { t, tc } from '@/lib/i18n';
import { route } from '@/lib/routes';
import type { EpreuveCatalogue, TestComplet } from '@/types';

type Props = {
    statistiques: {
        tests: number;
        scoreMoyen: number | null;
        dernierTest: { niveau: string | null; le: string } | null;
    };
    complet: TestComplet;
    epreuves: EpreuveCatalogue[];
};

export default function Espace({ statistiques, complet, epreuves }: Props) {
    const { auth } = usePage().props;
    const { tests, scoreMoyen, dernierTest } = statistiques;

    return (
        <>
            <Head title={t('Tableau de bord')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <section className="relative isolate flex flex-col gap-2 overflow-hidden rounded-xl border bg-card px-6 py-7 shadow-xs">
                    <Arcs couleur="#FFB59E" className="-end-[120px] -top-[120px] size-[300px]" />
                    <p className="text-sm font-bold text-muted-foreground">{t('Bonjour :prenom', { prenom: auth.user.name.split(' ')[0] })} 👋</p>
                    <h1 className="font-titre text-[26px] leading-tight tracking-[-0.5px] md:text-[32px]">{t('Prêt pour un test blanc ?')}</h1>
                    <p className="max-w-xl text-[15px] text-muted-foreground">
                        {t("Conditions réelles : chrono de l'examen, une seule écoute par audio, correction à la fin.")}
                    </p>
                </section>

                <section aria-label={t('Statistiques')} className="grid gap-3 sm:grid-cols-3">
                    <StatCard
                        icone={ClipboardCheck}
                        titre={t('Tests passés')}
                        valeur={String(tests)}
                        detail={tests ? tc('test blanc terminé|tests blancs terminés', tests) : t('Aucun test terminé pour le moment')}
                    />
                    <StatCard
                        icone={Target}
                        titre={t('Score moyen')}
                        valeur={scoreMoyen === null ? '—' : `${scoreMoyen} %`}
                        detail={t('de bonnes réponses')}
                    />
                    <StatCard
                        icone={Award}
                        titre={t('Dernier NCLC')}
                        valeur={dernierTest ? libelleNclc(dernierTest.niveau, true) : '—'}
                        detail={dernierTest ? t('estimé le :date', { date: dateFr(dernierTest.le) }) : t('Passe un test pour l’estimer')}
                    />
                </section>

                <section className="flex flex-col gap-3">
                    <h2 className="font-titre text-xl tracking-[-0.5px]">{t('Tests blancs')}</h2>
                    <CatalogueTestsBlancs complet={complet} epreuves={epreuves} />
                </section>
            </div>
        </>
    );
}

Espace.layout = {
    breadcrumbs: [{ title: 'Tableau de bord', href: route('espace') }],
};
