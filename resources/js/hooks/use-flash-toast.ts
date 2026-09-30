import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

/** Messages déjà affichés : une page qui revient de l'historique ne les réaffiche pas. */
const affiches = new Set<string>();

/** Affiche en toast les messages flash partagés par HandleInertiaRequests. */
export function useFlashToast(): void {
    const { flash } = usePage().props;

    useEffect(() => {
        if (!flash || affiches.has(flash.id)) {
            return;
        }

        affiches.add(flash.id);

        if (flash.succes) {
            toast.success(flash.succes);
        }

        if (flash.erreur) {
            toast.error(flash.erreur);
        }
    }, [flash]);
}
