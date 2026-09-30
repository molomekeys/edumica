import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { SidebarInset, SidebarProvider } from '@/components/ui/sidebar';
import { useFlashToast } from '@/hooks/use-flash-toast';
import type { BreadcrumbItem } from '@/types';

/**
 * Layout commun des dashboards : sidebar rétractable (icônes seules, Ctrl/Cmd+B, état gardé
 * dans un cookie), tiroir sur mobile, header avec le fil d'Ariane. Persistant entre les pages.
 */
export default function AppLayout({ breadcrumbs = [], children }: { breadcrumbs?: BreadcrumbItem[]; children: ReactNode }) {
    const { sidebarOpen } = usePage().props;
    useFlashToast();

    return (
        <SidebarProvider defaultOpen={sidebarOpen}>
            <AppSidebar />
            <SidebarInset className="min-w-0 overflow-x-clip">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </SidebarInset>
        </SidebarProvider>
    );
}
