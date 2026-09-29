import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';

/**
 * Dashboards Edumica (espace utilisateur, test blanc, admin) : Inertia + React, rendu côté client.
 */
void createInertiaApp({
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
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster position="top-center" />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#0E7A45',
    },
});
