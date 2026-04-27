import * as Sentry from '@sentry/browser';
declare const __SENTRY_RELEASE__: string;

const dsn = import.meta.env.VITE_SENTRY_DSN as string | undefined;

if (dsn) {
    Sentry.init({
        dsn,
        release: __SENTRY_RELEASE__,
        environment: import.meta.env.MODE,
        sendDefaultPii: false,
        tracesSampleRate: 0.1,
        replaysSessionSampleRate: 0,
        replaysOnErrorSampleRate: 0,
        profileSessionSampleRate: 0,
        enableLogs: false,
        integrations: [
            // glitch does not seem to support this yet
            // Sentry.feedbackIntegration({
            //     colorScheme: 'system',
            // }),
        ],
        beforeSend(event, hint) {
            console.debug('sending error to tracker...', event, hint);

            if (event.exception && event.event_id) {
                // does not work currently, no need to annoy the user with that yet
                //Sentry.showReportDialog({ eventId: event.event_id });
            }
            return event;
        },
    });
}
