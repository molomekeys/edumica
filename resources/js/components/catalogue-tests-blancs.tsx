import { Link } from '@inertiajs/react';
import { ArrowRight, Timer } from 'lucide-react';
import { Arcs } from '@/components/arcs';
import { IconeEpreuve } from '@/components/icone-epreuve';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { listeFr } from '@/lib/format';
import { route } from '@/lib/routes';
import type { EpreuveCatalogue, TestComplet } from '@/types';

/** Tests blancs proposés : le test complet, puis un test par épreuve. */
export function CatalogueTestsBlancs({ complet, epreuves }: { complet: TestComplet; epreuves: EpreuveCatalogue[] }) {
    return (
        <div className="flex flex-col gap-3">
            {complet && (
                <div className="relative isolate flex flex-col gap-4 overflow-hidden rounded-xl bg-foret p-5 text-white sm:flex-row sm:items-center md:p-6">
                    <Arcs couleur="#FFB59E" className="-top-[140px] -right-[100px] size-[300px]" />
                    <div className="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-white/10 text-peche">
                        <Timer className="size-6" aria-hidden="true" />
                    </div>
                    <div className="flex flex-1 flex-col gap-0.5">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs font-bold tracking-[0.14em] text-peche uppercase">Test blanc complet</span>
                            {complet.enCours && <Badge className="bg-peche text-foret">En cours</Badge>}
                        </div>
                        <div className="text-[17px] font-bold">{listeFr(complet.epreuves)}</div>
                        <div className="text-sm text-white/70">
                            {complet.questions} questions · {complet.duree} min · un seul chrono
                        </div>
                    </div>
                    <Button asChild size="lg" className="self-start bg-peche font-bold text-foret hover:bg-white sm:self-center">
                        <Link href={route('test-blanc.complet')}>
                            {complet.enCours ? 'Reprendre le test complet' : 'Commencer le test complet'}
                            <ArrowRight />
                        </Link>
                    </Button>
                </div>
            )}

            <div className="grid gap-3 md:grid-cols-2">
                {epreuves.map((epreuve) => (
                    <Card key={epreuve.id} className="flex-row items-center gap-4 p-5">
                        <div className="flex size-12 shrink-0 items-center justify-center rounded-[14px] bg-peche-clair text-foret">
                            <IconeEpreuve nom={epreuve.icone} className="size-6" />
                        </div>
                        <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="text-[17px] font-bold">{epreuve.nom}</span>
                                {epreuve.enCours && <Badge variant="secondary">En cours</Badge>}
                            </div>
                            <span className="text-sm text-muted-foreground">
                                {epreuve.questions ? `${epreuve.questions} questions · ${epreuve.duree} min` : 'Bientôt disponible'}
                            </span>
                        </div>
                        {epreuve.questions ? (
                            <Button asChild className="font-bold">
                                <Link href={route('test-blanc', epreuve.slug)}>{epreuve.enCours ? 'Reprendre' : 'Commencer'}</Link>
                            </Button>
                        ) : (
                            <Badge variant="outline" className="text-muted-foreground">
                                Bientôt
                            </Badge>
                        )}
                    </Card>
                ))}
            </div>
        </div>
    );
}
