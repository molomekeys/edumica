import { cn } from '@/lib/utils';

export default function InputError({ message, className }: { message?: string; className?: string }) {
    if (!message) {
        return null;
    }

    return <p className={cn('text-sm font-medium text-destructive', className)}>{message}</p>;
}
