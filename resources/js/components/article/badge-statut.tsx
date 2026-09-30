import { CalendarClock, CircleDashed, Globe } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

/** Statut affiché d'un article : en ligne, publié à une date future, ou brouillon. */
export type StatutArticle = 'publie' | 'programme' | 'brouillon';

const statuts = {
    publie: { libelle: 'Publié', icone: Globe, classes: 'bg-menthe text-vert' },
    programme: { libelle: 'Programmé', icone: CalendarClock, classes: 'bg-peche-clair text-foret' },
    brouillon: { libelle: 'Brouillon', icone: CircleDashed, classes: 'border-border bg-card text-muted-foreground' },
};

export function BadgeStatut({ statut, className }: { statut: StatutArticle; className?: string }) {
    const { libelle, icone: Icone, classes } = statuts[statut];

    return (
        <Badge variant="outline" className={cn('gap-1 border-transparent font-semibold', classes, className)}>
            <Icone aria-hidden="true" />
            {libelle}
        </Badge>
    );
}
