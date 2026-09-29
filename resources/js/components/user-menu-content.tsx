import { Link, router } from '@inertiajs/react';
import { LayoutDashboard, LogOut, ShieldCheck } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { useSidebar } from '@/components/ui/sidebar';
import { UserInfo } from '@/components/user-info';
import { route } from '@/lib/routes';
import type { User } from '@/types';

export function UserMenuContent({ user }: { user: User }) {
    const { setOpenMobile } = useSidebar();

    // Sur mobile, le tiroir se referme quand on quitte la page.
    const fermer = () => setOpenMobile(false);

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal">
                <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                    <UserInfo user={user} showEmail />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem asChild>
                    <Link href={route('espace')} className="w-full cursor-pointer" onClick={fermer}>
                        <LayoutDashboard />
                        Mon espace
                    </Link>
                </DropdownMenuItem>
                {user.is_admin && (
                    <DropdownMenuItem asChild>
                        <Link href={route('admin.questions')} className="w-full cursor-pointer" onClick={fermer}>
                            <ShieldCheck />
                            Admin
                        </Link>
                    </DropdownMenuItem>
                )}
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            {/* La déconnexion renvoie vers l'accueil Livewire, chargé entièrement (Inertia::location). */}
            <DropdownMenuItem className="cursor-pointer" onSelect={() => router.post(route('deconnexion'))}>
                <LogOut />
                Déconnexion
            </DropdownMenuItem>
        </>
    );
}
