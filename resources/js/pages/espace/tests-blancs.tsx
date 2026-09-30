import { Head } from '@inertiajs/react';
import { Clock, Headphones, ListChecks } from 'lucide-react';
import { CatalogueTestsBlancs } from '@/components/catalogue-tests-blancs';
import Heading from '@/components/heading';
import { t } from '@/lib/i18n';
import { route } from '@/lib/routes';
import type { EpreuveCatalogue, TestComplet } from '@/types';

// Textes français (clés de traduction), traduits au rendu.
const regles = [
    { icone: Clock, titre: 'Le chrono de l’examen', texte: 'À la fin du temps, le test se termine et tes réponses sont figées.' },
    { icone: Headphones, titre: 'Une seule écoute', texte: 'Chaque audio ne se lance qu’une fois, même si tu reviens sur la question.' },
    { icone: ListChecks, titre: 'Correction à la fin', texte: 'Score, niveau NCLC estimé et explication de chaque question.' },
];

export default function TestsBlancs({ complet, epreuves }: { complet: TestComplet; epreuves: EpreuveCatalogue[] }) {
    return (
        <>
            <Head title={t('Tests blancs')} />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading titre={t('Tests blancs')} description={t('Entraîne-toi épreuve par épreuve, ou enchaîne-les toutes avec un seul chrono.')} />

                <ul className="grid gap-3 sm:grid-cols-3">
                    {regles.map(({ icone: Icone, titre, texte }) => (
                        <li key={titre} className="flex gap-3 rounded-xl bg-menthe/60 p-4">
                            <Icone className="mt-0.5 size-5 shrink-0 text-vert" aria-hidden="true" />
                            <div className="flex flex-col gap-0.5">
                                <span className="text-sm font-bold">{t(titre)}</span>
                                <span className="text-[13px] leading-snug text-muted-foreground">{t(texte)}</span>
                            </div>
                        </li>
                    ))}
                </ul>

                <CatalogueTestsBlancs complet={complet} epreuves={epreuves} />
            </div>
        </>
    );
}

TestsBlancs.layout = {
    breadcrumbs: [{ title: 'Tests blancs', href: route('espace.tests-blancs') }],
};
