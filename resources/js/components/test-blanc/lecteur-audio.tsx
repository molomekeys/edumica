import { Lock, Pause, Play } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { t } from '@/lib/i18n';
import { cn } from '@/lib/utils';

const BARRES = [34, 18, 26, 10, 14, 14, 34, 10, 24, 10, 14, 26, 26, 14, 24, 14, 26, 10, 14, 24, 10, 26, 10, 24, 10, 18, 30, 26, 18, 14];

function format(secondes: number): string {
    const s = Math.max(0, Math.round(secondes));

    return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
}

/**
 * Lecteur « une seule écoute ». Lit le fichier audio s'il existe, sinon fait lire la
 * transcription par la synthèse vocale du navigateur. « ecoutee » : l'écoute a déjà eu lieu,
 * le lecteur démarre verrouillé. « onDebut » : appelé au lancement, pour que le verrou
 * survive à la navigation entre questions et au rechargement de la page.
 */
export function LecteurAudio({
    source,
    transcription,
    duree: dureeEstimee,
    ecoutee,
    onDebut,
    className,
}: {
    source: string | null;
    transcription: string | null;
    duree: number;
    ecoutee: boolean;
    onDebut: () => void;
    className?: string;
}) {
    const [duree, setDuree] = useState(dureeEstimee);
    const [position, setPosition] = useState(0);
    const [enLecture, setEnLecture] = useState(false);
    const [fini, setFini] = useState(ecoutee);
    const audio = useRef<HTMLAudioElement | null>(null);
    const minuteurs = useRef<number[]>([]);

    // En quittant la question, la lecture s'arrête (et l'écoute reste consommée).
    useEffect(
        () => () => {
            minuteurs.current.forEach((id) => window.clearTimeout(id));
            audio.current?.pause();

            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
            }
        },
        [],
    );

    const terminer = () => {
        minuteurs.current.forEach((id) => window.clearTimeout(id));
        setEnLecture(false);
        setFini(true);
    };

    const jouer = () => {
        if (enLecture || fini) {
            return;
        }

        setEnLecture(true);
        onDebut();

        if (source) {
            const lecteur = new Audio(source);
            audio.current = lecteur;
            lecteur.addEventListener('loadedmetadata', () => setDuree(Math.round(lecteur.duration)));
            lecteur.addEventListener('timeupdate', () => setPosition(lecteur.currentTime));
            lecteur.addEventListener('ended', terminer);
            void lecteur.play();

            return;
        }

        // La durée est estimée : on reste juste avant la fin tant que la voix parle.
        const debut = performance.now();
        minuteurs.current.push(window.setInterval(() => setPosition(Math.min(duree - 0.5, (performance.now() - debut) / 1000)), 100));

        if ('speechSynthesis' in window && transcription) {
            const enonce = new SpeechSynthesisUtterance(transcription.replace(/—/g, ''));
            enonce.lang = 'fr-FR';
            enonce.rate = 0.95;
            enonce.addEventListener('end', terminer);
            window.speechSynthesis.cancel();
            window.speechSynthesis.speak(enonce);
        } else {
            minuteurs.current.push(window.setTimeout(terminer, duree * 1000));
        }
    };

    const progression = fini ? 1 : duree ? position / duree : 0;

    return (
        <div className={cn('flex flex-col gap-2.5 rounded-[20px] border-[1.5px] border-trait bg-white p-4', className)}>
            <div className="flex items-center gap-3.5">
                <button
                    type="button"
                    onClick={jouer}
                    disabled={enLecture || fini}
                    aria-label={fini ? t('Enregistrement déjà écouté') : t("Écouter l'enregistrement")}
                    className="flex size-14 shrink-0 items-center justify-center rounded-full bg-foret text-white transition-opacity disabled:opacity-40"
                >
                    {fini ? <Lock className="size-5" /> : enLecture ? <Pause className="size-5 fill-current" /> : <Play className="size-5 fill-current" />}
                </button>
                <div className="flex h-9 flex-1 items-center gap-[3px] overflow-hidden" aria-hidden="true">
                    {BARRES.map((hauteur, i) => (
                        <div
                            key={i}
                            className={cn('w-1 shrink-0 rounded-sm transition-colors', progression > i / BARRES.length ? 'bg-foret' : 'bg-foret/25')}
                            style={{ height: hauteur }}
                        />
                    ))}
                </div>
            </div>
            <div className="flex justify-between gap-3 text-[13px] text-mousse-fonce">
                <span className="font-bold" aria-live="polite">
                    {fini ? t('Écoute terminée · lecture bloquée') : enLecture ? t('Écoute en cours…') : t("Une seule écoute, comme à l'examen")}
                </span>
                <span className="shrink-0 whitespace-nowrap tabular-nums" dir="ltr">
                    {format(fini ? duree : position)} / {format(duree)}
                </span>
            </div>
        </div>
    );
}
