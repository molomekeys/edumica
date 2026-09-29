import { Head, Link, http, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Clock } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';
import AppLogoIcon from '@/components/app-logo-icon';
import { DialogueTerminer } from '@/components/test-blanc/dialogue-terminer';
import { LecteurAudio } from '@/components/test-blanc/lecteur-audio';
import { LETTRES } from '@/components/test-blanc/types';
import type { EpreuveTest, QuestionTest, Section } from '@/components/test-blanc/types';
import { formatChrono, useChrono } from '@/components/test-blanc/use-chrono';
import { VoletQuestions } from '@/components/test-blanc/volet-questions';
import { Button } from '@/components/ui/button';
import { SidebarInset, SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import { useFlashToast } from '@/hooks/use-flash-toast';
import { route } from '@/lib/routes';
import { cn } from '@/lib/utils';

type Tentative = {
    id: number;
    fin: number;
    maintenant: number;
    reponses: (number | null)[];
    marquees: number[];
    ecoutees: number[];
};

type Props = {
    epreuve: { nom: string; slug: string } | null;
    tentative: Tentative | null;
    epreuves: EpreuveTest[];
    questions: QuestionTest[];
};

/** Cookie du volet des questions, distinct de celui de la sidebar des dashboards. */
const COOKIE_VOLET = 'volet_test_blanc';

export default function PasserTestBlanc({ epreuve, tentative, epreuves, questions }: Props) {
    useFlashToast();

    if (!tentative || questions.length === 0) {
        return <TestVide complet={epreuve === null} />;
    }

    return <TestEnCours epreuve={epreuve} tentative={tentative} epreuves={epreuves} questions={questions} />;
}

function TestEnCours({ epreuve, tentative, epreuves, questions }: Props & { tentative: Tentative }) {
    const complet = epreuve === null;
    const total = questions.length;

    const [position, setPosition] = useState(0);
    const [reponses, setReponses] = useState(tentative.reponses);
    const [marquees, setMarquees] = useState(tentative.marquees);
    const [ecoutees, setEcoutees] = useState(tentative.ecoutees);
    const [confirmer, setConfirmer] = useState(false);
    const [envoi, setEnvoi] = useState(false);
    const termine = useRef(false);

    const etat = { reponses, marquees, ecoutees };
    const envoye = useRef(JSON.stringify(etat));

    const terminer = () => {
        if (termine.current) {
            return;
        }

        termine.current = true;
        setEnvoi(true);
        router.post(route('test-blanc.terminer', tentative.id), etat, {
            onError: () => toast.error('Le test n’a pas pu être terminé. Réessaie.'),
            onFinish: () => {
                termine.current = false;
                setEnvoi(false);
            },
        });
    };

    // À la fin du chrono, le test se termine avec les réponses données.
    const reste = useChrono(tentative.fin, tentative.maintenant, terminer);
    const verrouille = reste === 0 || envoi;

    // Sauvegarde au fil du test : le chrono, les réponses et les écoutes survivent à un rechargement.
    useEffect(() => {
        const json = JSON.stringify({ reponses, marquees, ecoutees });

        if (json === envoye.current) {
            return;
        }

        const minuteur = window.setTimeout(() => {
            envoye.current = json;
            http.getClient()
                .request({
                    method: 'put',
                    url: route('test-blanc.sauvegarder', tentative.id),
                    data: { reponses, marquees, ecoutees },
                    headers: { Accept: 'application/json' },
                })
                .catch(() => {
                    envoye.current = '';
                });
        }, 300);

        return () => window.clearTimeout(minuteur);
    }, [reponses, marquees, ecoutees, tentative.id]);

    // Avertit avant de recharger ou fermer la page en plein test.
    useEffect(() => {
        const avertir = (event: BeforeUnloadEvent) => event.preventDefault();
        window.addEventListener('beforeunload', avertir);

        return () => window.removeEventListener('beforeunload', avertir);
    }, []);

    const sections = useMemo(() => regrouper(questions, epreuves), [questions, epreuves]);
    const nomsEpreuves = useMemo(() => new Map(epreuves.map((e) => [e.id, e.nom])), [epreuves]);

    const question = questions[position];
    const choix = reponses[question.index] ?? null;
    const marquee = marquees.includes(question.index);
    const estRepondue = (index: number) => reponses[index] !== null && reponses[index] !== undefined;
    const repondues = questions.filter((q) => estRepondue(q.index)).length;

    const aller = (rang: number) => {
        if (questions[rang]) {
            setPosition(rang);
            window.scrollTo({ top: 0 });
        }
    };

    const choisir = (reponse: number | null) => {
        if (!verrouille) {
            setReponses((actuelles) => actuelles.map((valeur, index) => (index === question.index ? reponse : valeur)));
        }
    };

    const basculerMarque = () => {
        if (!verrouille) {
            setMarquees((actuelles) =>
                actuelles.includes(question.index) ? actuelles.filter((index) => index !== question.index) : [...actuelles, question.index],
            );
        }
    };

    const marquerEcoutee = (index: number) => setEcoutees((actuelles) => (actuelles.includes(index) ? actuelles : [...actuelles, index]));

    return (
        <SidebarProvider cookieName={COOKIE_VOLET} defaultOpen={!document.cookie.split('; ').includes(`${COOKIE_VOLET}=false`)} className="bg-papier">
            <Head title={complet ? 'Test blanc complet' : `Test blanc · ${epreuve.nom}`} />

            <VoletQuestions
                sections={sections}
                complet={complet}
                total={total}
                repondues={repondues}
                position={position}
                estRepondue={estRepondue}
                estMarquee={(index) => marquees.includes(index)}
                onAller={aller}
            />

            <SidebarInset className="min-w-0 bg-papier text-foret">
                {/* En-tête : pas de sortie directe, on passe par « Terminer » (qui propose d'abandonner). */}
                <header className="sticky top-0 z-30 bg-foret pt-[env(safe-area-inset-top)] text-white">
                    <div className="flex h-16 items-center gap-2 px-3 md:gap-3 md:px-5">
                        <SidebarTrigger
                            className="size-10 shrink-0 rounded-xl text-white/80 hover:bg-white/10 hover:text-white"
                            aria-label="Afficher ou masquer la liste des questions"
                        />
                        <div className="flex min-w-0 flex-1 flex-col">
                            <span className="truncate text-[11px] font-bold tracking-[0.14em] text-peche uppercase">
                                {complet ? 'Test blanc complet' : 'Test blanc'}
                            </span>
                            <span className="truncate text-[15px] leading-tight font-bold">{complet ? 'Toutes les épreuves' : epreuve.nom}</span>
                        </div>
                        <span className="hidden text-sm font-semibold text-white/70 tabular-nums md:inline">
                            {repondues} / {total} répondues
                        </span>
                        <div
                            role="timer"
                            aria-label="Temps restant"
                            className={cn(
                                'flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1.5 text-[15px] font-bold tabular-nums transition-colors',
                                reste <= 300 ? 'bg-peche text-foret' : 'bg-white/10',
                            )}
                        >
                            <Clock className="size-[18px]" aria-hidden="true" />
                            {formatChrono(reste)}
                        </div>
                        <Button
                            className="h-10 shrink-0 rounded-xl bg-peche px-3.5 font-bold text-foret hover:bg-white sm:px-4"
                            onClick={() => setConfirmer(true)}
                        >
                            Terminer
                        </Button>
                    </div>
                    <div className="h-1 bg-white/10" aria-hidden="true">
                        <div className="h-1 bg-peche transition-[width] duration-500" style={{ width: `${(repondues / total) * 100}%` }} />
                    </div>
                </header>

                {/* Question en cours */}
                <main key={question.id} className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-5 px-4 py-6 md:py-10">
                    <div className="flex flex-wrap items-center gap-2 text-[13px] font-bold">
                        <span className="rounded-full bg-foret px-3 py-1 text-white tabular-nums">
                            Question {position + 1} / {total}
                        </span>
                        {complet && <span className="rounded-full bg-peche-clair px-3 py-1">{nomsEpreuves.get(question.epreuve_id)}</span>}
                        <span className="text-cendre">{question.categorie}</span>
                    </div>

                    {(question.transcription || question.audio) && (
                        <LecteurAudio
                            source={question.audio}
                            transcription={question.transcription}
                            duree={question.duree_audio}
                            ecoutee={ecoutees.includes(question.index)}
                            onDebut={() => marquerEcoutee(question.index)}
                        />
                    )}

                    {question.support && (
                        <div className="rounded-[20px] border-[1.5px] border-trait bg-white p-5 text-[15px] leading-[1.65] whitespace-pre-line">
                            {question.support}
                        </div>
                    )}

                    <h1 id="enonce" className="font-titre text-[22px] leading-[1.2] tracking-[-0.5px]">
                        {question.enonce}
                    </h1>

                    <div role="radiogroup" aria-labelledby="enonce" className="flex flex-col gap-2.5">
                        {question.choix.map((libelle, i) => (
                            <button
                                key={i}
                                type="button"
                                role="radio"
                                aria-checked={choix === i}
                                disabled={verrouille}
                                onClick={() => choisir(i)}
                                className={cn(
                                    'flex min-h-14 items-center gap-3 rounded-2xl px-4 text-left text-base transition-colors',
                                    choix === i ? 'border-2 border-foret bg-peche-clair font-bold' : 'border-[1.5px] border-trait bg-white hover:border-cendre',
                                )}
                            >
                                <span
                                    className={cn(
                                        'flex size-7 shrink-0 items-center justify-center rounded-lg text-sm font-bold',
                                        choix === i ? 'bg-foret text-white' : 'bg-papier',
                                    )}
                                >
                                    {LETTRES[i]}
                                </span>
                                <span className="py-3">{libelle}</span>
                            </button>
                        ))}
                    </div>

                    {choix !== null && !verrouille && (
                        <button
                            type="button"
                            onClick={() => choisir(null)}
                            className="self-start text-sm font-semibold text-cendre underline underline-offset-4 hover:text-foret"
                        >
                            Effacer ma réponse
                        </button>
                    )}
                </main>

                {/* Navigation */}
                <div className="sticky bottom-0 border-t-[1.5px] border-trait bg-white/95 backdrop-blur">
                    <div className="mx-auto flex max-w-2xl items-center gap-2 px-4 pt-3 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                        <Button
                            variant="outline"
                            size="lg"
                            className="h-12 rounded-2xl border-[1.5px] border-trait bg-white px-3 font-bold sm:px-4"
                            disabled={position === 0}
                            onClick={() => aller(position - 1)}
                            aria-label="Question précédente"
                        >
                            <ChevronLeft className="size-5" />
                            <span className="hidden sm:inline">Précédente</span>
                        </Button>
                        <Button
                            variant="outline"
                            size="lg"
                            aria-pressed={marquee}
                            disabled={verrouille}
                            onClick={basculerMarque}
                            className={cn(
                                'h-12 flex-1 rounded-2xl px-3 font-bold',
                                marquee ? 'border-peche bg-peche hover:bg-peche/80' : 'border-[1.5px] border-trait bg-white',
                            )}
                        >
                            <span className={cn('size-2.5 rounded-full', marquee ? 'bg-foret' : 'bg-peche')} />
                            <span className="sm:hidden">À revoir</span>
                            <span className="hidden sm:inline">{marquee ? 'Marquée à revoir' : 'Marquer à revoir'}</span>
                        </Button>
                        {position + 1 < total ? (
                            <Button size="lg" className="h-12 rounded-2xl px-4 font-bold" onClick={() => aller(position + 1)}>
                                Suivante
                                <ChevronRight className="size-5" />
                            </Button>
                        ) : (
                            <Button
                                size="lg"
                                className="h-12 rounded-2xl bg-peche px-4 font-bold text-foret hover:bg-foret hover:text-white"
                                onClick={() => setConfirmer(true)}
                            >
                                Terminer
                            </Button>
                        )}
                    </div>
                </div>
            </SidebarInset>

            <DialogueTerminer
                open={confirmer}
                onOpenChange={setConfirmer}
                sansReponse={total - repondues}
                enCours={envoi}
                onTerminer={terminer}
                onAbandonner={() => router.delete(route('test-blanc.abandonner', tentative.id))}
            />
        </SidebarProvider>
    );
}

function TestVide({ complet }: { complet: boolean }) {
    return (
        <div className="flex min-h-svh flex-col bg-papier text-foret">
            <Head title="Test blanc" />
            <header className="flex h-16 items-center gap-3 bg-foret px-5 text-white">
                <AppLogoIcon inverse className="size-8 shrink-0" />
                <span className="text-[15px] font-bold">Test blanc</span>
            </header>
            <main className="flex flex-1 flex-col items-center justify-center gap-4 p-5 text-center">
                <h1 className="font-titre text-[22px] leading-tight">Pas encore de test blanc {complet ? 'disponible' : 'pour cette épreuve'}</h1>
                <Button asChild size="lg" className="h-12 rounded-2xl px-6 font-bold">
                    <Link href={route('espace')}>Retour à mon espace</Link>
                </Button>
            </main>
        </div>
    );
}

/** Regroupe les questions par épreuve puis par catégorie, dans l'ordre du test. */
function regrouper(questions: QuestionTest[], epreuves: EpreuveTest[]): Section[] {
    const sections: Section[] = [];

    questions.forEach((question, position) => {
        let section = sections.at(-1);

        if (!section || section.epreuve?.id !== question.epreuve_id) {
            section = { epreuve: epreuves.find((e) => e.id === question.epreuve_id), categories: [] };
            sections.push(section);
        }

        let categorie = section.categories.find((c) => c.nom === question.categorie);

        if (!categorie) {
            categorie = { nom: question.categorie, questions: [] };
            section.categories.push(categorie);
        }

        categorie.questions.push({ position, question });
    });

    return sections;
}
