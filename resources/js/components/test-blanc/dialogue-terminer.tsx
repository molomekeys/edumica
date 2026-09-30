import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { pluriel } from '@/lib/format';
import { t } from '@/lib/i18n';

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
                    <DialogTitle className="font-titre text-xl">{t('Terminer le test ?')}</DialogTitle>
                    <DialogDescription className="text-[15px]">
                        {sansReponse > 0
                            ? pluriel(sansReponse, 'Il te reste :count question sans réponse.|Il te reste :count questions sans réponse.')
                            : t('Tu as répondu à toutes les questions.')}{' '}
                        {t('Tu ne pourras plus modifier tes réponses.')}
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter className="flex-row gap-2 sm:justify-stretch">
                    <Button variant="outline" size="lg" className="flex-1" onClick={() => onOpenChange(false)}>
                        {t('Continuer')}
                    </Button>
                    <Button size="lg" className="flex-1" disabled={enCours} onClick={onTerminer}>
                        {t('Terminer')}
                    </Button>
                </DialogFooter>

                <div className="flex items-center justify-between gap-3 border-t pt-4">
                    <p className="text-[13px] leading-snug text-muted-foreground">
                        {abandon ? t('Sûr ? Tes réponses seront perdues.') : t('Tu veux arrêter là, sans résultat ?')}
                    </p>
                    {abandon ? (
                        <Button size="sm" disabled={enCours} className="shrink-0 bg-peche text-foret hover:bg-foret hover:text-white" onClick={onAbandonner}>
                            {t('Oui, abandonner')}
                        </Button>
                    ) : (
                        <Button size="sm" variant="outline" className="shrink-0 border-peche hover:bg-peche-clair" onClick={() => setAbandon(true)}>
                            {t('Abandonner')}
                        </Button>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}
