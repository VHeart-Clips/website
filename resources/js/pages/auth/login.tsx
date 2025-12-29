import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import Logo from '/resources/images/svg/Logo Icon.svg';

export default function Welcome({
    kannRegistrieren = true,
}: {
    kannRegistrieren?: boolean;
}) {
    const twitchAuthUrl = '/auth/twitch';

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-secondary to-black p-4">
            <Card className="w-full max-w-md">
                <CardHeader className="space-y-4 text-center">
                    <div className="mb-6 flex justify-center">
                        <img src={Logo} alt="Logo" className="h-16 w-16" />
                    </div>

                    <CardTitle className="text-3xl font-bold tracking-tight">
                        Willkommen bei VHeart!
                    </CardTitle>
                </CardHeader>

                <CardContent className="space-y-6">
                    <p className="text-center leading-relaxed">
                        Wir freuen uns, dich hier zu haben. Verbinde dein
                        Twitch-Konto, um auf alle Funktionen zugreifen zu
                        können.
                    </p>

                    <div className="flex justify-center">
                        <div className="h-1 w-16 rounded-full bg-gradient-to-r from-primary to-secondary"></div>
                    </div>
                </CardContent>

                <CardFooter className="flex flex-col space-y-4">
                    {kannRegistrieren && (
                        <a href={twitchAuthUrl} className="w-full">
                            <Button className="w-full py-6 text-lg" size="lg">
                                <svg
                                    className="mr-3 h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z" />
                                </svg>
                                Mit Twitch verbinden
                            </Button>
                        </a>
                    )}

                    <p className="border-t pt-4 text-center text-sm text-muted-foreground">
                        Durch die Verbindung stimmst du unseren
                        Nutzungsbedingungen und Datenschutzrichtlinien zu
                    </p>
                </CardFooter>
            </Card>

            <div className="mt-8 text-center text-white/80">
                <p className="text-sm">
                    Unterstützt von unserer großartigen Community ❤️
                </p>
            </div>
        </div>
    );
}
