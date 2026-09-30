import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, X } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { LETTRES } from '@/components/test-blanc/types';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { route } from '@/lib/routes';

const CHOIX_MIN = 2;
const CHOIX_MAX = 6;

type Question = {
    id: number | null;
    epreuve_id: number | null;
    categorie: string;
    ordre: number;
    enonce: string;
    support: string;
    audio: string;
    duree_audio: number | '';
    transcription: string;
    choix: string[];
    bonne_reponse: number;
    feedback: string;
    explication: string;
};

type Props = {
    question: Question;
    epreuves: { id: number; nom: string }[];
    /** Catégories existantes par épreuve, pour les suggestions. */
    categories: Record<string, string[]>;
    /** Prochain numéro d'ordre par épreuve. */
    prochainsOrdres: Record<string, number>;
};

export default function FormulaireQuestion({ question, epreuves, categories, prochainsOrdres }: Props) {
    const { id, ...valeurs } = question;
    const form = useForm(valeurs);
    const { data, setData, errors, processing } = form;
    // En création, l'ordre suit l'épreuve choisie tant qu'il n'a pas été saisi à la main.
    const [ordreSaisi, setOrdreSaisi] = useState(id !== null);

    const enregistrer = (event: FormEvent) => {
        event.preventDefault();

        if (id) {
            form.put(route('admin.questions.mettre-a-jour', id));
        } else {
            form.post(route('admin.questions.enregistrer'));
        }
    };

    const choisirEpreuve = (valeur: string) => {
        const epreuveId = Number(valeur);
        setData((actuelles) => ({
            ...actuelles,
            epreuve_id: epreuveId,
            ordre: ordreSaisi ? actuelles.ordre : (prochainsOrdres[valeur] ?? 1),
        }));
    };

    const modifierChoix = (index: number, libelle: string) => setData('choix', data.choix.map((actuel, i) => (i === index ? libelle : actuel)));

    const ajouterChoix = () => {
        if (data.choix.length < CHOIX_MAX) {
            setData('choix', [...data.choix, '']);
        }
    };

    const retirerChoix = (index: number) => {
        if (data.choix.length <= CHOIX_MIN) {
            return;
        }

        setData((actuelles) => ({
            ...actuelles,
            choix: actuelles.choix.filter((_, i) => i !== index),
            bonne_reponse:
                actuelles.bonne_reponse === index ? 0 : actuelles.bonne_reponse > index ? actuelles.bonne_reponse - 1 : actuelles.bonne_reponse,
        }));
    };

    const titre = id ? 'Modifier la question' : 'Nouvelle question';

    return (
        <>
            <Head title={`Admin · ${titre}`} />

            <form onSubmit={enregistrer} className="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-5 p-4 md:p-6">
                <div className="flex flex-col gap-2">
                    <Link href={route('admin.questions')} className="inline-flex items-center gap-1 self-start text-sm font-bold text-muted-foreground hover:text-foreground">
                        <ArrowLeft className="size-4" />
                        Toutes les questions
                    </Link>
                    <Heading titre={titre} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Classement</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 sm:grid-cols-3">
                        <Champ label="Épreuve" htmlFor="epreuve_id" erreur={errors.epreuve_id}>
                            <Select value={data.epreuve_id ? String(data.epreuve_id) : undefined} onValueChange={choisirEpreuve}>
                                <SelectTrigger id="epreuve_id" className="w-full" aria-invalid={Boolean(errors.epreuve_id)}>
                                    <SelectValue placeholder="Choisir une épreuve" />
                                </SelectTrigger>
                                <SelectContent>
                                    {epreuves.map((epreuve) => (
                                        <SelectItem key={epreuve.id} value={String(epreuve.id)}>
                                            {epreuve.nom}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Champ>
                        <Champ label="Catégorie" htmlFor="categorie" erreur={errors.categorie}>
                            <Input
                                id="categorie"
                                list="categories"
                                value={data.categorie}
                                onChange={(event) => setData('categorie', event.target.value)}
                                placeholder="Ex. Vie quotidienne"
                                aria-invalid={Boolean(errors.categorie)}
                            />
                            <datalist id="categories">
                                {(categories[String(data.epreuve_id)] ?? []).map((categorie) => (
                                    <option key={categorie} value={categorie} />
                                ))}
                            </datalist>
                        </Champ>
                        <Champ label="Ordre" htmlFor="ordre" erreur={errors.ordre}>
                            <Input
                                id="ordre"
                                type="number"
                                min={0}
                                value={data.ordre}
                                onChange={(event) => {
                                    setOrdreSaisi(true);
                                    setData('ordre', Number(event.target.value));
                                }}
                                aria-invalid={Boolean(errors.ordre)}
                            />
                        </Champ>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Support</CardTitle>
                        <CardDescription>Audio ou texte lu pour la compréhension orale, document pour la compréhension écrite.</CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        <Champ
                            label="Transcription / texte lu à voix haute"
                            aide="Sans fichier audio, ce texte est lu une seule fois par la voix de synthèse du navigateur."
                            htmlFor="transcription"
                            erreur={errors.transcription}
                        >
                            <Textarea id="transcription" rows={4} value={data.transcription} onChange={(event) => setData('transcription', event.target.value)} />
                        </Champ>
                        <div className="grid gap-4 sm:grid-cols-[1fr_160px]">
                            <Champ label="Fichier audio (optionnel)" htmlFor="audio" erreur={errors.audio}>
                                <Input
                                    id="audio"
                                    value={data.audio}
                                    onChange={(event) => setData('audio', event.target.value)}
                                    placeholder="audios/question-12.mp3 ou https://…"
                                />
                            </Champ>
                            <Champ label="Durée (s)" htmlFor="duree_audio" erreur={errors.duree_audio}>
                                <Input
                                    id="duree_audio"
                                    type="number"
                                    min={1}
                                    value={data.duree_audio}
                                    onChange={(event) => setData('duree_audio', event.target.value === '' ? '' : Number(event.target.value))}
                                    placeholder="30"
                                />
                            </Champ>
                        </div>
                        <Champ label="Document à lire (compréhension écrite)" htmlFor="support" erreur={errors.support}>
                            <Textarea id="support" rows={4} value={data.support} onChange={(event) => setData('support', event.target.value)} />
                        </Champ>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Question</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-5">
                        <Champ label="Énoncé" htmlFor="enonce" erreur={errors.enonce}>
                            <Input id="enonce" value={data.enonce} onChange={(event) => setData('enonce', event.target.value)} aria-invalid={Boolean(errors.enonce)} />
                        </Champ>

                        <fieldset className="flex flex-col gap-2">
                            <legend className="mb-2 text-sm font-semibold">
                                Choix <span className="font-normal text-muted-foreground">— coche la bonne réponse</span>
                            </legend>
                            <RadioGroup
                                value={String(data.bonne_reponse)}
                                onValueChange={(valeur) => setData('bonne_reponse', Number(valeur))}
                                className="gap-2"
                                aria-label="Bonne réponse"
                            >
                                {data.choix.map((libelle, i) => {
                                    const erreur = errors[`choix.${i}` as keyof typeof errors];

                                    return (
                                        <div key={i} className="flex flex-col gap-1">
                                            <div className="flex items-center gap-2">
                                                <RadioGroupItem value={String(i)} aria-label={`Bonne réponse : choix ${LETTRES[i]}`} className="size-5" />
                                                <span className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-muted text-sm font-bold">{LETTRES[i]}</span>
                                                <Input
                                                    value={libelle}
                                                    onChange={(event) => modifierChoix(i, event.target.value)}
                                                    aria-label={`Choix ${LETTRES[i]}`}
                                                    aria-invalid={Boolean(erreur)}
                                                />
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    disabled={data.choix.length <= CHOIX_MIN}
                                                    onClick={() => retirerChoix(i)}
                                                    aria-label={`Retirer le choix ${LETTRES[i]}`}
                                                    className="shrink-0 text-muted-foreground"
                                                >
                                                    <X />
                                                </Button>
                                            </div>
                                            <InputError message={erreur} className="pl-[76px]" />
                                        </div>
                                    );
                                })}
                            </RadioGroup>
                            <InputError message={errors.bonne_reponse ?? errors.choix} />
                            {data.choix.length < CHOIX_MAX && (
                                <Button type="button" variant="ghost" size="sm" className="self-start font-bold text-vert" onClick={ajouterChoix}>
                                    <Plus />
                                    Ajouter un choix
                                </Button>
                            )}
                        </fieldset>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Correction</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-4">
                        <Champ label="Explication" htmlFor="explication" erreur={errors.explication}>
                            <Textarea
                                id="explication"
                                rows={3}
                                value={data.explication}
                                onChange={(event) => setData('explication', event.target.value)}
                                aria-invalid={Boolean(errors.explication)}
                            />
                        </Champ>
                        <Champ label="Message si bonne réponse (quiz)" htmlFor="feedback" erreur={errors.feedback}>
                            <Input id="feedback" value={data.feedback} onChange={(event) => setData('feedback', event.target.value)} />
                        </Champ>
                    </CardContent>
                </Card>

                <div className="flex justify-end gap-2">
                    <Button asChild variant="outline" size="lg">
                        <Link href={route('admin.questions')}>Annuler</Link>
                    </Button>
                    <Button type="submit" size="lg" className="font-bold" disabled={processing}>
                        Enregistrer
                    </Button>
                </div>
            </form>
        </>
    );
}

FormulaireQuestion.layout = (props: Props) => ({
    breadcrumbs: [
        { title: 'Questions', href: route('admin.questions') },
        {
            title: props.question.id ? 'Modifier la question' : 'Nouvelle question',
            href: props.question.id ? route('admin.questions.modifier', props.question.id) : route('admin.questions.creer'),
        },
    ],
});

function Champ({ label, aide, htmlFor, erreur, children }: { label: string; aide?: string; htmlFor: string; erreur?: string; children: ReactNode }) {
    return (
        <div className="flex flex-col gap-1.5">
            <Label htmlFor={htmlFor}>{label}</Label>
            {aide && <p className="text-[13px] text-muted-foreground">{aide}</p>}
            {children}
            <InputError message={erreur} />
        </div>
    );
}
