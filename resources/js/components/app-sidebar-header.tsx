import { Breadcrumbs } from '@/components/breadcrumbs';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

export function AppSidebarHeader({ breadcrumbs = [] }: { breadcrumbs?: BreadcrumbItem[] }) {
    return (
        <header className="flex h-14 shrink-0 items-center gap-2 border-b px-4 md:px-5">
            <SidebarTrigger className="-ms-1.5" />
            <Separator orientation="vertical" className="me-1 data-[orientation=vertical]:h-4" />
            <Breadcrumbs breadcrumbs={breadcrumbs} />
        </header>
    );
}
