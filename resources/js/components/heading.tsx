import type { ReactNode } from 'react';

/** Titre de page : surtitre, titre en Archivo étendu, description, actions à droite. */
export default function Heading({
    surtitre,
    titre,
    description,
    children,
}: {
    surtitre?: string;
    titre: string;
    description?: ReactNode;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-end justify-between gap-4">
            <div className="flex min-w-0 flex-col gap-1">
                {surtitre && <p className="text-sm font-semibold text-muted-foreground">{surtitre}</p>}
                <h1 className="font-titre text-2xl leading-tight tracking-[-0.5px] md:text-[28px]">{titre}</h1>
                {description && <p className="max-w-2xl text-[15px] text-muted-foreground">{description}</p>}
            </div>
            {children && <div className="flex shrink-0 flex-wrap items-center gap-2">{children}</div>}
        </div>
    );
}
