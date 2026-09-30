import { BookOpen, CircleHelp, Headphones, Mic, PenLine } from 'lucide-react';
import type { LucideIcon, LucideProps } from 'lucide-react';

/** Icônes des épreuves (colonne « icone » de la table epreuves). */
const icones: Record<string, LucideIcon> = {
    casque: Headphones,
    livre: BookOpen,
    crayon: PenLine,
    micro: Mic,
};

export function IconeEpreuve({ nom, ...props }: LucideProps & { nom: string }) {
    const Icone = icones[nom] ?? CircleHelp;

    return <Icone aria-hidden="true" {...props} />;
}
