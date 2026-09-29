import { Head, Link } from '@inertiajs/react';
import { ChartNoAxesColumn } from 'lucide-react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { route } from '@/lib/routes';

/** Page à venir : l'historique détaillé des tests blancs. */
export default function Resultats() {
    return (
        <>
            <Head title="Mes résultats" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading titre="Mes résultats" />

                <Card className="border-dashed">
                    <CardContent className="flex flex-col items-center gap-4 py-10 text-center">
                        <div className="flex size-14 items-center justify-center rounded-2xl bg-menthe text-foret">
                            <ChartNoAxesColumn className="size-7" aria-hidden="true" />
                        </div>
                        <div className="flex max-w-md flex-col gap-1">
                            <h2 className="font-titre text-xl tracking-[-0.5px]">Bientôt disponible</h2>
                            <p className="text-[15px] text-muted-foreground">
                                L’historique de tes tests blancs et ta progression par épreuve arrivent ici. En attendant, ton
                                tableau de bord résume tes derniers scores.
                            </p>
                        </div>
                        <div className="flex flex-wrap justify-center gap-2">
                            <Button asChild>
                                <Link href={route('espace.tests-blancs')}>Passer un test blanc</Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href={route('espace')}>Tableau de bord</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

Resultats.layout = {
    breadcrumbs: [{ title: 'Mes résultats', href: route('espace.resultats') }],
};
