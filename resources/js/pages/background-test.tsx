import SpaceBackground from '@/components/spacebackground';

export default function Welcome() {
    return (
        <div className="relative flex min-h-screen flex-col overflow-hidden bg-blue-50 dark:bg-[#0a0a1a]">
            <SpaceBackground />
        </div>
    );
}
