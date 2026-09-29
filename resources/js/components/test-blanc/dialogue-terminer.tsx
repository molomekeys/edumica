import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { pluriel } from '@/lib/format';

/** Fin du test : terminer pour voir ses résultats, ou abandonner sans rien garder. */
export function DialogueTerminer({
    open,
    onOpenChange,
    sansReponse,
    enCours,
    onTerminer,
    onAbandonner,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    sansReponse: number;
    enCours: boolean;
    onTerminer: () => void;
    onAbandonner: () => void;
}) {
    const [abandon, setAbandon] = useState(false);

    return (
        <Dialog
            open={open}
            onOpenChange={(ouvert) => {
                onOpenChange(ouvert);
                setAbandon(false);
            }}
        >
            <DialogContent className="gap-5 rounded-[24px] sm:max-w-md">
                <DialogHeader>
                    <DialogTitle className="font-titre text-xl">Terminer le test&nbsp;?</DialogTitle>
                    <DialogDescription className="text-[15px]">
                        {sansReponse > 0
                            ? `Il te reste ${pluriel(sansReponse, 'question')} sans réponse.`
                            : 'Tu as répondu à toutes les questions.'}{' '}
                        Tu ne pourras plus modifier tes réponses.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter className="flex-row gap-2 sm:justify-stretch">
                    <Button variant="outline" size="lg" className="flex-1" onClick={() => onOpenChange(false)}>
                        Continuer
                    </Button>
                    <Button size="lg" className="flex-1" disabled={enCours} onClick={onTerminer}>
                        Terminer
                    </Button>
                </DialogFooter>

                <div className="flex items-center justify-between gap-3 border-t pt-4">
                    <p className="text-[13px] leading-snug text-muted-foreground">
                        {abandon ? 'Sûr ? Tes réponses seront perdues.' : 'Tu veux arrêter là, sans résultat ?'}
                    </p>
                    {abandon ? (
                        <Button size="sm" disabled={enCours} className="shrink-0 bg-peche text-foret hover:bg-foret hover:text-white" onClick={onAbandonner}>
                            Oui, abandonner
                        </Button>
                    ) : (
                        <Button size="sm" variant="outline" className="shrink-0 border-peche hover:bg-peche-clair" onClick={() => setAbandon(true)}>
                            Abandonner
                        </Button>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}
