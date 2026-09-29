import type { BreadcrumbItem, EpreuveNavigation, Flash, User } from '@/types';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            auth: { user: User };
            flash: Flash;
            sidebarOpen: boolean;
            navigation: { epreuves: EpreuveNavigation[] };
        };
        layoutProps: {
            breadcrumbs: BreadcrumbItem[];
        };
    }
}
