import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
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
                            <SidebarMenuButton asChild tooltip={item.title} className="text-sidebar-foreground/75 hover:text-sidebar-foreground">
                                {item.externe ? (
                                    <a href={item.href}>
                                        <item.icon />
                                        <span>{item.title}</span>
                                    </a>
                                ) : (
                                    <Link href={item.href} onClick={() => setOpenMobile(false)}>
                                        <item.icon />
                                        <span>{item.title}</span>
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
