import AppLogoIcon from '@/components/app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <AppLogoIcon inverse className="size-8 shrink-0" />
            <span className="truncate font-titre text-lg leading-none tracking-[-0.5px] group-data-[collapsible=icon]:hidden">edumica</span>
        </>
    );
}
