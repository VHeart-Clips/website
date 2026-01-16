import T from '@/components/t';
import { Input } from '@/components/ui/input';

export default function TranslationTest() {
    // Old method will still work, but i recommend using the new component
    // to keep it simple
    // const { t } = useTranslation(['auth']);
    // t('throttle', { seconds: Math.random() })

    return (
        <div className="space-y-4 p-4">
            {/* Simple String as Skeleton */}
            <p>
                <T ns="navigation" k="dashboard" loadingSkeleton={true} />
            </p>
            {/* Custom Skeleton */}
            <p>
                <T
                    ns="navigation"
                    k="dashboard"
                    loadingSkeleton={<p>Custom Loading Placeholder</p>}
                />
            </p>
            {/* Simple String without Skeleton (using Suspense to block rendering) */}
            <p>
                <T ns="team" k="team_subtitle" />
            </p>

            {/* Attributes */}
            <p>
                <T ns="sendinclip" k="submit.clip_url_placeholder">
                    {(text) => <Input placeholder={text} />}
                </T>
            </p>

            {/* Data */}
            <p>
                <T ns="auth" k="throttle" values={{ seconds: Math.random() }} />
            </p>
            <p>
                <T
                    ns="validation"
                    k="between.numeric"
                    values={{ attribute: 'Age', min: 18, max: 99 }}
                />
            </p>
            <p>
                <T
                    ns="validation"
                    k="required_if"
                    values={{
                        attribute: 'Credit Card',
                        other: 'Payment Type',
                        value: 'Credit',
                    }}
                />
            </p>
        </div>
    );
}
