import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { t } from '@/lib/i18n';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';

export type LienSecondaire = {
    title: string;
    href: string;
    icon: LucideIcon;
    /** Lien vers le site public Livewire : lien classique, pas de visite Inertia. */
    externe?: boolean;
    /** Libellé écrit dans une autre langue que la page (sélecteur de langue) : affiché tel quel. */
    langue?: string;
};

/** Liens secondaires, en bas de la sidebar. */
export function NavFooter({ items }: { items: LienSecondaire[] }) {
    const { setOpenMobile } = useSidebar();

    return (
        <SidebarGroup className="mt-auto">
            <SidebarGroupContent>
                <SidebarMenu>
                    {items.map((item) => (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                tooltip={item.langue ? item.title : t(item.title)}
                                className="text-sidebar-foreground/75 hover:text-sidebar-foreground"
                            >
                                {item.externe ? (
                                    <a href={item.href} lang={item.langue} hrefLang={item.langue}>
                                        <item.icon />
                                        <span>{item.langue ? item.title : t(item.title)}</span>
                                    </a>
                                ) : (
                                    <Link href={item.href} onClick={() => setOpenMobile(false)}>
                                        <item.icon />
                                        <span>{t(item.title)}</span>
                                    </Link>
                                )}
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ))}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    );
}
