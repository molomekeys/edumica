import { useEffect, useRef, useState } from 'react';

/**
 * Compte à rebours calé sur l'échéance fixée par le serveur (horodatage Unix, en secondes).
 * « maintenant » (heure du serveur au rendu) corrige un éventuel décalage de l'horloge locale.
 */
export function useChrono(fin: number, maintenant: number, surFin: () => void): number {
    const [decalage] = useState(() => maintenant - Date.now() / 1000);
    const calculer = () => Math.max(0, Math.round(fin - (Date.now() / 1000 + decalage)));
    const [reste, setReste] = useState(calculer);
    const surFinRef = useRef(surFin);

    useEffect(() => {
        surFinRef.current = surFin;
    });

    useEffect(() => {
        const maj = () => {
            const restant = calculer();
            setReste(restant);

            if (restant === 0) {
                window.clearInterval(minuteur);
                surFinRef.current();
            }
        };

        const minuteur = window.setInterval(maj, 1000);
        maj();

        return () => window.clearInterval(minuteur);
    }, [fin, decalage]);

    return reste;
}

/** « 04:59 », ou « 1:04:59 » au-delà d'une heure (test complet). */
export function formatChrono(secondes: number): string {
    const heures = Math.floor(secondes / 3600);
    const minutes = String(Math.floor((secondes % 3600) / 60)).padStart(2, '0');
    const reste = String(secondes % 60).padStart(2, '0');

    return heures ? `${heures}:${minutes}:${reste}` : `${minutes}:${reste}`;
}
