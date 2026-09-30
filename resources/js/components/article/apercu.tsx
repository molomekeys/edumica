import { Newspaper } from 'lucide-react';
import { Arcs } from '@/components/arcs';
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog';
import { dateFr } from '@/lib/format';

type Props = {
    ouvert: boolean;
    onFermer: () => void;
    titre: string;
    extrait: string;
    contenu: string;
    categorie: string;
    couverture: string | null;
    tempsLecture: number;
    publieLe: string;
    auteur: string;
};

/**
 * Aperçu de l'article tel qu'il paraîtra sur le site, avec le contenu en cours d'écriture
 * (non enregistré) : même en-tête menthe et même typographie de lecture.
 */
export function ApercuArticle({ ouvert, onFermer, titre, extrait, contenu, categorie, couverture, tempsLecture, publieLe, auteur }: Props) {
    return (
        <Dialog open={ouvert} onOpenChange={(etat) => !etat && onFermer()}>
            <DialogContent className="flex max-h-[92dvh] w-[calc(100%-1rem)] flex-col gap-0 overflow-hidden p-0 sm:max-w-5xl">
                <div className="flex items-center gap-2 border-b bg-muted/60 px-4 py-2.5 pr-12">
                    <span className="rounded-full bg-peche px-2.5 py-0.5 text-xs font-bold text-foret">Aperçu</span>
                    <DialogTitle className="truncate text-sm font-semibold">{titre || 'Sans titre'}</DialogTitle>
                    <DialogDescription className="sr-only">Aperçu de l'article tel qu'il paraîtra sur le site, avant enregistrement.</DialogDescription>
                </div>

                <div className="overflow-y-auto bg-white">
                    <div className="p-2 md:p-4">
                        <div className="relative isolate grid gap-6 overflow-hidden rounded-[24px] bg-menthe p-5 md:p-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,380px)] lg:items-center lg:gap-10">
                            <Arcs className="-bottom-[260px] -left-[200px] size-[560px]" />
                            <div className="flex flex-col gap-4">
                                <span className="self-start rounded-full bg-white px-3 py-1 text-[13px] font-bold">{categorie}</span>
                                <h1 className="font-titre text-[26px] leading-[1.08] tracking-[-0.8px] text-balance md:text-[36px]">{titre || 'Titre de l’article'}</h1>
                                {extrait && <p className="text-[17px] leading-normal text-mousse-fonce">{extrait}</p>}
                                <p className="text-sm font-semibold text-mousse-fonce">
                                    {auteur} · {dateFr(publieLe || new Date().toISOString())} · {tempsLecture} min de lecture
                                </p>
                            </div>
                            <div className="aspect-[16/10] overflow-hidden rounded-[20px] bg-vert">
                                {couverture ? (
                                    <img src={couverture} alt="" className="size-full object-cover" />
                                ) : (
                                    <div className="flex size-full items-center justify-center text-menthe">
                                        <Newspaper className="size-12" aria-hidden="true" />
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="px-5 py-8 md:px-12 md:py-12">
                        {contenu ? (
                            <div className="contenu-article mx-auto max-w-[70ch]" dangerouslySetInnerHTML={{ __html: contenu }} />
                        ) : (
                            <p className="text-center text-muted-foreground">Le contenu est vide.</p>
                        )}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}
