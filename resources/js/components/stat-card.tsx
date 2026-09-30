import type { LucideIcon } from 'lucide-react';
import { Card, CardContent } from '@/components/ui/card';

/** Carte de statistique du tableau de bord. */
export function StatCard({ titre, valeur, detail, icone: Icone }: { titre: string; valeur: string; detail: string; icone: LucideIcon }) {
    return (
        <Card className="gap-0 py-0">
            <CardContent className="flex items-start gap-4 p-5">
                <div className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-menthe text-foret">
                    <Icone className="size-5" aria-hidden="true" />
                </div>
                <div className="flex min-w-0 flex-col gap-0.5">
                    <p className="text-sm font-semibold text-muted-foreground">{titre}</p>
                    <p className="font-titre text-[26px] leading-tight tracking-[-0.5px] tabular-nums">{valeur}</p>
                    <p className="truncate text-[13px] text-muted-foreground">{detail}</p>
                </div>
            </CardContent>
        </Card>
    );
}
