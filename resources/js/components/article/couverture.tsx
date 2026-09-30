import { ImageUp, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import type { DragEvent } from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export const TAILLE_MAX_COUVERTURE = 4 * 1024 * 1024;

/** URL affichable de la couverture : le fichier choisi, sinon l'image enregistrée. */
export function useApercuCouverture(fichier: File | null, url: string | null): string | null {
    const locale = useMemo(() => (fichier ? URL.createObjectURL(fichier) : null), [fichier]);

    useEffect(() => () => (locale ? URL.revokeObjectURL(locale) : undefined), [locale]);

    return locale ?? url;
}

/** Zone de dépôt de la couverture : glisser-déposer ou clic, avec aperçu. */
export function ChampCouverture({
    apercu,
    onChoisir,
    onRetirer,
    invalide = false,
}: {
    apercu: string | null;
    onChoisir: (fichier: File) => void;
    onRetirer: () => void;
    invalide?: boolean;
}) {
    const [survol, setSurvol] = useState(false);
    const champ = useRef<HTMLInputElement>(null);

    const deposer = (evenement: DragEvent) => {
        evenement.preventDefault();
        setSurvol(false);
        const fichier = Array.from(evenement.dataTransfer.files).find((f) => f.type.startsWith('image/'));

        if (fichier) {
            onChoisir(fichier);
        }
    };

    return (
        <div className="flex flex-col gap-2">
            <div
                role="button"
                tabIndex={0}
                aria-label={apercu ? 'Changer la couverture' : 'Ajouter une couverture'}
                onClick={() => champ.current?.click()}
                onKeyDown={(evenement) => (evenement.key === 'Enter' || evenement.key === ' ') && champ.current?.click()}
                onDragOver={(evenement) => {
                    evenement.preventDefault();
                    setSurvol(true);
                }}
                onDragLeave={() => setSurvol(false)}
                onDrop={deposer}
                className={cn(
                    'group relative flex aspect-[16/10] cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed bg-muted/60 transition-colors outline-none focus-visible:ring-[3px] focus-visible:ring-ring/40',
                    survol ? 'border-vert bg-menthe/60' : 'border-border hover:border-vert/50',
                    apercu && 'border-solid',
                    invalide && 'border-destructive',
                )}
            >
                {apercu ? (
                    <>
                        <img src={apercu} alt="" className="size-full object-cover" />
                        <div className="absolute inset-0 flex items-center justify-center bg-foret/50 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
                            <span className="flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-sm font-bold text-foret">
                                <ImageUp className="size-4" />
                                Remplacer
                            </span>
                        </div>
                    </>
                ) : (
                    <div className="flex flex-col items-center gap-2 px-4 text-center">
                        <div className="flex size-11 items-center justify-center rounded-full bg-card text-vert shadow-xs">
                            <ImageUp className="size-5" />
                        </div>
                        <p className="text-sm font-semibold">Glisse une image ici</p>
                        <p className="text-xs text-muted-foreground">ou clique pour choisir · JPG, PNG, WebP · 4 Mo max</p>
                    </div>
                )}
            </div>
            <input
                ref={champ}
                type="file"
                accept="image/jpeg,image/png,image/webp"
                className="hidden"
                onChange={(evenement) => {
                    const fichier = evenement.target.files?.[0];

                    if (fichier) {
                        onChoisir(fichier);
                    }

                    evenement.target.value = '';
                }}
            />
            {apercu && (
                <Button type="button" variant="ghost" size="sm" className="self-start text-muted-foreground hover:text-destructive" onClick={onRetirer}>
                    <Trash2 />
                    Retirer la couverture
                </Button>
            )}
        </div>
    );
}
