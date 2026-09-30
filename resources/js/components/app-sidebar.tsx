import { ArrowLeftRight, ChartNoAxesColumn, Globe, Languages, LayoutDashboard, Timer } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavAdmin } from '@/components/nav-admin';
import { NavFooter } from '@/components/nav-footer';
import type { LienSecondaire } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarRail,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { langue, t } from '@/lib/i18n';
import { route } from '@/lib/routes';
import type { NavItem } from '@/types';

const retourAuSite: LienSecondaire = { title: 'Retour au site', href: route('accueil'), icon: Globe, externe: true };

/** Bascule vers l'autre langue : recharge la page (sens d'écriture et traductions changent). */
const autreLangue: LienSecondaire =
    langue === 'ar'
        ? { title: 'Français', href: route('langue', 'fr'), icon: Languages, externe: true, langue: 'fr' }
        : { title: 'العربية', href: route('langue', 'ar'), icon: Languages, externe: true, langue: 'ar' };

export function AppSidebar() {
    const { estActif, estSousChemin } = useCurrentUrl();
    const enAdmin = estSousChemin('/admin');

    const navUtilisateur: NavItem[] = [
        { title: t('Tableau de bord'), href: route('espace'), icon: LayoutDashboard, isActive: estActif(route('espace')) },
        { title: t('Tests blancs'), href: route('espace.tests-blancs'), icon: Timer, isActive: estSousChemin(route('espace.tests-blancs'), '/test-blanc') },
        { title: t('Mes résultats'), href: route('espace.resultats'), icon: ChartNoAxesColumn, isActive: estSousChemin(route('espace.resultats')) },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        {/* Le site public est en Livewire : lien classique. */}
                        <SidebarMenuButton size="lg" asChild tooltip={t('Edumica · accueil du site')}>
                            <a href={route('accueil')} aria-label={t('Edumica, accueil du site')}>
                                <AppLogo />
                            </a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {enAdmin ? <NavAdmin /> : <NavMain label={t('Mon espace')} items={navUtilisateur} />}
                <NavFooter
                    items={
                        enAdmin
                            ? [{ title: 'Espace utilisateur', href: route('espace'), icon: ArrowLeftRight }, retourAuSite]
                            : [retourAuSite, autreLangue]
                    }
                />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>
    );
}
