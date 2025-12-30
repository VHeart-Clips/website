import Logo from '/resources/images/svg/Logo Icon.svg';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary">
                <img src={Logo} alt="VHeart Logo" className="size-5" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    VHeart
                </span>
            </div>
        </>
    );
}
