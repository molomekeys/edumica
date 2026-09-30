import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import type { User } from '@/types';

export function UserInfo({ user, showEmail = false }: { user: User; showEmail?: boolean }) {
    return (
        <>
            <Avatar className="size-8 rounded-full">
                <AvatarFallback className="bg-menthe text-xs font-bold text-foret">{user.initiales}</AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-start text-sm leading-tight">
                <span className="truncate font-semibold">{user.name}</span>
                {showEmail && <span className="truncate text-xs opacity-70">{user.email}</span>}
            </div>
        </>
    );
}
