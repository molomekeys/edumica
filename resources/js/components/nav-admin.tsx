import { Link, usePage } from '@inertiajs/react';
import { ChevronRight, ListChecks, Newspaper } from 'lucide-react';
import { actifMenthe } from '@/components/nav-main';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuAction,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { route } from '@/lib/routes';

/** Navigation admin : les questions, avec les épreuves en sous-menu repliable qui filtrent la liste, et les articles du blog. */
export function NavAdmin() {
    const { navigation } = usePage().props;
    const { chemin, parametres, estSousChemin } = useCurrentUrl();
    const surArticles = estSousChemin(route('admin.articles'));
    const { setOpenMobile } = useSidebar();

    // Filtre actif de la liste ; null hors de la liste (formulaire d'une question).
    const filtre = chemin === route('admin.questions') ? (parametres.get('epreuve') ?? '') : null;
    const sousMenu = [{ slug: '', nom: 'Toutes les épreuves', questions_count: null }, ...navigation.epreuves];

    return (
        <SidebarGroup>
            <SidebarGroupLabel>Administration</SidebarGroupLabel>
            <SidebarMenu>
                <Collapsible asChild defaultOpen className="group/collapsible">
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild isActive={estSousChemin('/admin') && !surArticles} tooltip="Questions" className={actifMenthe}>
                            <Link href={route('admin.questions')} onClick={() => setOpenMobile(false)}>
                                <ListChecks />
                                <span>Questions</span>
                            </Link>
                        </SidebarMenuButton>
                        <CollapsibleTrigger asChild>
                            <SidebarMenuAction className="transition-transform data-[state=open]:rotate-90">
                                <ChevronRight />
                                <span className="sr-only">Afficher ou masquer les épreuves</span>
                            </SidebarMenuAction>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                {sousMenu.map((epreuve) => (
                                    <SidebarMenuSubItem key={epreuve.slug}>
                                        <SidebarMenuSubButton asChild isActive={filtre === epreuve.slug} className="data-[active=true]:font-semibold">
                                            <Link href={route('admin.questions', { epreuve: epreuve.slug })} onClick={() => setOpenMobile(false)}>
                                                <span className="min-w-0 flex-1 truncate">{epreuve.nom}</span>
                                                {epreuve.questions_count !== null && (
                                                    <span className="shrink-0 text-xs tabular-nums opacity-60">{epreuve.questions_count}</span>
                                                )}
                                            </Link>
                                        </SidebarMenuSubButton>
                                    </SidebarMenuSubItem>
                                ))}
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>
                <SidebarMenuItem>
                    <SidebarMenuButton asChild isActive={surArticles} tooltip="Articles" className={actifMenthe}>
                        <Link href={route('admin.articles')} onClick={() => setOpenMobile(false)}>
                            <Newspaper />
                            <span>Articles</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroup>
    );
}
