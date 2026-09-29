import { Link } from '@inertiajs/react';
import { Fragment } from 'react';
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

export function Breadcrumbs({ breadcrumbs }: { breadcrumbs: BreadcrumbItemType[] }) {
    if (breadcrumbs.length === 0) {
        return null;
    }

    return (
        <Breadcrumb className="min-w-0">
            <BreadcrumbList className="flex-nowrap">
                {breadcrumbs.map((item, index) => {
                    const dernier = index === breadcrumbs.length - 1;

                    return (
                        <Fragment key={index}>
                            {/* Sur mobile, seul le dernier niveau reste visible. */}
                            <BreadcrumbItem className={dernier ? 'min-w-0' : 'hidden md:inline-flex'}>
                                {dernier ? (
                                    <BreadcrumbPage className="truncate font-semibold">{item.title}</BreadcrumbPage>
                                ) : (
                                    <BreadcrumbLink asChild>
                                        <Link href={item.href}>{item.title}</Link>
                                    </BreadcrumbLink>
                                )}
                            </BreadcrumbItem>
                            {!dernier && <BreadcrumbSeparator className="hidden md:block" />}
                        </Fragment>
                    );
                })}
            </BreadcrumbList>
        </Breadcrumb>
    );
}
