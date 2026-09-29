import { PanelLeftClose } from 'lucide-react';
import type { Section } from '@/components/test-blanc/types';
import { IconeEpreuve } from '@/components/icone-epreuve';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, useSidebar } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';

/**
 * Volet des questions du test blanc (sidebar shadcn) : rétractable sur grand écran,
 * tiroir sur mobile. Chaque question est une case : répondue, sans réponse, à revoir.
 */
export function VoletQuestions({
    sections,
    complet,
    total,
    repondues,
    position,
    estRepondue,
    estMarquee,
    onAller,
}: {
    sections: Section[];
    complet: boolean;
    total: number;
    repondues: number;
    position: number;
    estRepondue: (index: number) => boolean;
    estMarquee: (index: number) => boolean;
    onAller: (position: number) => void;
}) {
    const { isMobile, setOpen, setOpenMobile } = useSidebar();

    return (
        <Sidebar collapsible="offcanvas" aria-label="Liste des questions">
            <SidebarHeader className="h-16 flex-row items-center justify-between gap-2 border-b border-sidebar-border px-4">
                <div className="flex flex-col">
                    <span className="text-[15px] font-extrabold">Questions</span>
                    <span className="text-xs font-semibold text-sidebar-foreground/65 tabular-nums">
                        {repondues} sur {total} répondues
                    </span>
                </div>
                <button
                    type="button"
                    onClick={() => (isMobile ? setOpenMobile(false) : setOpen(false))}
                    aria-label="Masquer la liste des questions"
                    className="flex size-9 items-center justify-center rounded-lg text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-foreground"
                >
                    <PanelLeftClose className="size-5" />
                </button>
            </SidebarHeader>

            <SidebarContent className="gap-5 px-4 py-4">
                {sections.map((section, s) => (
                    <section key={section.epreuve?.id ?? s} className="flex flex-col gap-3.5">
                        {complet && section.epreuve && (
                            <div className="flex items-center gap-2 border-b border-sidebar-border pb-2">
                                <span className="flex size-7 items-center justify-center rounded-lg bg-white/10 text-peche">
                                    <IconeEpreuve nom={section.epreuve.icone} className="size-4" />
                                </span>
                                <span className="text-sm font-extrabold">{section.epreuve.nom}</span>
                            </div>
                        )}

                        {section.categories.map((categorie) => (
                            <div key={categorie.nom} className="flex flex-col gap-2">
                                <div className="flex items-baseline justify-between gap-2 text-xs font-semibold text-sidebar-foreground/60">
                                    <span className="font-bold tracking-[0.08em] uppercase">{categorie.nom}</span>
                                    <span className="tabular-nums">
                                        {categorie.questions.filter(({ question }) => estRepondue(question.index)).length}/{categorie.questions.length}
                                    </span>
                                </div>
                                <div className="grid grid-cols-5 gap-1.5">
                                    {categorie.questions.map(({ position: rang, question }) => {
                                        const repondue = estRepondue(question.index);
                                        const marquee = estMarquee(question.index);

                                        return (
                                            <button
                                                key={question.id}
                                                type="button"
                                                onClick={() => {
                                                    onAller(rang);
                                                    setOpenMobile(false);
                                                }}
                                                aria-label={`Question ${rang + 1}${repondue ? ', répondue' : ''}${marquee ? ', à revoir' : ''}`}
                                                aria-current={rang === position ? 'step' : undefined}
                                                className={cn(
                                                    'relative flex h-10 items-center justify-center rounded-lg text-sm font-bold tabular-nums transition-colors',
                                                    repondue
                                                        ? 'bg-menthe text-foret'
                                                        : 'border-[1.5px] border-white/25 text-white hover:border-white/60',
                                                    rang === position && 'outline-2 outline-offset-2 outline-peche',
                                                )}
                                            >
                                                {rang + 1}
                                                {marquee && <span className="absolute -top-1 -right-1 size-3 rounded-full border-2 border-sidebar bg-peche" />}
                                            </button>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </section>
                ))}
            </SidebarContent>

            <SidebarFooter className="flex-row flex-wrap gap-x-4 gap-y-1.5 border-t border-sidebar-border px-4 py-3 text-xs text-sidebar-foreground/70">
                <div className="flex items-center gap-1.5">
                    <span className="size-3 rounded bg-menthe" />
                    Répondue
                </div>
                <div className="flex items-center gap-1.5">
                    <span className="size-3 rounded border-[1.5px] border-white/40" />
                    Sans réponse
                </div>
                <div className="flex items-center gap-1.5">
                    <span className="size-3 rounded-full bg-peche" />À revoir
                </div>
            </SidebarFooter>
        </Sidebar>
    );
}
