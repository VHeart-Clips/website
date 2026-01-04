import AppLayout from '@/layouts/app-layout';
import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { store } from '@/actions/App/Http/Controllers/ClipSubmitController';
import InputError from '@/components/input-error';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import submitclip from '@/routes/submitclip';
import { AlertCircle, Loader2, Tag as TagIcon } from 'lucide-react';

interface Tag {
    id: number;
    name: string;
}

type ClipPreview = {
    clip_id: string;
    embed_url: string;
} | null;

type InertiaBaseProps = Record<string, unknown>;

interface PageProps extends InertiaBaseProps {
    auth: {
        permissions: Array<String>;
        user: {
            id: number;
            name: string;
            submission_count_today: number;
            daily_submission_limit: number;
        };
    };
    tags: Tag[];
}

export default function SubmitClipPage({ tags = [] }: { tags: Tag[] }) {
    const { t } = useTranslation('sendinclip');
    const { props, flash } = usePage<PageProps>();
    const { errors } = props;
    const user = props.auth?.user || null;

    const [clipPreview, setClipPreview] = useState<ClipPreview>(null);

    const [isSubmitting, setIsSubmitting] = useState(false);
    const [clipUrl, setClipUrl] = useState('');
    const [error, setError] = useState<string | null>(null);

    const [isChecking, setIsChecking] = useState(false);

    const lastPreviewUrlRef = useRef<string>('');
    const debounceRef = useRef<number | null>(null);

    const breadcrumbs = useMemo(
        () => [{ title: t('breadcrumb'), href: submitclip.create().url }],
        [t],
    );

    const previewErrors: string[] = useMemo(() => {
        return [];
    }, [clipPreview]);

    const hasInput = clipUrl.trim().length > 0;

    const showErrors = false; //hasInput && !isChecking && previewErrors.length > 0;
    const showLoading = hasInput && isChecking;

    useEffect(() => {
        const trimmed = clipUrl.trim();

        if (debounceRef.current) window.clearTimeout(debounceRef.current);

        if (!trimmed) {
            lastPreviewUrlRef.current = '';
            return;
        }

        debounceRef.current = window.setTimeout(() => {
            if (trimmed === lastPreviewUrlRef.current) return;

            setIsChecking(true);
            const host = document.location.hostname;

            const clipMatch = clipUrl.match(
                /https?:\/\/(?:www|clips)?\.?(?:twitch\.tv\/)(?:embed\?clip=|[\w\/]+\/clip\/)?([\w_-]+)/,
            );

            if (clipMatch) {
                const clipId = clipMatch[1];

                setClipPreview({
                    clip_id: clipId,
                    embed_url: `https://clips.twitch.tv/embed?clip=${clipId}&parent=${host}`,
                } as ClipPreview);
            } else {
                setClipPreview(null);
            }

            setIsChecking(false);
        }, 200);

        return () => {
            if (debounceRef.current) window.clearTimeout(debounceRef.current);
        };
    }, [clipUrl]);

    if (!user) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title={t('page_title')} />
                <div className="container mx-auto px-4 py-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('login.title')}</CardTitle>
                            <CardDescription>
                                {t('login.subtitle')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Alert variant="destructive">
                                <AlertCircle className="h-4 w-4" />
                                <AlertDescription>
                                    {t('login.alert')}
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                        <CardFooter>
                            <Link href="/login" className="w-full">
                                <Button className="w-full">
                                    {t('login.cta')}
                                </Button>
                            </Link>
                        </CardFooter>
                    </Card>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('page_title')} />

            <div className="container mx-auto px-4 py-8">
                <div className="mx-auto max-w-4xl">
                    <div className="mb-8 text-center">
                        <h1 className="mb-2 text-3xl font-bold tracking-tight">
                            {t('headline')}
                        </h1>
                    </div>

                    {flash?.submit_ok && flash?.submit_message && (
                        <div className="mb-6">
                            <Alert>
                                <AlertDescription>
                                    {flash.submit_message}
                                </AlertDescription>
                            </Alert>
                        </div>
                    )}

                    {error && (
                        <div className="mb-6">
                            <Alert variant="destructive">
                                <AlertCircle className="h-4 w-4" />
                                <AlertDescription>{error}</AlertDescription>
                            </Alert>
                        </div>
                    )}

                    <div className="grid gap-8 lg:grid-cols-3">
                        <div className="space-y-6 lg:col-span-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle>{t('preview.title')}</CardTitle>
                                </CardHeader>

                                <CardContent>
                                    <div className="space-y-4">
                                        <div className="relative aspect-video overflow-hidden rounded-lg bg-black">
                                            {clipPreview ? (
                                                <iframe
                                                    key={clipPreview.clip_id}
                                                    src={clipPreview.embed_url}
                                                    className="h-full w-full"
                                                    style={{ border: 0 }}
                                                    allow="fullscreen"
                                                />
                                            ) : (
                                                <div className="flex h-full w-full items-center justify-center text-center text-muted-foreground">
                                                    {showLoading ? (
                                                        <div className="flex items-center justify-center gap-2">
                                                            <Loader2 className="h-5 w-5 animate-spin" />
                                                            <span>
                                                                {t(
                                                                    'preview.loading',
                                                                )}
                                                            </span>
                                                        </div>
                                                    ) : (
                                                        <p className="text-sm font-medium">
                                                            {t(
                                                                'preview.placeholder',
                                                            )}
                                                        </p>
                                                    )}
                                                </div>
                                            )}
                                        </div>

                                        {showErrors && (
                                            <Alert variant="destructive">
                                                <AlertCircle className="h-4 w-4" />
                                                <AlertDescription>
                                                    <ul className="list-inside list-disc space-y-1">
                                                        {previewErrors.map(
                                                            (m, idx) => (
                                                                <li key={idx}>
                                                                    {m}
                                                                </li>
                                                            ),
                                                        )}
                                                    </ul>
                                                </AlertDescription>
                                            </Alert>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>{t('submit.title')}</CardTitle>
                                </CardHeader>

                                <CardContent>
                                    <Form
                                        action={store()}
                                        className="space-y-6"
                                        noValidate
                                    >
                                        <div className="space-y-2">
                                            <Label htmlFor="clip_url">
                                                {t('submit.clip_url_label')}
                                            </Label>
                                            <Input
                                                id="clip_url"
                                                name="clip_url"
                                                placeholder={t(
                                                    'submit.clip_url_placeholder',
                                                )}
                                                value={clipUrl}
                                                onChange={(e) =>
                                                    setClipUrl(e.target.value)
                                                }
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter')
                                                        e.preventDefault();
                                                }}
                                                disabled={isSubmitting}
                                                autoComplete="off"
                                                inputMode="url"
                                                type="url"
                                            />
                                            <InputError
                                                className="mt-2"
                                                message={errors.clip_url}
                                            />
                                        </div>

                                        <div className="space-y-4">
                                            <Label className="flex items-center gap-2">
                                                <TagIcon className="h-4 w-4" />
                                                {t('submit.tags_label')}
                                            </Label>

                                            <div className="flex flex-wrap gap-2">
                                                {tags.map((tag, index) => (
                                                    <div key={'tag-' + tag.id}>
                                                        <Checkbox
                                                            id={'tag-' + tag.id}
                                                            name="tags[]"
                                                            value={tag.id}
                                                        />
                                                        <Label
                                                            htmlFor={
                                                                'tag-' + tag.id
                                                            }
                                                            className="cursor-pointer"
                                                        >
                                                            {tag.name}
                                                        </Label>{' '}
                                                    </div>
                                                ))}
                                                <InputError
                                                    className="mt-2"
                                                    message={errors.tags}
                                                />
                                            </div>
                                        </div>

                                        <Separator />

                                        <div className="flex items-center space-x-2">
                                            <Checkbox
                                                id="is_anonymous"
                                                name="is_anonymous"
                                            />
                                            <Label
                                                htmlFor="is_anonymous"
                                                className="cursor-pointer"
                                            >
                                                {t('submit.anonymous')}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {t('submit.anonymous_hint')}
                                                </span>
                                            </Label>
                                            <InputError
                                                className="mt-2"
                                                message={errors.is_anonymous}
                                            />
                                        </div>

                                        <Button
                                            type="submit"
                                            className="w-full"
                                            disabled={
                                                clipUrl.match(
                                                    /https?:\/\/(?:www|clips)?\.?(?:twitch\.tv\/)(?:embed\?clip=|[\w\/]+\/clip\/)?([\w_-]+)/,
                                                ) === null
                                            }
                                        >
                                            {t('submit.cta')}
                                        </Button>
                                    </Form>
                                </CardContent>
                            </Card>
                        </div>

                        <div className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm">
                                        {t('rules.title')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <ul className="space-y-2 text-sm">
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-primary" />
                                            <span>
                                                {t('rules.items.registered')}
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-primary" />
                                            <span>
                                                {t('rules.items.consent')}
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-primary" />
                                            <span>
                                                {t('rules.items.no_explicit')}
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-primary" />
                                            <span>
                                                {t('rules.items.tags_match')}
                                            </span>
                                        </li>
                                    </ul>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-sm">
                                        {t('tips.title')}
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <ul className="space-y-2 text-sm">
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-blue-500" />
                                            <span>{t('tips.items.short')}</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-blue-500" />
                                            <span>
                                                {t('tips.items.quality')}
                                            </span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <div className="mt-0.5 h-2 w-2 rounded-full bg-blue-500" />
                                            <span>{t('tips.items.funny')}</span>
                                        </li>
                                    </ul>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
