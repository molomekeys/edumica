import type { LucideIcon } from 'lucide-react';

/** Utilisateur connecté, partagé par HandleInertiaRequests. */
export type User = {
    name: string;
    email: string;
    initiales: string;
    is_admin: boolean;
};

/** Messages flash de la session, affichés en toast. */
export type Flash = {
    id: string;
    succes: string | null;
    erreur: string | null;
} | null;

export type EpreuveNavigation = {
    slug: string;
    nom: string;
    questions_count: number;
};

export type BreadcrumbItem = {
    title: string;
    href: string;
};

export type NavItem = {
    title: string;
    href: string;
    icon: LucideIcon;
    isActive: boolean;
};

/** Épreuve du catalogue des tests blancs (EspaceController). */
export type EpreuveCatalogue = {
    id: number;
    slug: string;
    nom: string;
    description: string;
    icone: string;
    questions: number;
    duree: number;
    enCours: boolean;
};

export type TestComplet = {
    epreuves: string[];
    questions: number;
    duree: number;
    enCours: boolean;
} | null;

/** Paginateur Laravel (LengthAwarePaginator) sérialisé. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    prev_page_url: string | null;
    next_page_url: string | null;
};
