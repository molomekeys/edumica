import { createInertiaApp } from '@inertiajs/react';
import { Direction } from 'radix-ui';
import type { ComponentType } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import { chargerTraductions, direction } from '@/lib/i18n';


/**
 * Dashboards Edumica (espace utilisateur, test blanc, admin) : Inertia + React, rendu côté client.
 */
// Traductions chargées avant le premier rendu (arabe seulement : en français, les clés sont le texte).
void chargerTraductions().then(() => createInertiaApp({
    title: (title) => (title ? `${title} · Edumica` : 'Edumica'),
    resolve: async (name) => {
        const pages = import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx');

        return (await pages[`./pages/${name}.tsx`]()).default;
    },
    // Le test blanc en cours a son propre écran, sans la navigation des dashboards.
    layout: (name) => (name === 'test-blanc/passer' ? null : AppLayout),
    strictMode: true,
    withApp(app) {
        return (
            // Sens d'écriture de la page (rtl en arabe) transmis aux composants Radix : menus, sélecteurs, tiroirs.
            <Direction.Provider dir={direction}>
                <TooltipProvider delayDuration={0}>
                    {app}
                    <Toaster position="top-center" dir={direction} />
                </TooltipProvider>
            </Direction.Provider>
        );
    },
    progress: {
        color: '#0E7A45',
    },
}));
